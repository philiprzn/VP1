<?php

require('config.php');
$err = []; //массив ошибок

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . CHARSET;
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, DB_USER, DB_PASS, $opt);

$data = json_decode($_POST['dataJson'], true);
$dataObj = [];
foreach ($data as $field){
    $name = $field['name'];
    $value = $field['value'];
    $value = trim($value);
    $value = strip_tags($value);
    $value = htmlspecialchars($value);
    switch ($name){
        case "email":
            $value = filter_var($value, FILTER_SANITIZE_EMAIL);
            if(filter_var($value, FILTER_VALIDATE_EMAIL) === false){
                array_push($err, 'Incorrect email');
            };
            break;
        case "no_callback":
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            break;
        case "street":
        case "home":
        case "appt":
            if (empty($value)){
                array_push($err, 'Incorrect adress');
            }
            break;
        case 'phone':
            if (empty($value)){
                array_push($err, 'No phone number');
            }
            break;
        case 'name':
            if (empty($value)){
                array_push($err, 'No name');
            }
            break;
        default: break;
    }
    $dataObj["$name"] = $value;
}

if (empty($err) === true) {
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM users WHERE email = ?");
    $stmt->execute(array($dataObj['email']));
    $row = $stmt->fetch();

    if($row['COUNT(id)'] > 0) {
        echo "Вы авторизованы!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (email, name, phone) VALUES (?, ?, ?)");
        $stmt->execute(array($dataObj['email'],$dataObj['name'],$dataObj['phone']));

        echo "Вы зарегистрированы!";
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(array($dataObj['email']));
    $user = $stmt->fetch();


    $adress ='ул.' . $dataObj['street'] . ' д.' . $dataObj['home'] . ' корп.' . $dataObj['part'] . ' этаж ' . $dataObj['floor'] . ' кв. ' . $dataObj['appt'];

    $stmt = $pdo->prepare("INSERT INTO orders (adress, comment, payment, no_callback, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(array($adress, $dataObj['comment'], $dataObj['payment'], $dataObj['no_callback'], $user['id']));
    $orderId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT COUNT(id) FROM orders WHERE user_id = ?");
    $stmt->execute(array($user['id']));
    $orderCount = $stmt->fetch();

    $thanks = $orderCount['COUNT(id)'] > 1 ? "Это уже ваш {$orderCount['COUNT(id)']} заказ!" : "Это ваш первый заказ!";
    $payment = $dataObj['payment'] = 'cash' ? 'Оплата наличными' : 'Оплата картой';
    $no_callbackStr = $dataObj['no_callback'] ? 'Без обратного звонка' : 'Вам перезвонят';
    $header = 'Заказ №' . $orderId;
    $message = <<<MAIL
Заказ будет доставлен по адресу: $adress.
$payment.
$no_callbackStr.
Ваш заказ: 

DarkBeefBurger 500 рублей, 1 шт.

Итого: 500 руб.

Спасибо! $thanks
MAIL;
    mail($dataObj['email'], $header, $message);
    print("$header \n $message");

    $pdo = null;
    $stmt = null;

} else {
    foreach ($err as $error) {
        print ("$error \n");
    }
}

?>