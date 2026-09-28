<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Nuevo equipo";

$error = "";


/*
|--------------------------------------------------------------------------
| OBTENER CLIENTES
|--------------------------------------------------------------------------
*/

$sqlClientes = "
    SELECT
        id,
        nombre
    FROM clientes
    ORDER BY nombre ASC
";

$stmtClientes = $pdo->query($sqlClientes);

$clientes = $stmtClientes->fetchAll();


/*
|--------------------------------------------------------------------------
| GUARDAR EQUIPO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verificarTokenCSRF();

    $clienteId = filter_input(
        INPUT_POST,
        "cliente_id",
        FILTER_VALIDATE_INT
    );

    $tipo = trim($_POST["tipo"] ?? "");
    $marca = trim($_POST["marca"] ?? "");
    $modelo = trim($_POST["modelo"] ?? "");
    $numeroSerie = trim($_POST["numero_serie"] ?? "");
    $sistemaOperativo = trim(
        $_POST["sistema_operativo"] ?? ""
    );
    $observaciones = trim(
        $_POST["observaciones"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES
    |--------------------------------------------------------------------------
    */

    if (!$clienteId) {

        $error = "Selecciona un cliente.";

    } elseif ($tipo === "") {

        $error = "Ingresa el tipo de equipo.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | VERIFICAR QUE EL CLIENTE EXISTA
        |--------------------------------------------------------------------------
        */

        $sqlCliente = "
            SELECT id
            FROM clientes
            WHERE id = :id
            LIMIT 1
        ";

        $stmtCliente = $pdo->prepare($sqlCliente);

        $stmtCliente->execute([
            ":id" => $clienteId
        ]);

        $clienteExiste = $stmtCliente->fetch();


        if (!$clienteExiste) {

            $error = "El cliente seleccionado no existe.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | INSERTAR EQUIPO
            |--------------------------------------------------------------------------
            */

            $sql = "
                INSERT INTO equipos
                (
                    cliente_id,
                    tipo,
                    marca,
                    modelo,
                    numero_serie,
                    sistema_operativo,
                    observaciones
                )
                VALUES
                (
                    :cliente_id,
                    :tipo,
                    :marca,
                    :modelo,
                    :numero_serie,
                    :sistema_operativo,
                    :observaciones
                )
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([

                ":cliente_id" => $clienteId,

                ":tipo" => $tipo,

                ":marca" => $marca,

                ":modelo" => $modelo,

                ":numero_serie" => $numeroSerie,

                ":sistema_operativo" => $sistemaOperativo,

                ":observaciones" => $observaciones

            ]);


            /*
            |--------------------------------------------------------------------------
            | REDIRIGIR
            |--------------------------------------------------------------------------
            */

            header(
                "Location: equipos.php?mensaje=creado"
            );

            exit;
        }
    }
}


