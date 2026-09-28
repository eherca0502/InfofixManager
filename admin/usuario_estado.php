<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requiereAdministrador();


/*
|--------------------------------------------------------------------------
| SOLO POST
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header(
        "Location: usuarios.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

verificarTokenCSRF();


/*
|--------------------------------------------------------------------------
| ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id || $id <= 0) {

    header(
        "Location: usuarios.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| IMPEDIR DESACTIVAR AL PROPIO USUARIO
|--------------------------------------------------------------------------
*/

if (
    (int) $id ===
    (int) $_SESSION["usuario_id"]
) {

    header(
        "Location: usuarios.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| OBTENER USUARIO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        activo
    FROM usuarios
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$usuario =
    $stmt->fetch();


if (!$usuario) {

    header(
        "Location: usuarios.php"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| CAMBIAR ESTADO
|--------------------------------------------------------------------------
*/

$nuevoEstado =
    ((int) $usuario["activo"] === 1)
        ? 0
        : 1;


$stmt = $pdo->prepare("
    UPDATE usuarios
    SET activo = :activo
    WHERE id = :id
");

$stmt->execute([
    ":activo" => $nuevoEstado,
    ":id" => $id
]);



$mensaje =
    $nuevoEstado === 1
        ? "activado"
        : "desactivado";


header(
    "Location: usuarios.php?mensaje=" .
    $mensaje
);

exit;