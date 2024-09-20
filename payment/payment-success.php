<?php
require '../vendor/autoload.php'; // Stripe SDK
require '../database/db_connect.php'; // Fă conexiunea la baza de date

\Stripe\Stripe::setApiKey('sk_test_YOUR_SECRET_KEY_HERE');

$session_id = $_GET['session_id'];
$session = \Stripe\Checkout\Session::retrieve($session_id);

// Preia detaliile rezervării din baza de date
$reservation_id = $session->client_reference_id;

// Actualizează statusul rezervării în baza de date
$stmt = $pdo->prepare("UPDATE reservations SET status = 'paid' WHERE id = :reservation_id");
$stmt->execute(['reservation_id' => $reservation_id]);

// Trimite emailuri de confirmare
require '../mailer/send_confirmation_email.php';

// Redirecționează utilizatorul către pagina de succes
header('Location: ../success.html');
?>
