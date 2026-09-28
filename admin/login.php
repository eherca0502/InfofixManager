<?php

/*
|--------------------------------------------------------------------------
| Configuración de sesión
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Conexión a la base de datos
|--------------------------------------------------------------------------
*/

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Configuración del límite de intentos
|--------------------------------------------------------------------------
*/

$maxIntentos = 5;
$tiempoBloqueo = 15 * 60; // 15 minutos


/*
|--------------------------------------------------------------------------
| Variables de login
|--------------------------------------------------------------------------
*/

$error = "";


/*
|--------------------------------------------------------------------------
| Inicializar contador de intentos
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["login_intentos"])) {
    $_SESSION["login_intentos"] = 0;
}

if (!isset($_SESSION["login_bloqueado_hasta"])) {
    $_SESSION["login_bloqueado_hasta"] = 0;
}


/*
|--------------------------------------------------------------------------
| Comprobar si existe un bloqueo activo
|--------------------------------------------------------------------------
*/

$ahora = time();

if (
    $_SESSION["login_bloqueado_hasta"] > 0 &&
    $ahora < $_SESSION["login_bloqueado_hasta"]
) {

    $segundosRestantes =
        $_SESSION["login_bloqueado_hasta"] - $ahora;

    $minutosRestantes =
        max(1, (int) ceil($segundosRestantes / 60));

    $error =
        "Demasiados intentos fallidos. "
        . "Intenta nuevamente en "
        . $minutosRestantes
        . " minuto(s).";
}


/*
|--------------------------------------------------------------------------
| Procesar formulario
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $error === ""
) {

    $usuario = trim($_POST["usuario"] ?? "");
    $password = $_POST["password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validar campos
    |--------------------------------------------------------------------------
    */

    if (
        $usuario === "" ||
        $password === ""
    ) {

        $error = "Completa todos los campos.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Buscar usuario activo
        |--------------------------------------------------------------------------
        */

        $sql = "SELECT *
                FROM usuarios
                WHERE usuario = :usuario
                AND activo = 1
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":usuario" => $usuario
        ]);

        $usuarioDB = $stmt->fetch();


        /*
        |--------------------------------------------------------------------------
        | Verificar contraseña
        |--------------------------------------------------------------------------
        */

        if (
            $usuarioDB &&
            password_verify(
                $password,
                $usuarioDB["password"]
            )
        ) {

            /*
            |--------------------------------------------------------------------------
            | Login correcto
            |--------------------------------------------------------------------------
            */

            session_regenerate_id(true);


            /*
            |--------------------------------------------------------------------------
            | Guardar información del usuario
            |--------------------------------------------------------------------------
            */

            $_SESSION["usuario_id"] =
                $usuarioDB["id"];

            $_SESSION["usuario_nombre"] =
                $usuarioDB["nombre"];

            $_SESSION["usuario_rol"] =
                $usuarioDB["rol"];


            /*
            |--------------------------------------------------------------------------
            | Crear nuevo token CSRF
            |--------------------------------------------------------------------------
            */

            $_SESSION["csrf_token"] =
                bin2hex(random_bytes(32));


            /*
            |--------------------------------------------------------------------------
            | Reiniciar contador de intentos
            |--------------------------------------------------------------------------
            */

            $_SESSION["login_intentos"] = 0;

            $_SESSION["login_bloqueado_hasta"] = 0;


            /*
            |--------------------------------------------------------------------------
            | Entrar al sistema
            |--------------------------------------------------------------------------
            */

            header("Location: dashboard.php");
            exit;

        } else {

            /*
            |--------------------------------------------------------------------------
            | Login incorrecto
            |--------------------------------------------------------------------------
            */

            $_SESSION["login_intentos"]++;


            /*
            |--------------------------------------------------------------------------
            | Comprobar límite
            |--------------------------------------------------------------------------
            */

            if (
                $_SESSION["login_intentos"] >=
                $maxIntentos
            ) {

                $_SESSION["login_bloqueado_hasta"] =
                    time() + $tiempoBloqueo;

                $_SESSION["login_intentos"] = 0;

                $error =
                    "Demasiados intentos fallidos. "
                    . "El acceso ha sido bloqueado temporalmente "
                    . "durante 15 minutos.";

            } else {

                $intentosRestantes =
                    $maxIntentos -
                    $_SESSION["login_intentos"];

                $error =
                    "Usuario o contraseña incorrectos. "
                    . "Intentos restantes: "
                    . $intentosRestantes
                    . ".";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Iniciar sesión | Infofix Manager
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body class="login-body">

    <div class="login-container">


        <!--
        ============================================================
        MARCA
        ============================================================
        -->

        <div class="login-brand">

            <div class="login-logo">
                IF
            </div>

            <h1>
                Infofix
            </h1>

            <span>
                Manager
            </span>

        </div>


        <!--
        ============================================================
        TARJETA DE LOGIN
        ============================================================
        -->

        <div class="login-card">

            <div class="login-header">

                <h2>
                    Bienvenido
                </h2>

                <p>
                    Inicia sesión para acceder al sistema.
                </p>

            </div>


            <!--
            ========================================================
            MENSAJE DE ERROR
            ========================================================
            -->

            <?php if ($error !== ""): ?>

                <div class="alert-error">

                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                </div>

            <?php endif; ?>


            <!--
            ========================================================
            FORMULARIO
            ========================================================
            -->

            <form method="POST">

                <div class="form-group">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Ingresa tu usuario"
                        autocomplete="username"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Iniciar sesión
                </button>

            </form>

        </div>


     

        <a
            href="../index.php"
            class="back-home"
        >
            ← Volver al sitio
        </a>


    </div>

</body>

</html>