<?php
$host = 'localhost';
$dbname = 'pizzeria';
$user = 'root';
$password = ''; // Twoje hasło do MySQL

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("❌ Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
