<?php
require 'db.php';

if (!isset($_GET['lat']) || !isset($_GET['lng'])) {
    echo json_encode(['success' => false, 'error' => 'Координаты не переданы']);
    exit;
}

$lat = floatval($_GET['lat']);
$lng = floatval($_GET['lng']);

function findNearestPoints($pdo, $table, $lat, $lng, $limit)
{
    $sql = "SELECT name_set, adm_area_set, address_set, phone_set, lat_set, long_set, 
                   (6371 * acos(cos(radians(:lat)) * cos(radians(lat_set)) 
                   * cos(radians(long_set) - radians(:lng)) + sin(radians(:lat)) 
                   * sin(radians(lat_set)))) * 1000 AS distance 
            FROM $table 
            ORDER BY distance 
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lng', $lng);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $nearestFromDataset1 = findNearestPoints($pdo, 'dataset1', $lat, $lng, 2);
    $nearestFromDataset2 = findNearestPoints($pdo, 'dataset2', $lat, $lng, 1);

    echo json_encode([
        'success' => true,
        'dataset1' => $nearestFromDataset1,
        'dataset2' => $nearestFromDataset2
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
    error_log(json_encode($nearestFromDataset1));
    error_log(json_encode($nearestFromDataset2));
}
?>