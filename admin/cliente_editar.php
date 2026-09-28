<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Editar cliente";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: clientes.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| OBTENER CLIENTE
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM clientes
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$cliente = $stmt->fetch();


if (!$cliente) {

    header("Location: clientes.php");

    exit;
}


$error = "";


/*
|--------------------------------------------------------------------------
| ACTUALIZAR CLIENTE
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verificarTokenCSRF();

    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $direccion = trim($_POST["direccion"] ?? "");
    $notas = trim($_POST["notas"] ?? "");


    if ($nombre === "") {

        $error = "El nombre del cliente es obligatorio.";

    } elseif (
        $email !== "" &&
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $error = "Ingresa un correo electrónico válido.";

    } else {

        $sql = "
            UPDATE clientes
            SET
                nombre = :nombre,
                telefono = :telefono,
                email = :email,
                direccion = :direccion,
                notas = :notas
            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":nombre" => $nombre,
            ":telefono" => $telefono,
            ":email" => $email,
            ":direccion" => $direccion,
            ":notas" => $notas,
            ":id" => $id
        ]);


        header(
            "Location: clientes.php?mensaje=actualizado"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | MANTENER DATOS ESCRITOS SI HAY ERROR
    |--------------------------------------------------------------------------
    */

    $cliente["nombre"] = $nombre;
    $cliente["telefono"] = $telefono;
    $cliente["email"] = $email;
    $cliente["direccion"] = $direccion;
    $cliente["notas"] = $notas;
}


require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="main-content">


    

    <header class="topbar">

        <div>

            <h1>
                Editar cliente
            </h1>

            <p>
                Modifica la información del cliente
            </p>

        </div>


        <div class="topbar-user">

            <div class="user-avatar">

                <?= strtoupper(
                    substr(
                        $_SESSION["usuario_nombre"],
                        0,
                        1
                    )
                ) ?>

            </div>

            <div class="user-info">

                <strong>

                    <?= htmlspecialchars(
                        $_SESSION["usuario_nombre"]
                    ) ?>

                </strong>

                <span>

                    <?= htmlspecialchars(
                        $_SESSION["usuario_rol"]
                    ) ?>

                </span>

            </div>

        </div>

    </header>


    

    <section class="form-card">


        <div class="form-card-header">

            <div>

                <h2>
                    Información del cliente
                </h2>

                <p>
                    Cliente #<?= $cliente["id"] ?>
                </p>

            </div>

        </div>


        <?php if ($error !== ""): ?>

            <div class="alert-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            class="admin-form"
        >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(obtenerTokenCSRF(), ENT_QUOTES, "UTF-8") ?>"
                >


            <div class="form-grid">


                

                <div class="form-field full">

                    <label for="nombre">
                        Nombre completo *
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="<?= htmlspecialchars(
                            $cliente["nombre"]
                        ) ?>"
                        required
                    >

                </div>


                

                <div class="form-field">

                    <label for="telefono">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        value="<?= htmlspecialchars(
                            $cliente["telefono"] ?? ""
                        ) ?>"
                    >

                </div>


                

                <div class="form-field">

                    <label for="email">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars(
                            $cliente["email"] ?? ""
                        ) ?>"
                    >

                </div>


                

                <div class="form-field full">

                    <label for="direccion">
                        Dirección
                    </label>

                    <input
                        type="text"
                        id="direccion"
                        name="direccion"
                        value="<?= htmlspecialchars(
                            $cliente["direccion"] ?? ""
                        ) ?>"
                    >

                </div>


                

                <div class="form-field full">

                    <label for="notas">
                        Notas
                    </label>

                    <textarea
                        id="notas"
                        name="notas"
                        rows="5"
                    ><?= htmlspecialchars(
                        $cliente["notas"] ?? ""
                    ) ?></textarea>

                </div>


            </div>


            

            <div class="form-actions">

                <a
                    href="clientes.php"
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


    </section>


</main>


</div>

</body>

</html>