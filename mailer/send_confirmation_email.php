<?php

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.example.com'; // Setează serverul de email
$mail->SMTPAuth = true;
$mail->Username = 'your_email@example.com'; // Adaugă emailul tău
$mail->Password = 'your_password'; // Adaugă parola ta
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

// Trimite emailul către client
$mail->setFrom('your_email@example.com', 'Bect Dermatologie');
$mail->addAddress($reservation['email']); // Emailul clientului

$mail->isHTML(true);
$mail->Subject = 'Confirmare rezervare și plată';
$mail->Body = "<p>Rezervarea ta pentru " . $reservation['service'] . " a fost confirmată. Data: " . $reservation['date'] . ".</p>";

$mail->send();

// Trimite emailul către doctor
$mail->clearAddresses();
$mail->addAddress('doctor_email@example.com'); // Emailul doctorului
$mail->send();
?>