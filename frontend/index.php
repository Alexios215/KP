<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>МосСпортМаршрут</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="\data\logo.svg" type="image/svg">
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
    <main>
        <section>
            <h1>Построение маршрутов до ближайших спортивных объектов</h1>
            <p>
                Добро пожаловать в веб-приложение для поиска и построения маршрутов к ближайшим тренажерным городкам и
                залам в г. Москва.
            </p>
            <section class="image-section">
                <div class="image-link">
                    <img src="\data\img1.jpg" alt="Изображение 1">
                </div>
                <div class="image-link">
                    <img src="\data\img2.jpg" alt="Изображение 2">
                </div>
                <div class="image-link">
                    <img src="\data\img3.jpg" alt="Изображение 3">
                </div>
            </section>
            <a href="map.php" class="button">Перейти к карте</a>
        </section>

        <section>
            <div class="description left">
                <h2>Зачем нужно приложение для поиска спортивных объектов?</h2>
                <p>
                    В большом городе, таком как Москва, множество спортивных объектов, но не все они легко доступны для
                    поиска. Приложение упрощает процесс нахождения ближайших залов или городков, экономя время.
                </p>
            </div>

            <div class="description right">
                <h2>Зачем использовать открытые данные?</h2>
                <p>
                    Открытые данные обеспечивают прозрачность и актуальность информации. Использование таких данных
                    позволяет предоставлять пользователям достоверные сведения о местоположении, названиях и условиях
                    посещения объектов, что повышает доверие к приложению.
                </p>
            </div>

            <div class="description left">
                <h2>Зачем нужна функция построения маршрутов?</h2>
                <p>
                    Построение маршрутов помогает не только найти ближайший объект, но и добраться до него
                    наиболее удобным способом. Это особенно полезно в незнакомом районе или для оптимизации времени.
                </p>
            </div>

            <div class="description right">
                <h2>Зачем нужна история запросов?</h2>
                <p>
                    История запросов позволяет отслеживать свои предыдущие поиски, что удобно для
                    повторного посещения понравившихся мест или анализа своих спортивных привычек.
                </p>
            </div>
        </section>
    </main>
    <footer>
        <span>Проект использует открытые данные:</span>
        <a href="https://data.mos.ru" target="_blank">Портал открытых данных Правительства Москвы</a>
    </footer>
</body>

</html>