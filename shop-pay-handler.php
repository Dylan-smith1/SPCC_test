<?php

// shopify.php

// Mock data for testing
$mockSessionResponse = [
    'token' => 'db4eede13822684b13a607823b7ba40d',
    'checkoutUrl' => 'https://shop.app/checkout/1/spe/db4eede13822684b13a607823b7ba40d/shoppay',
    'sourceIdentifier' => 'xyz123'
];

// Endpoint to handle session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/create_session') {
    header('Content-Type: application/json');
    echo json_encode($mockSessionResponse);
    exit;
}

// Endpoint to handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/confirm_payment') {
    $data = json_decode(file_get_contents('php://input'), true);
    // Mock validation logic
    if ($data['payment_request']['total']['amount'] > 1000) {
        header('Content-Type: application/json');
        echo json_encode(['errors' => [['type' => 'generalError', 'message' => 'Your order is over $1000. Please remove some items from your order.']]]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
    }
    exit;
}