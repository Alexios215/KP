<?php
session_start();
require '../backend/db.php';

$error = '';
$success = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT user_id, username, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            header('Location: map.php');
            exit();
        } else {
            $error = 'Неверное имя пользователя или пароль.';
        }
    } else {
        $error = 'Заполните все поля.';
    }
}

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Заполните все поля.';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают.';
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);

        if ($stmt->fetch()) {
            $error = 'Пользователь с таким именем уже существует.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->execute([
                'username' => $username,
                'password' => $hashedPassword
            ]);

            $success = 'Регистрация успешна! Можете <a href="auth.php">войти</a>.';
        }
    }
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $error = 'Заполните все поля.';
    } elseif ($new_password !== $confirm_new_password) {
        $error = 'Новые пароли не совпадают.';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($current_password, $user['password'])) {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
            $stmt->execute([
                'password' => $hashedPassword,
                'user_id' => $_SESSION['user_id']
            ]);

            $success = 'Пароль успешно изменен.';
        } else {
            $error = 'Текущий пароль неверен.';
        }
    }
}
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: auth.php');
    exit();
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
        <div class="auth-container">
            <?php if (isset($_SESSION['user_id'])): ?>
                <h2>Ваш аккаунт</h2>
                <div class="user-info">
                    <h3>Изменение пароля</h3>
                    <form method="POST" action="auth.php">
                        <label for="current_password">Текущий пароль:</label>
                        <input type="password" id="current_password" name="current_password" required>

                        <label for="new_password">Новый пароль:</label>
                        <input type="password" id="new_password" name="new_password" required>

                        <label for="confirm_new_password">Подтвердите новый пароль:</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" required>

                        <button type="submit" name="change_password" class="button">Изменить пароль</button>
                    </form>

                    <h3>История запросов</h3>
                    <?php
                    $stmt = $pdo->prepare("SELECT lat_set, long_set, created_at FROM history WHERE user_id = :user_id ORDER BY created_at DESC");
                    $stmt->execute(['user_id' => $_SESSION['user_id']]);
                    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <?php if (empty($history)): ?>
                        <p>История пуста.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Дата и время</th>
                                    <th>Действие</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $entry): ?>
                                    <tr>
                                        <td><?= (new DateTime($entry['created_at']))->format('d.m.Y H:i:s') ?></td>
                                        <td>
                                            <form method="GET" action="map.php">
                                                <input type="hidden" name="lat" value="<?= htmlspecialchars($entry['lat_set']) ?>">
                                                <input type="hidden" name="lng" value="<?= htmlspecialchars($entry['long_set']) ?>">
                                                <button type="submit" class="button small">Перейти</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <form method="POST" action="auth.php">
                        <button type="submit" name="logout" class="button">Выйти</button>
                    </form>
                </div>
            <?php else: ?>
                <h2>Авторизация и регистрация</h2>
                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <?php if ($success): ?>
                    <p class="success"><?= $success ?></p>
                <?php endif; ?>

                <div class="auth-forms">
                    <form id="login-form" method="POST" action="auth.php">
                        <h3>Вход</h3>
                        <label for="username">Имя пользователя:</label>
                        <input type="text" id="username" name="username" required>

                        <label for="password">Пароль:</label>
                        <input type="password" id="password" name="password" required>

                        <button type="submit" name="login" class="button">Войти</button>
                        <p>Нет аккаунта? <a href="#register-form" onclick="showRegisterForm()">Зарегистрироваться</a></p>
                    </form>

                    <form id="register-form" method="POST" action="auth.php" style="display:none;">
                        <h3>Регистрация</h3>
                        <label for="register-username">Имя пользователя:</label>
                        <input type="text" id="register-username" name="username" required>

                        <label for="register-password">Пароль:</label>
                        <input type="password" id="register-password" name="password" required>

                        <label for="register-confirm-password">Подтвердите пароль:</label>
                        <input type="password" id="register-confirm-password" name="confirm_password" required>

                        <button type="submit" name="register" class="button">Зарегистрироваться</button>
                        <p>Уже есть аккаунт? <a href="#login-form" onclick="showLoginForm()">Войти</a></p>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        function showRegisterForm() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('register-form').style.display = 'block';
        }

        function showLoginForm() {
            document.getElementById('register-form').style.display = 'none';
            document.getElementById('login-form').style.display = 'block';
        }
    </script>
    <footer>
        <span>Проект использует открытые данные:</span>
        <a href="https://data.mos.ru" target="_blank">Портал открытых данных Правительства Москвы</a>
    </footer>
</body>

</html>