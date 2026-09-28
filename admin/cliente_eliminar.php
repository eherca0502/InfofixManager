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
        "Location: clientes.php"
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
        "Location: clientes.php?mensaje=no_encontrado"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| COMPROBAR CLIENTE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM clientes
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$cliente = $stmt->fetch();


if (!$cliente) {

    header(
        "Location: clientes.php?mensaje=no_encontrado"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| COMPROBAR RELACIONES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT

        (
            SELECT COUNT(*)
            FROM equipos
            WHERE cliente_id = :id1
        ) AS equipos,

        (
            SELECT COUNT(*)
            FROM servicios
            WHERE cliente_id = :id2
        ) AS servicios,

        (
            SELECT COUNT(*)
            FROM tickets
            WHERE cliente_id = :id3
        ) AS tickets
");

$stmt->execute([
    ":id1" => $id,
    ":id2" => $id,
    ":id3" => $id
]);

$relaciones =
    $stmt->fetch();


/*
|--------------------------------------------------------------------------
| NO ELIMINAR SI TIENE HISTORIAL
|--------------------------------------------------------------------------
*/

if (
    (int) $relaciones["equipos"] > 0 ||
    (int) $relaciones["servicios"] > 0 ||
    (int) $relaciones["tickets"] > 0
) {

    header(
        "Location: clientes.php?mensaje=no_eliminar"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ELIMINAR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM clientes
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);




header(
    "Location: clientes.php?mensaje=eliminado"
);

exit;