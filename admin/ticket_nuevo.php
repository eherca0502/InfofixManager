<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$error = "";



$sql = "SELECT id, nombre
        FROM clientes
        ORDER BY nombre ASC";

$stmt = $pdo->query($sql);

$clientes = $stmt->fetchAll();




$sql = "SELECT
            equipos.id,
            equipos.cliente_id,
            equipos.tipo,
            equipos.marca,
            equipos.modelo
        FROM equipos
        ORDER BY equipos.id DESC";

$stmt = $pdo->query($sql);

$equipos = $stmt->fetchAll();




$sql = "SELECT id, nombre, rol
        FROM usuarios
        WHERE activo = 1
        ORDER BY nombre ASC";

$stmt = $pdo->query($sql);

$usuarios = $stmt->fetchAll();




if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verificarTokenCSRF();

    $cliente_id = filter_input(
        INPUT_POST,
        "cliente_id",
        FILTER_VALIDATE_INT
    );

    $equipo_id = filter_input(
        INPUT_POST,
        "equipo_id",
        FILTER_VALIDATE_INT
    );

    $usuario_id = filter_input(
        INPUT_POST,
        "usuario_id",
        FILTER_VALIDATE_INT
    );

    $asunto = trim($_POST["asunto"] ?? "");

    $descripcion = trim(
        $_POST["descripcion"] ?? ""
    );

    $prioridad = $_POST["prioridad"] ?? "Media";

    $estado = $_POST["estado"] ?? "Abierto";


   

    if ($asunto === "") {

        $error = "El asunto es obligatorio.";

    } elseif ($descripcion === "") {

        $error = "La descripción es obligatoria.";

    } elseif (
        !in_array(
            $prioridad,
            ["Baja", "Media", "Alta", "Critica"],
            true
        )
    ) {

        $error = "La prioridad seleccionada no es válida.";

    } elseif (
        !in_array(
            $estado,
            ["Abierto", "En proceso", "Resuelto", "Cerrado"],
            true
        )
    ) {

        $error = "El estado seleccionado no es válido.";

    }


  

    if ($error === "" && $cliente_id) {

        $sql = "SELECT id
                FROM clientes
                WHERE id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $cliente_id
        ]);

        if (!$stmt->fetch()) {

            $error = "El cliente seleccionado no existe.";

        }

    }


  

    if ($error === "" && $equipo_id) {

        $sql = "SELECT id, cliente_id
                FROM equipos
                WHERE id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $equipo_id
        ]);

        $equipo = $stmt->fetch();

        if (!$equipo) {

            $error = "El equipo seleccionado no existe.";

        } elseif (
            $cliente_id &&
            (int)$equipo["cliente_id"] !== (int)$cliente_id
        ) {

            $error = "El equipo no pertenece al cliente seleccionado.";

        }

    }


    

    if ($error === "" && $usuario_id) {

        $sql = "SELECT id
                FROM usuarios
                WHERE id = :id
                AND activo = 1
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $usuario_id
        ]);

        if (!$stmt->fetch()) {

            $error = "El usuario seleccionado no existe o está inactivo.";

        }

    }


    

    if ($error === "") {

        $sql = "INSERT INTO tickets
                (
                    cliente_id,
                    equipo_id,
                    usuario_id,
                    asunto,
                    descripcion,
                    prioridad,
                    estado
                )
                VALUES
                (
                    :cliente_id,
                    :equipo_id,
                    :usuario_id,
                    :asunto,
                    :descripcion,
                    :prioridad,
                    :estado
                )";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ":cliente_id" =>
                $cliente_id ?: null,

            ":equipo_id" =>
                $equipo_id ?: null,

            ":usuario_id" =>
                $usuario_id ?: null,

            ":asunto" =>
                $asunto,

            ":descripcion" =>
                $descripcion,

            ":prioridad" =>
                $prioridad,

            ":estado" =>
                $estado

        ]);

        header(
            "Location: tickets.php?mensaje=creado"
        );

        exit;
    }

}

?>

<?php

