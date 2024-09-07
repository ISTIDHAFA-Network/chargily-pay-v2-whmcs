<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function pay_chargily_MetaData()
{
    return array(
        'DisplayName' => 'CIB & Edahabia Payment Gateway Module',
        'APIVersion' => '2.0',
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function pay_chargily_config() {
    $jsonFilePath = __DIR__ . '/pay_chargily/whmcs.json';

    if (file_exists($jsonFilePath)) {
        $jsonContent = file_get_contents($jsonFilePath);
        $configData = json_decode($jsonContent, true);

        if (isset($configData['fields'])) {
            return $configData['fields']; 
        }
    }
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'CIB & Edahabia Payment Gateway',
        ),
        'publicKey' => array(
            'FriendlyName' => 'Public key',
            'Type' => 'text',
            'Size' => '99',
            'Default' => '',
            'Description' => 'Enter your Public key',
        ),
        'secretKey' => array(
            'FriendlyName' => 'Secret Key',
            'Type' => 'password',
            'Size' => '99',
            'Default' => '',
            'Description' => 'Enter secret key here',
        ),
        'environment' => array(
            'FriendlyName' => 'Environment',
            'Type' => 'dropdown',
            'Options' => 'test,production',
            'Description' => 'Choose the environment: test or production',
        ),
        'discount' => array(
            'FriendlyName' => 'Discount',
            'Type' => 'text',
            'Size' => '2',
            'Default' => '0',
            'Description' => 'If you offer a special discount on this payment method, write it down here (0-99)%',
        ),
    );
}

function get_chargily_api_url($environment) {
    return $environment === 'production' ? "https://pay.chargily.net/api/v2/" : "https://pay.chargily.net/test/api/v2/";
}

function pay_chargily_link($params) {
    if ($params['currency'] != 'DZD') {
        return "Les paiements via cette passerelle ne sont autorisés qu'en DZD.";
    }
    $invoiceId = $params['invoiceid'];  

    // URL du webhook
    $webhook_url = $params['systemurl'] . 'modules/gateways/callback/pay_chargily.php';

    $payment_data = array(
        "amount" => $params['amount'],
        "currency" => "dzd", 
        "payment_method" => "cib", 
        "success_url" => $params['returnurl'], 
        "webhook_endpoint" => $webhook_url, 
        "description" => "Payment for Invoice #" . $invoiceId, 
        "locale" => "fr", 
        "percentage_discount" => $params['discount'],
        "metadata" => array( 
            "invoice_id" => $invoiceId 
        )
    );

    $headers = array(
        "Authorization: Bearer " . $params['secretKey'],
        "Content-Type: application/json"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, get_chargily_api_url($params["environment"]) . "checkouts");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        logTransaction("pay_chargily", $params, "cURL Error #: " . $err);
        return "An error occurred: cURL Error #: " . $err;
    }

    $checkout_response = json_decode($result, true);

    logTransaction("pay_chargily", array_merge($checkout_response, $params), "API Response");

    if (isset($checkout_response['errors']['amount'])) {
        if (in_array("The amount field must be greater than or equal to 50.", $checkout_response['errors']['amount'])) {
            return "Le montant minimum pour effectuer un paiement est de 50 DZD.";
        }
    }

    if (isset($checkout_response['checkout_url'])) {
        $url = $checkout_response['checkout_url'];
        return "<a href=\"$url\" class=\"btn btn-primary\">" . $params["langpaynow"] . "</a>";
    } else {
        return "An error occurred during payment creation. Response: " . json_encode($checkout_response);
    }  
}

function restrict_gateways_by_currency($vars)
{
    $sessionCurrencyId = isset($_SESSION['currency']) ? $_SESSION['currency'] : 1; // Par défaut 1 si non défini
    $clientCurrencyId = isset($_SESSION['uid']) ? getCurrency($_SESSION['uid'])['id'] : $sessionCurrencyId;
    $currencyId = isset($_SESSION['uid']) ? $clientCurrencyId : $sessionCurrencyId;
    $selectedGateway = isset($vars['selectedgateway']) ? $vars['selectedgateway'] : '';

    if ($currencyId == 1) { 
        foreach ($vars['gateways'] as $key => $gateway) {
            if ($gateway['sysname'] != 'pay_chargily') {
                unset($vars['gateways'][$key]); 
            }
        }
        if (isset($vars['gateways']['pay_chargily'])) {
            $selectedGateway = 'pay_chargily';
        }
    } else {
        foreach ($vars['gateways'] as $key => $gateway) {
            if ($gateway['sysname'] == 'pay_chargily') {
                unset($vars['gateways'][$key]); 
            }
        }
    }

    return array(
        'gateways' => $vars['gateways'],
        'selectedgateway' => $selectedGateway
    );
}

add_hook('ClientAreaPageCart', 1, 'restrict_gateways_by_currency');
add_hook('ClientAreaPageViewInvoice', 1, 'restrict_gateways_by_currency');
add_hook('ClientAreaPageAddFunds', 1, 'restrict_gateways_by_currency');