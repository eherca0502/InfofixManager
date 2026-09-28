<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

requiereAdministrador();




$nombre = "";
$usuario = "";
$rol = "Tecnico";
$activo = 1;

$errores = [];




if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verificarTokenCSRF();

    $nombre = trim($_POST["nombre"] ?? "");
    $usuario = trim($_POST["usuario"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmarPassword = $_POST["confirmar_password"] ?? "";
    $rol = $_POST["rol"] ?? "Tecnico";
    $activo = isset($_POST["activo"]) ? 1 : 0;


   

    if ($nombre === "") {

        $errores[] = "El nombre es obligatorio.";

    } elseif (strlen($nombre) < 3) {

        $errores[] = "El nombre debe tener al menos 3 caracteres.";

    }


  

    if ($usuario === "") {

        $errores[] = "El nombre de usuario es obligatorio.";

    } elseif (strlen($usuario) < 3) {

        $errores[] = "El usuario debe tener al menos 3 caracteres.";

    } elseif (strlen($usuario) > 50) {

        $errores[] = "El usuario no puede tener más de 50 caracteres.";

    }


    
    if ($usuario !== "") {

        $stmt = $pdo->prepare("
            SELECT id
            FROM usuarios
            WHERE usuario = :usuario
            LIMIT 1
        ");

        $stmt->execute([
            ":usuario" => $usuario
        ]);

        if ($stmt->fetch()) {

            $errores[] = "Ese nombre de usuario ya está registrado.";

        }

    }


  

    if ($password === "") {

        $errores[] = "La contraseña es obligatoria.";

    } elseif (strlen($password) < 6) {

        $errores[] = "La contraseña debe tener al menos 6 caracteres.";

    }



    if ($password !== $confirmarPassword) {

        $errores[] = "Las contraseñas no coinciden.";

    }


  

    $rolesPermitidos = [
        "Administrador",
        "Tecnico"
    ];

    if (!in_array($rol, $rolesPermitidos, true)) {

        $errores[] = "El rol seleccionado no es válido.";

    }


  

    if (empty($errores)) {

   

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


       

        $stmt = $pdo->prepare("
            INSERT INTO usuarios
            (
                nombre,
                usuario,
                password,
                rol,
                activo
            )
            VALUES
            (
                :nombre,
                :usuario,
                :password,
                :rol,
                :activo
            )
        ");

        $stmt->execute([
            ":nombre" => $nombre,
            ":usuario" => $usuario,
            ":password" => $passwordHash,
            ":rol" => $rol,
            ":activo" => $activo
        ]);


      
        header(
            "Location: usuarios.php?mensaje=creado"
        );

        exit;

    }

}


$tituloPagina = "Nuevo usuario";

?>


<?php require_once "../includes/admin_header.php"; ?>

<?php require_once "../includes/admin_sidebar.php"; ?>


<main class="admin-main">



    <div class="admin-topbar">

        <div>

            <h1>Nuevo usuario</h1>

            <p>
                Crea una nueva cuenta para acceder a Infofix Manager.
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



    <?php if (!empty($errores)): ?>

        <div class="form-errors">

            <strong>
                No se pudo crear el usuario:
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



    <div class="form-card">


        <div class="form-card-header">

            <div>

                <h2>
                    Información del usuario
                </h2>

                <p>
                    Completa los datos de la nueva cuenta.
                </p>

            </div>

        </div>


        <form
            method="POST"
            action="usuario_nuevo.php"
            class="admin-form"
        >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(obtenerTokenCSRF(), ENT_QUOTES, "UTF-8") ?>"
                >



            <div class="form-grid">


       

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
                        Este nombre se utilizará para iniciar sesión.
                    </small>

                </div>


                <div class="form-field">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="6"
                        placeholder="Mínimo 6 caracteres"
                        required
                    >

                    <small>
                        La contraseña se almacenará de forma segura mediante hash.
                    </small>

                </div>


             

                <div class="form-field">

                    <label for="confirmar_password">
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="confirmar_password"
                        name="confirmar_password"
                        minlength="6"
                        placeholder="Repite la contraseña"
                        required
                    >

                </div>


               

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
                        Si está desactivado, no podrá iniciar sesión.
                    </small>

                </div>


            </div>



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
                    Crear usuario
                </button>

            </div>


        </form>

    </div>


</main>


</div>

</body>

</html>