<?php
$cardType = isset($_GET['card_type']) ? htmlspecialchars($_GET['card_type']) : 'Unknown';
$lastFourDigits = isset($_GET['last_four_digits']) ? htmlspecialchars($_GET['last_four_digits']) : 'XXXX';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
</head>
<body>
    <h1>Thank You for Your Purchase!</h1>
    <p>Your payment was successful.</p>
    <p>Card Type: <?php echo $cardType; ?></p>
    <p>Last Four Digits: <?php echo $lastFourDigits; ?></p>
</body>
</html>