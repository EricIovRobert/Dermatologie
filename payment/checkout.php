<?php
require '../vendor/autoload.php'; // Stripe SDK
require '../database/db_connect.php'; // Fă conexiunea la baza de date

\Stripe\Stripe::setApiKey('sk_test_YOUR_SECRET_KEY_HERE'); // Adaugă cheia ta secretă de test de la Stripe

$reservation_id = $_GET['reservation_id'];

// Preia detaliile rezervării din baza de date
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :id");
$stmt->execute(['id' => $reservation_id]);
$reservation = $stmt->fetch();

$YOUR_DOMAIN = 'http://localhost/medicio'; // URL-ul tău local

$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'ron',
            'product_data' => [
                'name' => 'Rezervare - ' . $reservation['service'],
            ],
            'unit_amount' => 5000, // Suma de plată (de exemplu, 50 RON)
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => $YOUR_DOMAIN . '/payment/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $YOUR_DOMAIN . '/payment/payment-cancel.php',
]);

header("Location: " . $session->url);
