<?php

/*
|--------------------------------------------------------------------------
| Configuración segura de sesión
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    $secure = (
        !empty($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off"
    );

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $secure,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}


/*
|--------------------------------------------------------------------------
| Verificar que exista una sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["usuario_id"])) {

    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Verificar que el usuario siga activo
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/../config/database.php";


$usuarioId = filter_var(
    $_SESSION["usuario_id"],
    FILTER_VALIDATE_INT
);


if (!$usuarioId || $usuarioId <= 0) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}


$stmt = $pdo->prepare("
    SELECT
        id,
        nombre,
        rol,
        activo
    FROM usuarios
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $usuarioId
]);

$usuarioActual = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Usuario inexistente o desactivado
|--------------------------------------------------------------------------
*/

if (
    !$usuarioActual ||
    (int) $usuarioActual["activo"] !== 1
) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Actualizar información de sesión
|--------------------------------------------------------------------------
*/

$_SESSION["usuario_nombre"] =
    $usuarioActual["nombre"];

$_SESSION["usuario_rol"] =
    $usuarioActual["rol"];


/*
|--------------------------------------------------------------------------
| Función: verificar administrador
|--------------------------------------------------------------------------
*/

function requiereAdministrador()
{
    if (
        !isset($_SESSION["usuario_rol"]) ||
        $_SESSION["usuario_rol"] !== "Administrador"
    ) {

        header("Location: dashboard.php");
        exit;
    }
}




function obtenerTokenCSRF()
{
    if (empty($_SESSION["csrf_token"])) {

        $_SESSION["csrf_token"] =
            bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}



function verificarTokenCSRF()
{
    $tokenSesion =
        $_SESSION["csrf_token"] ?? "";

    $tokenFormulario =
        $_POST["csrf_token"] ?? "";


    if (
        $tokenSesion === "" ||
        $tokenFormulario === "" ||
        !hash_equals(
            $tokenSesion,
            $tokenFormulario
        )
    ) {

        http_response_code(403);

        die("Solicitud no válida.");
    }
}