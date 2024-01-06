<?php
ini_set("memory_limit", "1024M");
// Предполагается, что у вас уже установлено соединение с MySQLi
$mysqli = new mysqli("localhost", "root", "root", "logs");

// Проверка соединения
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Вызов хранимой процедуры
$sql = "CALL GetLogsData()";
$result = $mysqli->multi_query($sql);

// Извлечение данных в PHP-массив
$data = array();

// Используем первый набор данных
if ($result = $mysqli->store_result()) {
    // Открытие файла для записи
    //$outputFile = fopen("output.txt", "w");

    while ($row = $result->fetch_assoc()) {
        $id = intval($row['id']);
        $code = intval($row['code']);
        $size = intval($row['size']);
        //$date = date_create_from_format('d/M/Y:H:i:s O', $row['date']);
        $ip = $row['ip'];
        $ipBinary = inet_pton($ip);

        $row['ip'] = bin2hex($ipBinary);
        $row['id'] = $id;
        $row['code'] = $code;
        $row['size'] = $size;
        //$row['date'] = date_format($date, 'Y-m-d H:i:s');

        $data[] = $row;
    }

    // Закрытие файла после завершения записи
    //if ($outputFile) {
    //    fclose($outputFile);
    //}

    $result->free();  // Освобождение результата запроса
}

// Закрытие соединения с MySQLi
$mysqli->close();

// Преобразование PHP-массива в JSON для использования в JavaScript
$jsonData = json_encode($data);

// Вывод JSON-данных
echo $jsonData;
