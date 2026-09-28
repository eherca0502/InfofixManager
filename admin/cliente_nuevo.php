

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Nuevo cliente";

$error = "";


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
            INSERT INTO clientes
            (
                nombre,
                telefono,
                email,
                direccion,
                notas
            )
            VALUES
            (
                :nombre,
                :telefono,
                :email,
                :direccion,
                :notas
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":nombre" => $nombre,
            ":telefono" => $telefono,
            ":email" => $email,
            ":direccion" => $direccion,
            ":notas" => $notas
        ]);


        header(
            "Location: clientes.php?mensaje=creado"
        );

        exit;
    }
}


require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="main-content">


    

    <header class="topbar">

        <div>

            <h1>
                Nuevo cliente
            </h1>

            <p>
                Registra un nuevo cliente en el sistema
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
                    Completa los datos principales del cliente.
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
                        placeholder="Ej. Juan Pérez"
                        value="<?= htmlspecialchars(
                            $_POST["nombre"] ?? ""
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
                        placeholder="Ej. 844 123 4567"
                        value="<?= htmlspecialchars(
                            $_POST["telefono"] ?? ""
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
                        placeholder="cliente@correo.com"
                        value="<?= htmlspecialchars(
                            $_POST["email"] ?? ""
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
                        placeholder="Calle, número, colonia..."
                        value="<?= htmlspecialchars(
                            $_POST["direccion"] ?? ""
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
                        placeholder="Información adicional del cliente..."
                    ><?= htmlspecialchars(
                        $_POST["notas"] ?? ""
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
                    Guardar cliente
                </button>

            </div>


        </form>


    </section>


</main>


</div>

</body>

</html>