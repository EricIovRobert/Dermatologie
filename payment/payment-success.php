<?php
require '../database/db_connect.php'; // Conexiunea la baza de date
require '../vendor/autoload.php'; // PHPMailer și Stripe SDK

\Stripe\Stripe::setApiKey('sk_test_51PM4JAJVBSSkhR5YX4cLn2nte3Okt9vsad7gjyfF1H02kJe79PsPYuXZMAJhpaCK7iGCX1J42nciPFRsSWly4ujc009rYxPjf4'); // Cheia ta secretă Stripe

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$session_id = $_GET['session_id'];
$reservation_id = $_GET['reservation_id'];

// Verifică sesiunea de plată
$session = \Stripe\Checkout\Session::retrieve($session_id);

// Dacă plata a fost efectuată cu succes
if ($session->payment_status === 'paid') {
    // Actualizează statusul rezervării în 'paid'
    $stmt = $pdo->prepare("UPDATE reservations SET status = 'paid' WHERE id = :reservation_id");
    $stmt->execute(['reservation_id' => $reservation_id]);

    // Obține datele rezervării pentru email
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :reservation_id");
    $stmt->execute(['reservation_id' => $reservation_id]);
    $reservation = $stmt->fetch();

    // Trimite emailuri folosind PHPMailer și Mailtrap
    $mail = new PHPMailer(true);

    try {
        // Setează serverul SMTP pentru Mailtrap
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'deian.ageu1@gmail.com'; // Username-ul din Mailtrap
        $mail->Password = "qmnu pnhc fnpp edtj"; // Parola din Mailtrap
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Setează expeditorul
        $mail->setFrom('office@bect.com', 'Bect Dermatologie');

        // Trimite emailul către client
        $mail->addAddress($reservation['email']); // Emailul clientului
        $mail->isHTML(true);
        $mail->Subject = 'Confirmare rezervare si plata';
        $mail->Body = "<p>Salut, " . $reservation['name'] . "!<br>Rezervarea ta pentru " . $reservation['service'] . " a fost confirmată.<br>Data: " . $reservation['date'] . ".</p>";
        $mail->send();

        // Trimite emailul către doctor
        $mail->clearAddresses();
        $mail->addAddress('ericiov73@gmail.com'); // Adresa reală a doctorului
        $mail->Subject = 'Nouă rezervare confirmată';
        $mail->Body = "
            O nouă rezervare a fost confirmată:<br>
            Nume: " . $reservation['name'] . "<br>
            Email: " . $reservation['email'] . "<br>
            Telefon: " . $reservation['phone'] . "<br>
            Data: " . $reservation['date'] . "<br>
            Serviciu: " . $reservation['service'] . "
        ";
        $mail->send();

        // Redirecționează utilizatorul către o pagină de succes
        header('Location: ../success.html');
    } catch (Exception $e) {
        echo "Mesajul nu a putut fi trimis. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    // Dacă plata nu a reușit, redirecționează către o pagină de eșec
    header('Location: ../failure.html');
}
