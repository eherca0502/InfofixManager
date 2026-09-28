<?php

$host = "localhost";
$dbname = "infofix_manager";
$username = "root";
$password = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    error_log(
        "Infofix Manager - Error de conexión DB: "
        . $e->getMessage()
    );

    die(
        "No fue posible conectar con la base de datos. "
        . "Intenta nuevamente más tarde."
    );

}