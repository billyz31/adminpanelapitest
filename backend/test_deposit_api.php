<?php

require __DIR__ . '/vendor/autoload.php';

// Configuration
$baseUrl = 'http://nginx/api'; // Use nginx service name within docker network
$email = 'deposit_test_' . time() . '@example.com';
$password = 'password123';
$username = 'dep_user_' . time();

// Helper function to make HTTP requests
function makeRequest($method, $url, $data = [], $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch) . "\n";
    }
    
    
    
    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

echo "Starting Deposit API Test...\n";

// 1. Register
echo "\n1. Registering user ($username)...\n";
$registerData = [
    'username' => $username,
    'name' => 'Deposit Tester',
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password
];
$regResponse = makeRequest('POST', $baseUrl . '/register', $registerData);
echo "Status: " . $regResponse['code'] . "\n";
if ($regResponse['code'] !== 201 && $regResponse['code'] !== 200) {
    echo "Registration failed. Response: " . json_encode($regResponse['body']) . "\n";
    exit(1);
}
$token = $regResponse['body']['token'];
echo "Token obtained: " . substr($token, 0, 10) . "...\n";

// 2. Check Initial Balance
echo "\n2. Checking initial balance...\n";
$meResponse = makeRequest('GET', $baseUrl . '/me', [], $token);
echo "Initial Balance: " . $meResponse['body']['balance'] . "\n";

// 3. Deposit Money
$depositAmount = 100.50;
echo "\n3. Depositing $depositAmount...\n";
$depositData = [
    'amount' => $depositAmount,
    'description' => 'Test Deposit'
];
$depResponse = makeRequest('POST', $baseUrl . '/deposit', $depositData, $token);
echo "Status: " . $depResponse['code'] . "\n";
echo "Response: " . json_encode($depResponse['body']) . "\n";

if ($depResponse['code'] === 200) {
    echo "Deposit successful!\n";
} else {
    echo "Deposit failed!\n";
    exit(1);
}

// 4. Verify Balance
echo "\n4. Verifying final balance...\n";
$finalMeResponse = makeRequest('GET', $baseUrl . '/me', [], $token);
echo "Final Balance: " . $finalMeResponse['body']['balance'] . "\n";

if ($finalMeResponse['body']['balance'] == $depositAmount) {
    echo "\nTEST PASSED: Balance updated correctly.\n";
} else {
    echo "\nTEST FAILED: Balance mismatch.\n";
}
