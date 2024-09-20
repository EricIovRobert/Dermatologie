<?php
require '../database/db_connect.php'; // Conexiunea la baza de date

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date']; // Ziua selectată

    // Selectează orele care sunt deja rezervate în acea zi și au statusul 'paid'
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(date, '%H:%i') as time FROM reservations WHERE DATE(date) = :date AND status = 'paid'");
    $stmt->execute(['date' => $date]);
    $reserved_times = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Obține orele deja rezervate (în format H:i)

    // Definim orele disponibile între 08:00 și 15:00
    $available_times = [];
    $start_time = strtotime('08:00');
    $end_time = strtotime('15:00');

    // Creează lista de ore disponibile excluzând orele rezervate
    for ($time = $start_time; $time <= $end_time; $time = strtotime('+1 hour', $time)) {
        $formatted_time = date('H:i', $time); // Formatăm ora ca 'H:i'
        // Adaugă ora în lista disponibilă doar dacă nu este rezervată
        if (!in_array($formatted_time, $reserved_times)) {
            $available_times[] = $formatted_time;
        }
    }

    // Returnează orele disponibile în format JSON
    echo json_encode($available_times);
}
?>
