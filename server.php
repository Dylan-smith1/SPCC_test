<?php
header('Content-Type: application/json');

$endpoint = 'https://newplustest.myshopify.com/api/2024-07/graphql.json';
$accessToken = 'd8de61f2373782f576051018de40bbf1';

function makeGraphQLRequest($query, $variables) {
    global $endpoint, $accessToken;

    $data = json_encode([
        'query' => $query,
        'variables' => $variables
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Shopify-Storefront-Access-Token: ' . $accessToken
    ]);

    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return ['errors' => [['message' => 'cURL error: ' . $error_msg]]];
    }

    curl_close($ch);

    $decodedResponse = json_decode($response, true);

    // Check for GraphQL errors
    if (isset($decodedResponse['errors'])) {
        return ['errors' => $decodedResponse['errors']];
    }

    return $decodedResponse;
}

$request = json_decode(file_get_contents('php://input'), true);

if ($request['action'] === 'createSession') {
    $query = <<<GQL
    mutation shopPayPaymentRequestSessionCreate(\$sourceIdentifier: String!, \$paymentRequest: ShopPayPaymentRequestInput!) {
        shopPayPaymentRequestSessionCreate(sourceIdentifier: \$sourceIdentifier, paymentRequest: \$paymentRequest) {
            shopPayPaymentRequestSession {
                token
                sourceIdentifier
                checkoutUrl
                paymentRequest {
                    lineItems {
                        label
                        finalLinePrice {
                            amount
                            currencyCode
                        }
                    }
                    total {
                        amount
                        currencyCode
                    }
                }
            }
            userErrors {
                field
                message
            }
        }
    }
    GQL;

    $variables = [
        'sourceIdentifier' => uniqid(),
        'paymentRequest' => $request['payment_request']
    ];

    $response = makeGraphQLRequest($query, $variables);

    if (isset($response['data']['shopPayPaymentRequestSessionCreate']['shopPayPaymentRequestSession'])) {
        $session = $response['data']['shopPayPaymentRequestSessionCreate']['shopPayPaymentRequestSession'];
        echo json_encode([
            'token' => $session['token'],
            'checkoutUrl' => $session['checkoutUrl'],
            'sourceIdentifier' => $session['sourceIdentifier']
        ]);
    } else {
        $errors = isset($response['data']['shopPayPaymentRequestSessionCreate']['userErrors']) ? $response['data']['shopPayPaymentRequestSessionCreate']['userErrors'] : [];
        echo json_encode(['errors' => $errors]);
    }
} elseif ($request['action'] === 'submitSession') {
    $query = <<<GQL
    mutation shopPayPaymentRequestSessionSubmit(\$token: String!, \$paymentRequest: ShopPayPaymentRequestInput!, \$idempotencyKey: String!) {
        shopPayPaymentRequestSessionSubmit(token: \$token, paymentRequest: \$paymentRequest, idempotencyKey: \$idempotencyKey) {
            paymentRequestReceipt {
                token
                processingStatusType
            }
            userErrors {
                field
                message
            }
        }
    }
    GQL;

    $variables = [
        'token' => $request['token'],
        'paymentRequest' => $request['payment_request'],
        'idempotencyKey' => uniqid()
    ];

    $response = makeGraphQLRequest($query, $variables);

    if (isset($response['data']['shopPayPaymentRequestSessionSubmit']['paymentRequestReceipt'])) {
        echo json_encode(['success' => true]);
    } else {
        $errors = isset($response['data']['shopPayPaymentRequestSessionSubmit']['userErrors']) ? $response['data']['shopPayPaymentRequestSessionSubmit']['userErrors'] : [];
        echo json_encode(['errors' => $errors]);
    }
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>