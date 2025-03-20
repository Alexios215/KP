<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $data = json_decode(file_get_contents('php://input'), true);

    $lat = $data['lat'];
    $lng = $data['lng'];

    $stmt = $pdo->prepare("INSERT INTO history (user_id, lat_set, long_set) VALUES (:user_id, :lat_set, :long_set)");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'lat_set' => $lat,
        'long_set' => $lng
    ]);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>