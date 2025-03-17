<?php
require 'db.php';

ini_set('max_execution_time', 180);

function importJSON1($pdo)
{
    ini_set('memory_limit', '50G');
    $filePath = __DIR__ . '/../data/f_dataset1.json';
    $fileContent = file_get_contents($filePath);

    if ($fileContent === false) {
        die("Ошибка при чтении файла.");
    }

    $data = json_decode($fileContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Ошибка при декодировании JSON файла: " . json_last_error_msg());
    }

    foreach ($data as $location) {
        if (
            isset($location["ObjectName"]) &&
            isset($location["AdmArea"]) &&
            isset($location["Address"]) &&
            isset($location["HelpPhone"]) &&
            isset($location["Paid"]) &&
            isset($location["geoData"]["coordinates"])
        ) {
            $latitude = $location["geoData"]["coordinates"][1];
            $longitude = $location["geoData"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO dataset1 (name_set, adm_area_set, address_set, phone_set, paid_set, lat_set, long_set) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $location['ObjectName'],
                $location['AdmArea'],
                $location['Address'],
                $location['HelpPhone'],
                $location['Paid'],
                $latitude,
                $longitude
            ]);
        } else {
            error_log("Пропущена запись: " . json_encode($location) . "\n", 3);
        }
    }

    echo "Импорт JSON данных для сета 1 завершён.\n";
}
function importJSON2($pdo)
{
    ini_set('memory_limit', '50G');
    $filePath = __DIR__ . '/../data/f_dataset2.json';
    $fileContent = file_get_contents($filePath);

    if ($fileContent === false) {
        die("Ошибка при чтении файла.");
    }

    $data = json_decode($fileContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Ошибка при декодировании JSON файла: " . json_last_error_msg());
    }

    foreach ($data as $location) {
        if (
            isset($location["ObjectName"]) &&
            isset($location["AdmArea"]) &&
            isset($location["Address"]) &&
            isset($location["HelpPhone"]) &&
            isset($location["Paid"]) &&
            isset($location["geoData"]["coordinates"])
        ) {
            $latitude = $location["geoData"]["coordinates"][1];
            $longitude = $location["geoData"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO dataset2 (name_set, adm_area_set, address_set, phone_set, paid_set, lat_set, long_set) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $location['ObjectName'],
                $location['AdmArea'],
                $location['Address'],
                $location['HelpPhone'],
                $location['Paid'],
                $latitude,
                $longitude
            ]);
        } else {
            error_log("Пропущена запись: " . json_encode($location) . "\n", 3);
        }
    }

    echo "Импорт JSON данных для сета 2 завершён.\n";
}

try {
    importJSON2($pdo);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>