<?php
session_start();
require '../backend/db.php';

if (isset($_SESSION['user_id'])) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lat']) && isset($_POST['lng'])) {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $stmt = $pdo->prepare("INSERT INTO history (user_id, lat_set, long_set) VALUES (:user_id, :lat_set, :long_set)");
    $stmt->execute([
      'user_id' => $_SESSION['user_id'],
      'lat_set' => $lat,
      'long_set' => $lng
    ]);
  }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>СпортМаршрут</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="/data/logo.svg" type="image/svg">
  <link rel="stylesheet" href="../leaflet/leaflet.css" />
  <script src="../leaflet/leaflet.js"></script>
  <link rel="stylesheet" href="../leaflet/leaflet-routing-machine.css" />
  <script src="../leaflet/leaflet-routing-machine.js"></script>
  <script src="../leaflet/lrm-graphhopper-1.2.0.min.js"></script>
  <script type="text/javascript">
    let map, clustersLayer, selectedPoint = null, routeLayers = [];

    const datasetIcons = {
      dataset1: L.icon({
        iconUrl: '../data/icon1.png',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
      }),
      dataset2: L.icon({
        iconUrl: '../data/icon2.png',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
      }),
      selected: L.icon({
        iconUrl: '../data/icon_s.png',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
      })
    };

    document.addEventListener('DOMContentLoaded', () => {
      map = L.map('map').setView([55.755825, 37.617630], 12);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);

      clustersLayer = L.layerGroup().addTo(map);

      map.on('click', (e) => {
        clearMap();

        selectedPoint = L.marker([e.latlng.lat, e.latlng.lng], {
          icon: datasetIcons.selected
        }).addTo(map);
        selectedPoint.bindPopup("Выбрано местоположение").openPopup();
      });

      // Проверка GET-параметров
      const urlParams = new URLSearchParams(window.location.search);
      const lat = urlParams.get('lat');
      const lng = urlParams.get('lng');

      // Если параметры переданы, вызываем findNearestPoints
      if (lat && lng) {
        L.marker([lat, lng], {
          icon: datasetIcons.selected
        }).addTo(map);
        findNearestPoints(parseFloat(lat), parseFloat(lng));
      }

      console.log(L.Routing);
      console.log(L.Routing.GraphHopper);
    });

    function clearMap() {
      clustersLayer.clearLayers();
      routeLayers.forEach(layer => map.removeControl(layer));
      routeLayers = [];
      if (selectedPoint) {
        map.removeLayer(selectedPoint);
        selectedPoint = null;
      }
    }

    async function findNearestPoints(lat, lng) {
      try {
        const response = await fetch(`../backend/find_nearest.php?lat=${lat}&lng=${lng}`);
        if (!response.ok) {
          throw new Error(`Ошибка сети: ${response.statusText}`);
        }
        const data = await response.json();
        console.log("Полученные данные:", data);

        if (data.success) {
          plotPoints(data.dataset1, data.dataset2);
          plotRoutesToPoints(lat, lng, data.dataset1, data.dataset2);
        } else {
          alert('Не удалось найти ближайшие точки.');
        }
      } catch (error) {
        console.error('Ошибка запроса:', error);
        alert('Ошибка при загрузке данных.');
      }
    }

    function plotPoints(dataset1, dataset2) {
      if (selectedPoint) {
        selectedPoint.addTo(clustersLayer).bindPopup("Выбранная точка");
      }

      dataset1.forEach(point => {
        L.marker([point.lat_set, point.long_set], {
          icon: datasetIcons.dataset1
        }).addTo(clustersLayer).bindPopup(`${point.name_set}, ${point.adm_area_set}, ${point.address_set}`);
      });

      dataset2.forEach(point => {
        L.marker([point.lat_set, point.long_set], {
          icon: datasetIcons.dataset2
        }).addTo(clustersLayer).bindPopup(`${point.name_set}, ${point.adm_area_set}, ${point.address_set}`);
      });
    }

    function getRandomColor() {
      // Выбираем случайным образом, какая компонента будет максимальной (красная, зеленая или синяя)
      const component = Math.floor(Math.random() * 3); // 0 - красный, 1 - зеленый, 2 - синий

      const r = component === 0 ? Math.floor(Math.random() * 128) + 128 : Math.floor(Math.random() * 128);
      const g = component === 1 ? Math.floor(Math.random() * 128) + 128 : Math.floor(Math.random() * 128);
      const b = component === 2 ? Math.floor(Math.random() * 128) + 128 : Math.floor(Math.random() * 128);

      const toHex = (value) => value.toString(16).padStart(2, '0');
      return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
    }

    function plotRoutesToPoints(startLat, startLng, dataset1, dataset2) {
      const routes = [...dataset1.slice(0, 2), ...dataset2.slice(0, 1)];

      routes.forEach((point) => {
        const router = new L.Routing.GraphHopper('7a1d6633-3035-4e02-93d3-7de8ed0b9b9c', {
          urlParameters: {
            vehicle: 'foot'
          }
        });

        const route = L.Routing.control({
          waypoints: [
            L.latLng(startLat, startLng),
            L.latLng(point.lat_set, point.long_set)
          ],
          routeWhileDragging: true,
          router: router,
          createMarker: () => null,
          lineOptions: {
            styles: [{ color: getRandomColor(), opacity: 0.6, weight: 5 }]
          },
          show: false
        }).addTo(map);
        routeLayers.push(route);
      });
    }

    function handleFormSubmit(event) {
      event.preventDefault();
      if (selectedPoint) {
        const { lat, lng } = selectedPoint.getLatLng();
        findNearestPoints(lat, lng);

        // Если пользователь авторизован, отправляем данные на сервер для сохранения истории
        <?php if (isset($_SESSION['user_id'])): ?>
          fetch('../backend/save_history.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              lat: lat,
              lng: lng
            })
          })
            .then(response => {
              if (!response.ok) {
                throw new Error('Ошибка при сохранении истории');
              }
              return response.json();
            })
            .then(data => {
              if (data.success) {
                console.log('История успешно сохранена');
              } else {
                console.error('Ошибка при сохранении истории:', data.message);
              }
            })
            .catch(error => {
              console.error('Ошибка сети:', error);
            });
        <?php endif; ?>
      } else {
        alert("Выберите точку на карте.");
      }
    }
  </script>
