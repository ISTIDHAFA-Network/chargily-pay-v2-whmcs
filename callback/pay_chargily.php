<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

$gatewayParams = getGatewayVariables("pay_chargily");

if (!$gatewayParams['type']) {
    logTransaction('pay_chargily', [], 'Module Not Activated');
    die("Module Not Activated");
}

$secret = $gatewayParams['secretKey'];
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Journaliser les données reçues
logTransaction('pay_chargily', $data, 'Webhook Data Received');

// Vérifier si les données sont valides
if (!isset($data["type"], $data["data"]["status"], $data["data"]["amount"])) {
    logTransaction('pay_chargily', $data, 'Invalid data received');
    die("Invalid data received");
}

// Validation de la signature
$localSignature = hash_hmac('sha256', $rawData, $secret);
$headers = isset($_SERVER['HTTP_SIGNATURE']) ? $_SERVER['HTTP_SIGNATURE'] : '';

if (!hash_equals($localSignature, $headers)) {
    logTransaction('pay_chargily', $data, 'Signature Validation Failed');
    die("Signature validation failed");
}

logTransaction('pay_chargily', $data, 'Signature Validation Successful');

if ($data["type"] === 'checkout.paid' && $data["data"]["status"] === 'paid') {
    $transactionId = $data["id"];
    $paymentAmount = $data["data"]["amount"];
    $invoiceId = isset($data["data"]["metadata"]["invoice_id"]) ? $data["data"]["metadata"]["invoice_id"] : null;  // Récupérer l'ID de la facture des métadonnées

    if (is_null($invoiceId)) {
        logTransaction('pay_chargily', $data, 'Invoice ID is missing');
        die("Invoice ID is missing in the metadata");
    }

    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        0,
        'pay_chargily'
    );

    logTransaction('pay_chargily', $data, 'Payment Successful');
    die("Payment processed successfully");
} else {
    logTransaction('pay_chargily', $data, 'Payment Status Not Paid or Invalid Event Type');
    die("Payment status not paid or invalid event type");
}
?>
