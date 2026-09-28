<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requiereAdministrador();


/*
|--------------------------------------------------------------------------
| Obtener ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {

    header("Location: usuarios.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener usuario
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre,
        usuario,
        password,
        rol,
        activo
    FROM usuarios
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ":id" => $id
]);

$usuarioEditar = $stmt->fetch();


if (!$usuarioEditar) {

    header("Location: usuarios.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Variables del formulario
|--------------------------------------------------------------------------
*/

$nombre = $usuarioEditar["nombre"];
$usuario = $usuarioEditar["usuario"];
$rol = $usuarioEditar["rol"];
$activo = (int) $usuarioEditar["activo"];

$errores = [];


/*
|--------------------------------------------------------------------------
| Procesar formulario
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verificarTokenCSRF();

    $nombre = trim($_POST["nombre"] ?? "");
    $usuario = trim($_POST["usuario"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmarPassword = $_POST["confirmar_password"] ?? "";
    $rol = $_POST["rol"] ?? "Tecnico";
    $activo = isset($_POST["activo"]) ? 1 : 0;


    /*
    |--------------------------------------------------------------------------
    | Validar nombre
    |--------------------------------------------------------------------------
    */

    if ($nombre === "") {

        $errores[] = "El nombre es obligatorio.";

    } elseif (strlen($nombre) < 3) {

        $errores[] = "El nombre debe tener al menos 3 caracteres.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validar usuario
    |--------------------------------------------------------------------------
    */

    if ($usuario === "") {

        $errores[] = "El nombre de usuario es obligatorio.";

    } elseif (strlen($usuario) < 3) {

        $errores[] = "El usuario debe tener al menos 3 caracteres.";

    } elseif (strlen($usuario) > 50) {

        $errores[] = "El usuario no puede tener más de 50 caracteres.";

    }


    /*
    |--------------------------------------------------------------------------
    | Verificar usuario duplicado
    |--------------------------------------------------------------------------
    */

    if ($usuario !== "") {

        $stmt = $pdo->prepare("
            SELECT
                id
            FROM usuarios
            WHERE usuario = :usuario
            AND id <> :id
            LIMIT 1
        ");

        $stmt->execute([
            ":usuario" => $usuario,
            ":id" => $id
        ]);

        if ($stmt->fetch()) {

            $errores[] =
                "Ese nombre de usuario ya está registrado por otra cuenta.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Validar contraseña
    |--------------------------------------------------------------------------
    */

    if ($password !== "") {

        if (strlen($password) < 6) {

            $errores[] =
                "La nueva contraseña debe tener al menos 6 caracteres.";

        }

        if ($password !== $confirmarPassword) {

            $errores[] =
                "Las contraseñas no coinciden.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Validar contraseña de confirmación
    |--------------------------------------------------------------------------
    */

    if (
        $password === "" &&
        $confirmarPassword !== ""
    ) {

        $errores[] =
            "Escribe una nueva contraseña o deja ambos campos vacíos.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validar rol
    |--------------------------------------------------------------------------
    */

    $rolesPermitidos = [
        "Administrador",
        "Tecnico"
    ];

    if (!in_array($rol, $rolesPermitidos, true)) {

        $errores[] =
            "El rol seleccionado no es válido.";

    }


    /*
    |--------------------------------------------------------------------------
    | Evitar que el administrador actual pierda privilegios
    |--------------------------------------------------------------------------
    */

    if (
        (int) $id === (int) $_SESSION["usuario_id"]
        && $rol !== "Administrador"
    ) {

        $errores[] =
            "No puedes cambiar tu propia cuenta de Administrador a Técnico.";

    }


    /*
    |--------------------------------------------------------------------------
    | Evitar que el administrador actual se desactive
    |--------------------------------------------------------------------------
    */

    if (
        (int) $id === (int) $_SESSION["usuario_id"]
        && $activo === 0
    ) {

        $errores[] =
            "No puedes desactivar tu propia cuenta.";

    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar usuario
    |--------------------------------------------------------------------------
    */

    if (empty($errores)) {


        /*
        |--------------------------------------------------------------------------
        | Si escribió nueva contraseña
        |--------------------------------------------------------------------------
        */

        if ($password !== "") {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET
                    nombre = :nombre,
                    usuario = :usuario,
                    password = :password,
                    rol = :rol,
                    activo = :activo
                WHERE id = :id
            ");

            $stmt->execute([
                ":nombre" => $nombre,
                ":usuario" => $usuario,
                ":password" => $passwordHash,
                ":rol" => $rol,
                ":activo" => $activo,
                ":id" => $id
            ]);

        } else {


            /*
            |--------------------------------------------------------------------------
            | Mantener contraseña actual
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET
                    nombre = :nombre,
                    usuario = :usuario,
                    rol = :rol,
                    activo = :activo
                WHERE id = :id
            ");

            $stmt->execute([
                ":nombre" => $nombre,
                ":usuario" => $usuario,
                ":rol" => $rol,
                ":activo" => $activo,
                ":id" => $id
            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Si se editó la sesión actual, actualizar datos de sesión
        |--------------------------------------------------------------------------
        */

        if ((int) $id === (int) $_SESSION["usuario_id"]) {

            $_SESSION["usuario_nombre"] = $nombre;
            $_SESSION["usuario_rol"] = $rol;

        }


        /*
        |--------------------------------------------------------------------------
        | Regresar
        |--------------------------------------------------------------------------
        */

        header(
            "Location: usuarios.php?mensaje=actualizado"
        );

        exit;

    }

}


$tituloPagina = "Editar usuario";

?>


<?php require_once "../includes/admin_header.php"; ?>

<?php require_once "../includes/admin_sidebar.php"; ?>


<main class="admin-main">


    <!-- =========================================================
         TOPBAR
    ========================================================== -->

    <div class="admin-topbar">

        <div>

            <h1>Editar usuario</h1>

            <p>
                Modifica los datos y permisos de esta cuenta.
            </p>

        </div>


        <div class="topbar-user">

            <div class="topbar-user-info">

                <strong>
                    <?= htmlspecialchars($nombreUsuario) ?>
                </strong>

                <span>
                    <?= htmlspecialchars($rolUsuario) ?>
                </span>

            </div>

            <div class="topbar-avatar">

                <?= strtoupper(
                    substr($nombreUsuario, 0, 1)
                ) ?>

            </div>

        </div>

    </div>


    <!-- =========================================================
         ERRORES
    ========================================================== -->

    <?php if (!empty($errores)): ?>

        <div class="form-errors">

            <strong>
                No se pudo actualizar el usuario:
            </strong>

            <ul>

                <?php foreach ($errores as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <!-- =========================================================
         FORMULARIO
    ========================================================== -->

    <div class="form-card">


        <div class="form-card-header">

            <div>

                <h2>
                    Información del usuario
                </h2>

                <p>
                    Actualiza los datos de la cuenta.
                </p>

            </div>

        </div>


        <form
            method="POST"
            action="usuario_editar.php?id=<?= (int) $id ?>"
            class="admin-form"
        >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(obtenerTokenCSRF(), ENT_QUOTES, "UTF-8") ?>"
                >


            <div class="form-grid">


                <!-- =================================================
                     NOMBRE
                ================================================== -->

                <div class="form-field">

                    <label for="nombre">
                        Nombre completo
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        maxlength="100"
                        value="<?= htmlspecialchars($nombre) ?>"
                        placeholder="Ej. Juan Pérez"
                        required
                    >

                </div>


                <!-- =================================================
                     USUARIO
                ================================================== -->

                <div class="form-field">

                    <label for="usuario">
                        Nombre de usuario
                    </label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        maxlength="50"
                        value="<?= htmlspecialchars($usuario) ?>"
                        placeholder="Ej. jperez"
                        required
                    >

                    <small>
                        Se utiliza para iniciar sesión.
                    </small>

                </div>


                <!-- =================================================
                     NUEVA CONTRASEÑA
                ================================================== -->

                <div class="form-field">

                    <label for="password">
                        Nueva contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="6"
                        placeholder="Déjalo vacío para conservarla"
                    >

                    <small>
                        Si no deseas cambiarla, deja este campo vacío.
                    </small>

                </div>


                <!-- =================================================
                     CONFIRMAR CONTRASEÑA
                ================================================== -->

                <div class="form-field">

                    <label for="confirmar_password">
                        Confirmar nueva contraseña
                    </label>

                    <input
                        type="password"
                        id="confirmar_password"
                        name="confirmar_password"
                        minlength="6"
                        placeholder="Repite la nueva contraseña"
                    >

                </div>


                <!-- =================================================
                     ROL
                ================================================== -->

                <div class="form-field">

                    <label for="rol">
                        Rol
                    </label>

                    <select
                        id="rol"
                        name="rol"
                        required
                    >

                        <option
                            value="Tecnico"
                            <?= $rol === "Tecnico" ? "selected" : "" ?>
                        >
                            Técnico
                        </option>

                        <option
                            value="Administrador"
                            <?= $rol === "Administrador" ? "selected" : "" ?>
                        >
                            Administrador
                        </option>

                    </select>

                    <small>
                        Los administradores pueden gestionar usuarios y configuración.
                    </small>

                </div>


                <!-- =================================================
                     ESTADO
                ================================================== -->

                <div class="form-field">

                    <label>
                        Estado
                    </label>

                    <label class="checkbox-field">

                        <input
                            type="checkbox"
                            name="activo"
                            value="1"
                            <?= $activo === 1 ? "checked" : "" ?>
                        >

                        <span>
                            Usuario activo
                        </span>

                    </label>

                    <small>
                        Un usuario inactivo no puede iniciar sesión.
                    </small>

                </div>


            </div>


            <!-- =================================================
                 INFORMACIÓN
            ================================================== -->

            <div class="form-info-box">

                <strong>
                    🔐 Contraseña
                </strong>

                <p>
                    La contraseña actual nunca se muestra.
                    Si dejas los campos de contraseña vacíos,
                    se conservará la contraseña existente.
                </p>

            </div>


            <!-- =================================================
                 BOTONES
            ================================================== -->

            <div class="form-actions">

                <a
                    href="usuarios.php"
                    class="secondary-button"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    Guardar cambios
                </button>

            </div>


        </form>

    </div>


</main>


</div>

</body>

</html>