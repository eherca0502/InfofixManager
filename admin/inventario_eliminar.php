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
        "Location: inventario.php"
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
        "Location: inventario.php?mensaje=no_encontrado"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| COMPROBAR PRODUCTO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM inventario
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$producto = $stmt->fetch();


if (!$producto) {

    header(
        "Location: inventario.php?mensaje=no_encontrado"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ELIMINAR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM inventario
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);


header(
    "Location: inventario.php?mensaje=eliminado"
);

exit;