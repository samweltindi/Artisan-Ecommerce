<?php
define('DARAJA_MODE', 'sandbox');

// ---- Sandbox credentials gotten from my safaricom developer account
define('MPESA_CONSUMER_KEY', 'jh85kvd6WMUl8GI1DeSAZTPTv9fPG3oX4ZVJmKEGlbD8TWhq');
define('MPESA_CONSUMER_SECRET', 'qEtZqaxHuyNeDbtGN6GzuoHaBDGTopA103VdCGWmzEXGBXVGiIz5cLhjnJAoyK0Y');
define('MPESA_SHORTCODE', '174379');
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');
define('MPESA_CALLBACK_URL', 'https://handcuff-vintage-cardiac.ngrok-free.dev/School_Project/includes/mpesa_callback.php');
define('MPESA_BASE_URL', DARAJA_MODE === 'live'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke');

/**
 * Converts 07XXXXXXXX / 01XXXXXXXX / 2547XXXXXXXX into the 2547XXXXXXXX
 * format Daraja expects.
 */
function normalizeMpesaPhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        $phone = '254' . substr($phone, 1);
    }
    return $phone;
}

function getMpesaAccessToken() {
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

    $ch = curl_init(MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['access_token'] ?? null;
}

/**
 * Triggers an STK push (or simulates one, in mock mode).
 *
 * @param string $phone              Customer phone, any common format.
 * @param float  $amount              Amount to charge.
 * @param string $account_reference   Your invoice_number, used to match the callback later.
 * @return array ['success' => bool, 'CheckoutRequestID' => string|null, 'error'/'ResponseDescription' => string]
 */
function initiateSTKPush($phone, $amount, $account_reference) {
    $phone = normalizeMpesaPhone($phone);

    if (DARAJA_MODE === 'mock') {
        // No network call — instantly "succeeds" so you can test the rest
        // of the flow (order recording, cart clearing, confirmation page)
        // without any Daraja setup.
        return [
            'success'              => true,
            'CheckoutRequestID'    => 'mock_' . uniqid(),
            'ResponseDescription'  => 'MOCK MODE — no real STK push was sent. Treated as accepted.',
        ];
    }

    $access_token = getMpesaAccessToken();
    if (!$access_token) {
        return ['success' => false, 'error' => 'Could not authenticate with Daraja API. Check your consumer key/secret.'];
    }

    $timestamp = date('YmdHis');
    $password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password'          => $password,
        'Timestamp'          => $timestamp,
        'TransactionType'    => 'CustomerPayBillOnline',
        'Amount'             => (int) round($amount),
        'PartyA'             => $phone,
        'PartyB'             => MPESA_SHORTCODE,
        'PhoneNumber'        => $phone,
        'CallBackURL'        => MPESA_CALLBACK_URL,
        'AccountReference'   => $account_reference,
        'TransactionDesc'    => 'ArtisanOrders payment',
    ];

    $ch = curl_init(MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['ResponseCode']) && $result['ResponseCode'] === '0') {
        return [
            'success'             => true,
            'CheckoutRequestID'   => $result['CheckoutRequestID'],
            'ResponseDescription' => $result['ResponseDescription'],
        ];
    }

    return ['success' => false, 'error' => $result['errorMessage'] ?? 'STK push failed.'];
}