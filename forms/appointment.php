<?php
require '../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date']; // Ziua selectată

    // Selectează orele care sunt deja rezervate în acea zi
    $stmt = $pdo->prepare("SELECT TIME(date) as time FROM reservations WHERE DATE(date) = :date AND status = 'paid'");
    $stmt->execute(['date' => $date]);
    $reserved_times = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Definim orele disponibile între 08:00 și 15:00
    $available_times = [];
    $start_time = strtotime('08:00');
    $end_time = strtotime('15:00');

    for ($time = $start_time; $time <= $end_time; $time = strtotime('+1 hour', $time)) {
        $formatted_time = date('H:i', $time);
        // Adaugă ora în lista disponibilă doar dacă nu este rezervată
        if (!in_array($formatted_time, $reserved_times)) {
            $available_times[] = $formatted_time;
        }
    }

    // Log pentru depanare
    var_dump($reserved_times); // Vezi orele rezervate
    var_dump($available_times); // Vezi orele disponibile

    // Returnează orele disponibile în format JSON
    echo json_encode($available_times);
}
?>