require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="main-content">

    <header class="topbar">

        <div>

            <h1>
                Nuevo equipo
            </h1>

            <p>
                Registra un nuevo equipo para un cliente
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


    <?php if ($error !== ""): ?>

        <div class="alert-error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <?php if (count($clientes) === 0): ?>

        <section class="form-card">

            <div class="form-card-header">

                <h2>
                    No hay clientes registrados
                </h2>

                <p>
                    Antes de registrar un equipo necesitas
                    crear al menos un cliente.
                </p>

            </div>


            <div class="admin-form">

                <a
                    href="cliente_nuevo.php"
                    class="primary-button"
                >
                    + Registrar cliente
                </a>

                <a
                    href="equipos.php"
                    class="secondary-button"
                    style="margin-left: 8px;"
                >
                    Cancelar
                </a>

            </div>

        </section>

    <?php else: ?>


        <section class="form-card">

            <div class="form-card-header">

                <h2>
                    Información del equipo
                </h2>

                <p>
                    Completa los datos del equipo que deseas
                    registrar.
                </p>

            </div>


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

                        <label for="cliente_id">
                            Cliente *
                        </label>

                        <select
                            id="cliente_id"
                            name="cliente_id"
                            required
                        >

                            <option value="">
                                Selecciona un cliente
                            </option>

                            <?php foreach ($clientes as $cliente): ?>

                                <option
                                    value="<?= $cliente["id"] ?>"
                                    <?= (
                                        isset(
                                            $_POST["cliente_id"]
                                        )
                                        &&
                                        $_POST["cliente_id"]
                                        == $cliente["id"]
                                    )
                                        ? "selected"
                                        : ""
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $cliente["nombre"]
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    

                    <div class="form-field">

                        <label for="tipo">
                            Tipo de equipo *
                        </label>

                        <select
                            id="tipo"
                            name="tipo"
                            required
                        >

                            <option value="">
                                Selecciona un tipo
                            </option>

                            <option
                                value="Laptop"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Laptop"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Laptop
                            </option>

                            <option
                                value="PC de escritorio"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "PC de escritorio"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                PC de escritorio
                            </option>

                            <option
                                value="MacBook"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "MacBook"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                MacBook
                            </option>

                            <option
                                value="iMac"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "iMac"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                iMac
                            </option>

                            <option
                                value="Servidor"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Servidor"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Servidor
                            </option>

                            <option
                                value="Impresora"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Impresora"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Impresora
                            </option>

                            <option
                                value="Otro"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Otro"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Otro
                            </option>

                        </select>

                    </div>


                    

                    <div class="form-field">

                        <label for="marca">
                            Marca
                        </label>

                        <input
                            type="text"
                            id="marca"
                            name="marca"
                            placeholder="Ej. HP, Lenovo, Dell..."
                            value="<?= htmlspecialchars(
                                $_POST["marca"] ?? ""
                            ) ?>"
                        >

                    </div>


                    

                    <div class="form-field">

                        <label for="modelo">
                            Modelo
                        </label>

                        <input
                            type="text"
                            id="modelo"
                            name="modelo"
                            placeholder="Ej. Pavilion 15..."
                            value="<?= htmlspecialchars(
                                $_POST["modelo"] ?? ""
                            ) ?>"
                        >

                    </div>


                    

                    <div class="form-field">

                        <label for="numero_serie">
                            Número de serie
                        </label>

                        <input
                            type="text"
                            id="numero_serie"
                            name="numero_serie"
                            placeholder="Número de serie del equipo"
                            value="<?= htmlspecialchars(
                                $_POST["numero_serie"] ?? ""
                            ) ?>"
                        >

                    </div>


                    

                    <div class="form-field">

                        <label for="sistema_operativo">
                            Sistema operativo
                        </label>

                        <select
                            id="sistema_operativo"
                            name="sistema_operativo"
                        >

                            <option value="">
                                Selecciona un sistema
                            </option>

                            <option
                                value="Windows 11"
                                <?= (
                                    ($_POST["sistema_operativo"] ?? "")
                                    === "Windows 11"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Windows 11
                            </option>

                            <option
                                value="Windows 10"
                                <?= (
                                    ($_POST["sistema_operativo"] ?? "")
                                    === "Windows 10"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Windows 10
                            </option>

                            <option
                                value="Windows 7"
                                <?= (
                                    ($_POST["sistema_operativo"] ?? "")
                                    === "Windows 7"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Windows 7
                            </option>

                            <option
                                value="macOS"
                                <?= (
                                    ($_POST["sistema_operativo"] ?? "")
                                    === "macOS"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                macOS
                            </option>

                            <option
                                value="Linux"
                                <?= (
                                    ($_POST["sistema_operativo"] ?? "")
                                    === "Linux"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Linux
                            </option>

                            <option
                                value="ChromeOS"
                                <?= (
                                    ($_POST["sistema_operativo"] ?? "")
                                    === "ChromeOS"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                ChromeOS
                            </option>

                            <option
                                value="Otro"
                                <?= (
                                    ($_POST["sistema_operativo"] ?? "")
                                    === "Otro"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Otro
                            </option>

                        </select>

                    </div>


                    

                    <div class="form-field full">

                        <label for="observaciones">
                            Observaciones
                        </label>

                        <textarea
                            id="observaciones"
                            name="observaciones"
                            placeholder="Describe cualquier información adicional del equipo..."
                        ><?= htmlspecialchars(
                            $_POST["observaciones"] ?? ""
                        ) ?></textarea>

                    </div>


                </div>


                <div class="form-actions">

                    <a
                        href="equipos.php"
                        class="secondary-button"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Guardar equipo
                    </button>

                </div>

            </form>

        </section>

    <?php endif; ?>

</main>

</div>

</body>

</html>