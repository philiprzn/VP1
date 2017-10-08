<?php
    require ('config.php');

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . CHARSET;
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opt);

    $stmt = $pdo->query("SELECT * FROM users");

    echo '<table border="1" callpadding="10">Пользователи<thead><th>ID</th><th>E-mail</th><th>Имя</th></thead>';

    foreach ($stmt as $row) {
        echo "<tr><td>{$row['id']}</td><td>{$row['email']}</td><td>{$row['name']}</td></tr>";
    };
    echo '</table>';

    $stmt = $pdo->query("SELECT * FROM orders");

    echo '<table border="1" cellpadding="10">Заказы<thead><th>ID</th><th>Адрес</th><th>Комментарий</th><th>Оплата</th><th>Обратный звонок</th><th>ID Пользователя</th></thead>';

    foreach ($stmt as $row) {
        echo "<tr><td>{$row['id']}</td><td>{$row['adress']}</td><td>{$row['comment']}</td><td>{$row['payment']}</td><td>{$row['callback']}</td><td>{$row['user_id']}</td></tr>";

    };
    echo '</table>';

    $pdo = null;
    $stmt = null;