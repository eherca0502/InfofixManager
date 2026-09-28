<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Editar equipo";

$error = "";


/*
|--------------------------------------------------------------------------
| OBTENER ID DEL EQUIPO
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {

    header("Location: equipos.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| OBTENER EQUIPO
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM equipos
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$equipo = $stmt->fetch();


if (!$equipo) {

    header("Location: equipos.php");
    exit;
}


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
| ACTUALIZAR EQUIPO
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
        | VERIFICAR CLIENTE
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
            | ACTUALIZAR
            |--------------------------------------------------------------------------
            */

            $sqlUpdate = "
                UPDATE equipos
                SET
                    cliente_id = :cliente_id,
                    tipo = :tipo,
                    marca = :marca,
                    modelo = :modelo,
                    numero_serie = :numero_serie,
                    sistema_operativo = :sistema_operativo,
                    observaciones = :observaciones
                WHERE id = :id
            ";

            $stmtUpdate = $pdo->prepare($sqlUpdate);

            $stmtUpdate->execute([

                ":cliente_id" => $clienteId,

                ":tipo" => $tipo,

                ":marca" => $marca,

                ":modelo" => $modelo,

                ":numero_serie" => $numeroSerie,

                ":sistema_operativo" => $sistemaOperativo,

                ":observaciones" => $observaciones,

                ":id" => $id

            ]);


            /*
            |--------------------------------------------------------------------------
            | REDIRECCIÓN
            |--------------------------------------------------------------------------
            */

            header(
                "Location: equipos.php?mensaje=actualizado"
            );

            exit;
        }
    }
}


/*
|--------------------------------------------------------------------------
| VALORES DEL FORMULARIO
|--------------------------------------------------------------------------
|
| Si hubo un error usamos los datos enviados.
| Si no, usamos los datos actuales de la base de datos.
|
*/

$valorCliente = $_POST["cliente_id"]
    ?? $equipo["cliente_id"];

$valorTipo = $_POST["tipo"]
    ?? $equipo["tipo"];

$valorMarca = $_POST["marca"]
    ?? $equipo["marca"];

$valorModelo = $_POST["modelo"]
    ?? $equipo["modelo"];

$valorNumeroSerie = $_POST["numero_serie"]
    ?? $equipo["numero_serie"];

$valorSistemaOperativo = $_POST["sistema_operativo"]
    ?? $equipo["sistema_operativo"];

$valorObservaciones = $_POST["observaciones"]
    ?? $equipo["observaciones"];


require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="main-content">

    <header class="topbar">

        <div>

            <h1>
                Editar equipo
            </h1>

            <p>
                Modifica la información del equipo
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


    <section class="form-card">

        <div class="form-card-header">

            <h2>
                Información del equipo
            </h2>

            <p>
                Equipo #<?= $equipo["id"] ?>
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
                                    $valorCliente
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
                            <?= $valorTipo === "Laptop"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Laptop
                        </option>

                        <option
                            value="PC de escritorio"
                            <?= $valorTipo === "PC de escritorio"
                                ? "selected"
                                : ""
                            ?>
                        >
                            PC de escritorio
                        </option>

                        <option
                            value="MacBook"
                            <?= $valorTipo === "MacBook"
                                ? "selected"
                                : ""
                            ?>
                        >
                            MacBook
                        </option>

                        <option
                            value="iMac"
                            <?= $valorTipo === "iMac"
                                ? "selected"
                                : ""
                            ?>
                        >
                            iMac
                        </option>

                        <option
                            value="Servidor"
                            <?= $valorTipo === "Servidor"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Servidor
                        </option>

                        <option
                            value="Impresora"
                            <?= $valorTipo === "Impresora"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Impresora
                        </option>

                        <option
                            value="Otro"
                            <?= $valorTipo === "Otro"
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
                            $valorMarca ?? ""
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
                            $valorModelo ?? ""
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
                            $valorNumeroSerie ?? ""
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
                            <?= $valorSistemaOperativo === "Windows 11"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Windows 11
                        </option>

                        <option
                            value="Windows 10"
                            <?= $valorSistemaOperativo === "Windows 10"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Windows 10
                        </option>

                        <option
                            value="Windows 7"
                            <?= $valorSistemaOperativo === "Windows 7"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Windows 7
                        </option>

                        <option
                            value="macOS"
                            <?= $valorSistemaOperativo === "macOS"
                                ? "selected"
                                : ""
                            ?>
                        >
                            macOS
                        </option>

                        <option
                            value="Linux"
                            <?= $valorSistemaOperativo === "Linux"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Linux
                        </option>

                        <option
                            value="ChromeOS"
                            <?= $valorSistemaOperativo === "ChromeOS"
                                ? "selected"
                                : ""
                            ?>
                        >
                            ChromeOS
                        </option>

                        <option
                            value="Otro"
                            <?= $valorSistemaOperativo === "Otro"
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
                        $valorObservaciones ?? ""
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
                    Guardar cambios
                </button>

            </div>

        </form>

    </section>

</main>

</div>

</body>

</html>