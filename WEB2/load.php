<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "logs");
$start = microtime(true);
// Извлекаем информацию о состоянии из сессии
$current_line = isset($_SESSION['current_line']) ? $_SESSION['current_line'] : 1;
$file = fopen("log.txt", "r");

$pattern = '/^(\S+) - - \[([^\]]+)\] "(\S+) (\S+) (\S+)" (\d+) (\S+) "([^"]*)" "([^"]*)"$/';
if ($file) {


    $conn->begin_transaction();  // Начало транзакции
    try {
        // ... Ваш цикл для вставки данных ...

        // Чтение файла построчно
        while (($line = fgets($file)) !== false) {
            preg_match($pattern, trim($line), $matches);
            // Extracted values
            $ip = $matches[1];
            $date = $matches[2];
            $request_type = $matches[3];
            $request = $matches[4];
            $http = $matches[5];
            $status_code = intval($matches[6]);
            $size = $matches[7] === '-' ? null : $matches[7];
            $size = intval($size);
            $reference = $matches[8] === '-' ? null : $matches[8];
            $user_agent = $matches[9];
            // Вызываем процедуру добавления данных в БД
            // Подготовленный запрос для вызова хранимой процедуры
            $sql = "CALL AddData(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // Привязываем параметры
            $stmt->bind_param('sssssssss', $ip, $date, $request_type, $request, $http, $status_code, $size, $reference, $user_agent);

            // Выполняем запрос
            $stmt->execute();

            // Проверяем на ошибки
            if ($stmt->errno) {
                $strEr = "Error: " . $stmt->error;
            }

            // Закрываем подготовленный запрос
            $stmt->close();
        }
        $conn->commit();  // Фиксация изменений
    } catch (Exception $e) {
        $conn->rollback();  // Откат изменений в случае ошибки
    }





    fclose($file);
}
$conn->close();
$end = microtime(true);
$executionTime = $end - $start;
echo true;
?>