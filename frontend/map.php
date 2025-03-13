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
    let map, clustersLayer;

    DG.then(() => {
      map = DG.map('map', { center: [55.755825, 37.617630], zoom: 12 });
      clustersLayer = DG.layerGroup().addTo(map);
    });

    const datasetIcons = {
      dataset1: '../data/icon1.png',
      dataset2: '../data/icon2.png'
    };

    async function fetchData(url) {
      try {
        const response = await fetch(url);

        if (!response.ok) {
          throw new Error(`Ошибка сети: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success) {
          processClusters(data.clusters);
        } else {
          alert(data.error || 'Ошибка обработки данных');
        }
      } catch (error) {
        console.error('Ошибка запроса:', error);
        alert('Ошибка при загрузке данных.');
      }
    }

    function handleFormSubmit(event) {
      event.preventDefault();
      //Что то
      fetchData(url);
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
            <li></li>
            <li></li>
            <li></li>
            <li></li>
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