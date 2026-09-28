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
        "Location: garantias.php"
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
        "Location: garantias.php?mensaje=no_encontrado"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| COMPROBAR GARANTÍA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM garantias
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$garantia = $stmt->fetch();


if (!$garantia) {

    header(
        "Location: garantias.php?mensaje=no_encontrado"
    );

    exit;
}




$stmt = $pdo->prepare("
    DELETE FROM garantias
    WHERE id = :id
");

$stmt->execute([
    ":id" => $id
]);


header(
    "Location: garantias.php?mensaje=eliminado"
);

exit;