</head>

<body>
  <header>
    <div class="header-left">
      <img src="/data/logo.svg" alt="Логотип" class="logo">
      <span class="project-title">СпортМаршрут</span>
    </div>
    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="index.php">Главная</a></li>
        <li><a href="map.php">Карта</a></li>
        <li><a href="auth.php"><?= isset($_SESSION['user_id']) ? 'Аккаунт' : 'Войти' ?></a></li>
      </ul>
    </nav>
    </header>

    <main>
      <div class="content-container">
        <div id="map"></div>
        <div class="control-panel">
          <h2>Пульт управления</h2>
          <form onsubmit="handleFormSubmit(event)">
            <button type="submit" class="button">Подтвердить</button>
            <div class="instructions">
              <p>Для использования приложения выполните следующие шаги:</p>
              <ul>
                <li>Кликните в любое место на карте, чтобы установить маркер. Эта точка будет использоваться для поиска
                  ближайших спортивных объектов.</li>
                <li>Нажмите кнопку "Подтвердить", чтобы найти 2 ближайших тренажерных городка и 1 ближайший спортивный
                  объект. Маршрут до этих точек автоматически отобразится на карте.</li>
                <li>После поиска вы увидите найденные точки на карте, а также маршруты, соединяющие их с выбранной вами
                  точкой.</li>
                <li>Если вы зарегистрируетесь и войдете в систему, то сможете сохранять историю своих запросов и
                  просматривать её позже.</li>
              </ul>
            </div>
          </form>
        </div>
      </div>
    </main>

    <footer>
      <span>Проект использует открытые данные:</span>
      <a href="https://data.mos.ru" target="_blank">Портал открытых данных Правительства Москвы</a>
    </footer>
</body>

</html>