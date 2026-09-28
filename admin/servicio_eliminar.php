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
        "Location: servicios.php"
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
        "Location: servicios.php?mensaje=no_encontrado"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| COMPROBAR SERVICIO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM servicios
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$servicio = $stmt->fetch();


if (!$servicio) {

    header(
        "Location: servicios.php?mensaje=no_encontrado"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| COMPROBAR GARANTÍA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM garantias
    WHERE servicio_id = :id
");

$stmt->execute([
    ":id" => $id
]);

$totalGarantias =
    (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| NO ELIMINAR SI TIENE GARANTÍA
|--------------------------------------------------------------------------
*/

if ($totalGarantias > 0) {

    header(
        "Location: servicios.php?mensaje=no_eliminar"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ELIMINAR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM servicios
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);


header(
    "Location: servicios.php?mensaje=eliminado"
);

exit;