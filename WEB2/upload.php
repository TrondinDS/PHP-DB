<?php
session_start();

//$start = microtime(true);

// Извлекаем информацию о состоянии из сессии
$current_line = isset($_SESSION['current_line']) ? $_SESSION['current_line'] : 1;

// Устанавливаем количество строк для чтения
$lines_to_read = 2000;
$conn = new mysqli("localhost", "root", "root", "logs");
// Используем SplFileObject для работы с файлом
$file = new SplFileObject("log.txt", "r");

// Устанавливаем позицию в файле
$file->seek($current_line);

$pattern = '/^(\S+) - - \[([^\]]+)\] "(\S+) (\S+) (\S+)" (\d+) (\S+) "([^"]*)" "([^"]*)"$/';

if ($file) {
    $conn->begin_transaction();  // Начало транзакции
    try {
        $count = 0;

        // Чтение файла построчно
        while (!$file->eof() && $count < $lines_to_read) {
            $line = $file->fgets();
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

            $count++;
        }

        // Запоминаем текущую позицию в файле
        $_SESSION['current_line'] = $file->key() + 1;

        $conn->commit();  // Фиксация изменений
    } catch (Exception $e) {
        $conn->rollback();  // Откат изменений в случае ошибки
    }

    $file = null; // Close the SplFileObject
}

$conn->close();
//$end = microtime(true);
//$executionTime = $end - $start;

echo true;
?>