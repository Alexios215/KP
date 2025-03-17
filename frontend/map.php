<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>МосСпортМаршрут</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="\data\logo.svg" type="image/svg">
  <script src="https://maps.api.2gis.ru/2.0/loader.js?pkg=full"></script>
  <script type="text/javascript">
    let map, clustersLayer, selectedPoint = null, routeLayer = null;

    const datasetIcons = {
      dataset1: '../data/icon1.png',
      dataset2: '../data/icon2.png'
    };

    DG.then(() => {
      map = DG.map('map', { center: [55.755825, 37.617630], zoom: 12 });
      clustersLayer = DG.layerGroup().addTo(map);

      map.on('click', (e) => {
        if (selectedPoint) {
          map.removeLayer(selectedPoint);
        }
        selectedPoint = DG.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
        selectedPoint.bindPopup("Выбрано местоположение").openPopup();
      });
    });

    async function findNearestPoints(lat, lng) {
      try {
        const response = await fetch(`../backend/find_nearest.php?lat=${lat}&lng=${lng}`);
        if (!response.ok) {
          throw new Error(`Ошибка сети: ${response.statusText}`);
        }
        const data = await response.json();
        console.log("Полученные данные:", data);

        if (data.success) {
          plotRoute(data.dataset1, data.dataset2);
        } else {
          alert('Не удалось найти ближайшие точки.');
        }
      } catch (error) {
        console.error('Ошибка запроса:', error);
        alert('Ошибка при загрузке данных.');
      }
    }

    function plotRoute(dataset1, dataset2) {
      clustersLayer.clearLayers();

      // Отображаем выбранную точку (если она есть)
      if (selectedPoint) {
        DG.marker(selectedPoint.getLatLng()).addTo(clustersLayer).bindPopup("Выбранная точка").openPopup();
      }

      // Отображаем объекты из dataset1
      dataset1.forEach(point => {
        DG.marker([point.lat_set, point.long_set], {
          icon: DG.icon({
            iconUrl: datasetIcons.dataset1, // Путь к иконке
            iconSize: [30, 30], // Размер иконки (ширина, высота)
            iconAnchor: [15, 15] // Смещение точки привязки иконки (центр иконки)
          })
        }).addTo(clustersLayer).bindPopup(point.name_set);
      });

      // Отображаем объекты из dataset2
      dataset2.forEach(point => {
        DG.marker([point.lat_set, point.long_set], {
          icon: DG.icon({
            iconUrl: datasetIcons.dataset2, // Путь к иконке
            iconSize: [30, 30], // Размер иконки (ширина, высота)
            iconAnchor: [15, 15] // Смещение точки привязки иконки (центр иконки)
          })
        }).addTo(clustersLayer).bindPopup(point.name_set);
      });
    }

    function handleFormSubmit(event) {
      event.preventDefault();
      if (selectedPoint) {
        const { lat, lng } = selectedPoint.getLatLng();
        findNearestPoints(lat, lng);
      } else {
        alert("Выберите точку на карте.");
      }
    }

  </script>
</head>

<body>
  <header>
    <div class="header-left">
      <img src="\data\logo.svg" alt="Логотип" class="logo">
      <span class="project-title">МосСпортМаршрут</span>
    </div>
    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="index.php">Главная</a></li>
        <li><a href="map.php">Карта</a></li>
        <li><a href="auth.php">Вход</a></li>
      </ul>
    </nav>
  </header>

  <div class="content-container">
    <div id="map"></div>
    <div class="control-panel">
      <h2>Пульт управления</h2>
      <form onsubmit="handleFormSubmit(event)">

        <button type="submit">Подтвердить</button>
        <div class="instructions">
          <p>Для использования приложения выполните следующие шаги:</p>
          <ul>
            <li>Кликните в любое место на карте, чтобы установить маркер. Эта точка будет использоваться для поиска
              ближайших спортивных объектов.</li>
            <li>Нажмите кнопку "Подтвердить", чтобы найти 2 ближайших тренажерных городка и 1 ближайший
              Спортивный объект. Маршрут до этих точек автоматически отобразится на карте.</li>
            <li>После поиска вы увидите найденные точки на карте, а также маршруты, соединяющие их с выбранной вами
              точкой.</li>
            <li>Если вы зарегистрируетесь и войдете в систему, то сможете сохранять историю своих запросов и
              просматривать её позже.</li>
          </ul>
        </div>
      </form>
    </div>
  </div>

  <footer>
    <span>Проект использует открытые данные:</span>
    <a href="https://data.mos.ru" target="_blank">Портал открытых данных Правительства Москвы</a>
  </footer>
</body>

</html>