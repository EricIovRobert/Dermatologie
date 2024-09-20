<?php
require '../database/db_connect.php'; // Conexiunea la baza de date
require '../vendor/autoload.php'; // Stripe SDK

\Stripe\Stripe::setApiKey('sk_test_51PM4JAJVBSSkhR5YX4cLn2nte3Okt9vsad7gjyfF1H02kJe79PsPYuXZMAJhpaCK7iGCX1J42nciPFRsSWly4ujc009rYxPjf4'); // Cheia ta secretă Stripe

// Verifică dacă cererea este POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Preia datele din formular
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $service = $_POST['service'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    // Verifică dacă există un mesaj sau nu
    $message = !empty($_POST['message']) ? $_POST['message'] : NULL;

    // Combină data și ora într-un format datetime pentru MySQL
    $datetime = $date . ' ' . $time . ':00';

    // Verifică dacă ora este deja rezervată
    $check_availability = $pdo->prepare("SELECT * FROM reservations WHERE date = :date AND status = 'paid'");
    $check_availability->execute(['date' => $datetime]);
    $existing_reservation = $check_availability->fetch();

    if ($existing_reservation) {
        // Redirecționează utilizatorul către o pagină care arată că ora este ocupată
        header('Location: ../unavailable.html');
    } else {
        // Salvează rezervarea în baza de date cu status 'pending'
        $stmt = $pdo->prepare("INSERT INTO reservations (name, email, phone, service, date, message, status) VALUES (:name, :email, :phone, :service, :date, :message, 'pending')");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'date' => $datetime,
            'message' => $message
        ]);

        // Obține ID-ul rezervării adăugate
        $reservation_id = $pdo->lastInsertId();

        // Inițializează sesiunea Stripe pentru plată
        $YOUR_DOMAIN = 'http://localhost/medicio';
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'ron',
                    'product_data' => [
                        'name' => 'Rezervare: ' . $service,
                    ],
                    'unit_amount' => 5000, // Valoarea de plată (de ex. 50 lei)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/payment/payment-success.php?session_id={CHECKOUT_SESSION_ID}&reservation_id=' . $reservation_id,
            'cancel_url' => $YOUR_DOMAIN . '/payment/payment-cancel.php',
        ]);

        // Redirecționează utilizatorul către Stripe Checkout
        header("Location: " . $session->url);
    }
} else {
    echo "Cerere nevalidă.";
}
