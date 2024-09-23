<?php
require '../database/db_connect.php'; // Conexiunea la baza de date
require '../vendor/autoload.php'; // PHPMailer și Stripe SDK

\Stripe\Stripe::setApiKey('pk_live_51Q2EhgGALGqnsrsMOa3cWrsvKMk95QnRWStBZucXltjcI42pUaM0GBsxnqTz7wFdmbwZ93nE99RyvRpQ9Jesrig700vknlFK6r'); // Cheia ta secretă Stripe

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
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '9d92866713b47e'; // Username-ul din Mailtrap
        $mail->Password = '17f7d46462f2b9'; // Parola din Mailtrap
        $mail->SMTPSecure = 'tls';
        $mail->Port = 2525;

        // Setează expeditorul
        $mail->setFrom('from@example.com', 'Bect Dermatologie');

        // Trimite emailul către client
        $mail->addAddress($reservation['email']); // Emailul clientului
        $mail->isHTML(true);
        $mail->Subject = 'Confirmare rezervare și plată';
        $mail->Body = "<p>Salut, " . $reservation['name'] . "!<br>Rezervarea ta pentru " . $reservation['service'] . " a fost confirmată.<br>Data: " . $reservation['date'] . ".</p>";
        $mail->send();

        // Trimite emailul către doctor
        $mail->clearAddresses();
        $mail->addAddress('kissgezalevente@yahoo.com'); // Adresa reală a doctorului
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