$tituloPagina = "Nuevo ticket";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="admin-main">


    

    <div class="admin-topbar">

        <div>

            <h1>Nuevo ticket</h1>

            <p>
                Registra una nueva solicitud de soporte.
            </p>

        </div>


        <div class="topbar-user">

            <div class="topbar-avatar">

                <?= strtoupper(
                    substr($nombreUsuario, 0, 1)
                ) ?>

            </div>


            <div>

                <strong>
                    <?= htmlspecialchars($nombreUsuario) ?>
                </strong>

                <span>
                    <?= htmlspecialchars($rolUsuario) ?>
                </span>

            </div>

        </div>

    </div>


    <?php if ($error): ?>

        <div class="alert-error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    

    <div class="form-card">

        <div class="form-card-header">

            <h2>Información del ticket</h2>

            <p>
                Completa los datos de la solicitud.
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


                

                <div class="form-field">

                    <label for="cliente_id">
                        Cliente
                    </label>

                    <select
                        name="cliente_id"
                        id="cliente_id"
                    >

                        <option value="">
                            Sin cliente
                        </option>

                        <?php foreach ($clientes as $cliente): ?>

                            <option
                                value="<?= (int)$cliente["id"] ?>"
                                <?= (
                                    ($_POST["cliente_id"] ?? "") ==
                                    $cliente["id"]
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

                    <label for="equipo_id">
                        Equipo
                    </label>

                    <select
                        name="equipo_id"
                        id="equipo_id"
                    >

                        <option value="">
                            Sin equipo
                        </option>

                        <?php foreach ($equipos as $equipo): ?>

                            <option
                                value="<?= (int)$equipo["id"] ?>"
                                data-cliente="<?= (int)$equipo["cliente_id"] ?>"
                                <?= (
                                    ($_POST["equipo_id"] ?? "") ==
                                    $equipo["id"]
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $equipo["tipo"]
                                ) ?>

                                <?php if ($equipo["marca"]): ?>

                                    -
                                    <?= htmlspecialchars(
                                        $equipo["marca"]
                                    ) ?>

                                <?php endif; ?>

                                <?php if ($equipo["modelo"]): ?>

                                    <?= htmlspecialchars(
                                        $equipo["modelo"]
                                    ) ?>

                                <?php endif; ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                

                <div class="form-field full">

                    <label for="asunto">
                        Asunto *
                    </label>

                    <input
                        type="text"
                        name="asunto"
                        id="asunto"
                        value="<?= htmlspecialchars(
                            $_POST["asunto"] ?? ""
                        ) ?>"
                        placeholder="Ej. La computadora no enciende"
                        required
                    >

                </div>


                

                <div class="form-field">

                    <label for="prioridad">
                        Prioridad
                    </label>

                    <select
                        name="prioridad"
                        id="prioridad"
                    >

                        <?php

                        $prioridades = [
                            "Baja",
                            "Media",
                            "Alta",
                            "Critica"
                        ];

                        foreach ($prioridades as $opcion):

                        ?>

                            <option
                                value="<?= $opcion ?>"
                                <?= (
                                    ($_POST["prioridad"] ?? "Media")
                                    === $opcion
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= $opcion ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                

                <div class="form-field">

                    <label for="estado">
                        Estado
                    </label>

                    <select
                        name="estado"
                        id="estado"
                    >

                        <?php

                        $estados = [
                            "Abierto",
                            "En proceso",
                            "Resuelto",
                            "Cerrado"
                        ];

                        foreach ($estados as $opcion):

                        ?>

                            <option
                                value="<?= $opcion ?>"
                                <?= (
                                    ($_POST["estado"] ?? "Abierto")
                                    === $opcion
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= $opcion ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                

                <div class="form-field">

                    <label for="usuario_id">
                        Usuario asignado
                    </label>

                    <select
                        name="usuario_id"
                        id="usuario_id"
                    >

                        <option value="">
                            Sin asignar
                        </option>

                        <?php foreach ($usuarios as $usuario): ?>

                            <option
                                value="<?= (int)$usuario["id"] ?>"
                                <?= (
                                    ($_POST["usuario_id"] ?? "") ==
                                    $usuario["id"]
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $usuario["nombre"]
                                ) ?>

                                -
                                <?= htmlspecialchars(
                                    $usuario["rol"]
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                

                <div class="form-field full">

                    <label for="descripcion">
                        Descripción *
                    </label>

                    <textarea
                        name="descripcion"
                        id="descripcion"
                        placeholder="Describe detalladamente el problema o solicitud..."
                        required
                    ><?= htmlspecialchars(
                        $_POST["descripcion"] ?? ""
                    ) ?></textarea>

                </div>


            </div>


            

            <div class="form-actions">

                <a
                    href="tickets.php"
                    class="secondary-button"
                >
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    Crear ticket
                </button>

            </div>


        </form>

    </div>


</main>

</div>

</body>

</html>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const clienteSelect =
            document.getElementById("cliente_id");

        const equipoSelect =
            document.getElementById("equipo_id");


        function filtrarEquipos() {

            const clienteId =
                clienteSelect.value;

            const opciones =
                equipoSelect.querySelectorAll(
                    "option[data-cliente]"
                );


            opciones.forEach(
                function (opcion) {

                    const pertenece =
                        opcion.dataset.cliente === clienteId;

                    opcion.hidden =
                        clienteId !== "" &&
                        !pertenece;

                }
            );


            if (
                equipoSelect.value !== ""
            ) {

                const seleccionada =
                    equipoSelect.options[
                        equipoSelect.selectedIndex
                    ];

                if (
                    seleccionada.dataset.cliente &&
                    seleccionada.dataset.cliente !== clienteId
                ) {

                    equipoSelect.value = "";

                }

            }

        }


        clienteSelect.addEventListener(
            "change",
            filtrarEquipos
        );


        filtrarEquipos();

    }
);

</script>