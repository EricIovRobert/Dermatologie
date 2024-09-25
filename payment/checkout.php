<?php
require '../vendor/autoload.php'; // Stripe SDK
require '../database/db_connect.php'; // Fă conexiunea la baza de date

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // Adjust path if needed
$dotenv->load();

// Now you can access the keys from the $_ENV superglobal
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
$reservation_id = $_GET['reservation_id'];

// Preia detaliile rezervării din baza de date
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :id");
$stmt->execute(['id' => $reservation_id]);
$reservation = $stmt->fetch();

$YOUR_DOMAIN = 'https://bect.ro'; // Sau înlocuiește cu URL-ul domeniului tău


$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'ron',
            'product_data' => [
                'name' => 'Rezervare - ' . $reservation['service'],
            ],
            'unit_amount' => 16000, // Suma de plată (de exemplu, 50 RON)
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => $YOUR_DOMAIN . '/payment/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $YOUR_DOMAIN . '/payment/payment-cancel.php',
]);

header("Location: " . $session->url);
