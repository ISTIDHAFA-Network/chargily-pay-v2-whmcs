
# Chargily Payment Gateway for WHMCS

**Chargily Payment Gateway for WHMCS** allows businesses in Algeria to integrate **CIB** and **Edahabia** card payments into their WHMCS platform. It provides a secure and reliable way to accept online payments, making it easier for users to pay for services like hosting, digital products, and more.

## Features

- **Full Chargily Integration**:
  - Supports **CIB** and **Edahabia** card payments.
  - Secure redirection to Chargily's payment platform for transaction completion.

- **Multi-Environment Support**:
  - Functionality for **test** and **production** environments.

- **Secure Payments**:
  - HMAC SHA256 signature verification for webhook validation.
  - Automatic payment validation to prevent fraud and errors.

- **Automated Invoice Management**:
  - Invoices in WHMCS are automatically updated when payments are marked as **paid**.
  - Comprehensive transaction logging for tracking and auditing.

- **Custom Payment Options**:
  - Supports custom discounts on transactions.
  - Allows detailed payment descriptions for better tracking.

- **Real-Time Payment Tracking**:
  - Real-time synchronization of payment statuses via webhook notifications.
  - Customers can view transaction statuses directly from their WHMCS client account.

- **Easy Installation**:
  - Quick setup through the WHMCS interface with simple configuration steps.
  - Detailed documentation provided.

- **Notification System**:
  - Automated notifications for successful, pending, and failed payments.
  - Configurable webhook and return URLs.

- **Localization**:
  - Supports **French** and **Arabic**, localized for Algeria.
  - Adapts to **DZD currency** and local language preferences.

- **WHMCS Compatibility**:
  - Compatible with WHMCS version 8.x and above.

## Installation

1. **Download the Module**: Clone this repository or download the zip file.

2. **Upload to WHMCS**:
   - Extract the module and upload it to the `/modules/gateways/` directory of your WHMCS installation.

3. **Activate the Module**:
   - Go to **Setup > Payments > Payment Gateways** in your WHMCS admin panel.
   - Click on **All Payment Gateways** and find **Chargily** in the list.
   - Activate the module.

4. **Configure the Module**:
   - Enter your **API Key** and **Secret Key** provided by Chargily.
   - Choose the **environment** (Test or Production).
   - Set up webhook and success URLs.

5. **Webhook Setup**:
   - Set your **Webhook URL** in your Chargily dashboard to `https://yourdomain.com/modules/gateways/callback/pay_chargily.php`.

## Usage

Once installed and configured, your customers will be able to choose **CIB** or **Edahabia** as a payment method during checkout. The payment process will redirect them to Chargily's secure payment page, and upon successful completion, their WHMCS invoices will be updated automatically.

## Contribution

If you would like to contribute to this project, feel free to submit a pull request or open an issue on GitHub.


## Support

For support, please contact support@isidhafa.net.
