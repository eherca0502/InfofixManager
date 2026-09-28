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
        "Location: equipos.php"
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
        "Location: equipos.php?mensaje=no_encontrado"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| COMPROBAR EQUIPO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM equipos
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$equipo = $stmt->fetch();


if (!$equipo) {

    header(
        "Location: equipos.php?mensaje=no_encontrado"
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
            FROM servicios
            WHERE equipo_id = :id1
        ) AS servicios,

        (
            SELECT COUNT(*)
            FROM tickets
            WHERE equipo_id = :id2
        ) AS tickets
");

$stmt->execute([
    ":id1" => $id,
    ":id2" => $id
]);

$relaciones =
    $stmt->fetch();


/*
|--------------------------------------------------------------------------
| NO ELIMINAR SI TIENE HISTORIAL
|--------------------------------------------------------------------------
*/

if (
    (int) $relaciones["servicios"] > 0 ||
    (int) $relaciones["tickets"] > 0
) {

    header(
        "Location: equipos.php?mensaje=no_eliminar"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ELIMINAR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM equipos
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);


header(
    "Location: equipos.php?mensaje=eliminado"
);

exit;