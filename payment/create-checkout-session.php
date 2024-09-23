<?php
require '../vendor/autoload.php'; // Stripe SDK
require '../database/db_connect.php';

\Stripe\Stripe::setApiKey('pk_live_51Q2EhgGALGqnsrsMOa3cWrsvKMk95QnRWStBZucXltjcI42pUaM0GBsxnqTz7wFdmbwZ93nE99RyvRpQ9Jesrig700vknlFK6r');

$reservation_id = $_POST['reservation_id'];

// Preia detaliile rezervării din baza de date
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :id");
$stmt->execute(['id' => $reservation_id]);
$reservation = $stmt->fetch();

$YOUR_DOMAIN = 'https://bect.ro';

$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'ron',
            'product_data' => [
                'name' => 'Rezervare Dermatologie - ' . $reservation['service'],
            ],
            'unit_amount' => 5000, // Exemplu de preț: 50 RON
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => $YOUR_DOMAIN . '/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $YOUR_DOMAIN . '/payment-cancel.php',
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $session->url);
