<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requiereAdministrador();




if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: usuarios.php");
    exit;
}

verificarTokenCSRF();


$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {

    header("Location: usuarios.php");
    exit;

}




if ((int) $id === (int) $_SESSION["usuario_id"]) {

    header("Location: usuarios.php");
    exit;

}



$stmt = $pdo->prepare("
    SELECT
        id,
        nombre,
        usuario,
        activo
    FROM usuarios
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$usuario = $stmt->fetch();


if (!$usuario) {

    header("Location: usuarios.php");
    exit;

}




$stmt = $pdo->prepare("
    UPDATE usuarios
    SET activo = 0
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);


header("Location: usuarios.php?mensaje=eliminado");

exit;