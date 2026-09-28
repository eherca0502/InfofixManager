<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Nuevo servicio";

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
| OBTENER TÉCNICOS
|--------------------------------------------------------------------------
*/

$sqlTecnicos = "
    SELECT
        id,
        nombre
    FROM usuarios
    WHERE activo = 1
    ORDER BY nombre ASC
";

$stmtTecnicos = $pdo->query($sqlTecnicos);

$tecnicos = $stmtTecnicos->fetchAll();


/*
|--------------------------------------------------------------------------
| OBTENER EQUIPOS
|--------------------------------------------------------------------------
|
| Los cargamos todos inicialmente.
| JavaScript filtrará los equipos según el cliente seleccionado.
|
*/

$sqlEquipos = "
    SELECT
        equipos.id,
        equipos.cliente_id,
        equipos.tipo,
        equipos.marca,
        equipos.modelo,
        equipos.numero_serie
    FROM equipos
    ORDER BY equipos.id DESC
";

$stmtEquipos = $pdo->query($sqlEquipos);

$equipos = $stmtEquipos->fetchAll();


/*
|--------------------------------------------------------------------------
| GUARDAR SERVICIO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verificarTokenCSRF();

    $clienteId = filter_input(
        INPUT_POST,
        "cliente_id",
        FILTER_VALIDATE_INT
    );

    $equipoId = filter_input(
        INPUT_POST,
        "equipo_id",
        FILTER_VALIDATE_INT
    );

    $tecnicoId = filter_input(
        INPUT_POST,
        "tecnico_id",
        FILTER_VALIDATE_INT
    );

    $tipo = trim(
        $_POST["tipo"] ?? ""
    );

    $descripcion = trim(
        $_POST["descripcion"] ?? ""
    );

    $diagnostico = trim(
        $_POST["diagnostico"] ?? ""
    );

    $solucion = trim(
        $_POST["solucion"] ?? ""
    );

    $costo = trim(
        $_POST["costo"] ?? ""
    );

    $estado = $_POST["estado"] ?? "Recibido";


    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES
    |--------------------------------------------------------------------------
    */

    if (!$clienteId) {

        $error = "Selecciona un cliente.";

    } elseif (!$equipoId) {

        $error = "Selecciona un equipo.";

    } elseif ($tipo === "") {

        $error = "Ingresa el tipo de servicio.";

    } elseif (!is_numeric($costo) || $costo < 0) {

        $error = "Ingresa un costo válido.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | VERIFICAR EQUIPO
        |--------------------------------------------------------------------------
        */

        $sqlEquipo = "
            SELECT
                id,
                cliente_id
            FROM equipos
            WHERE id = :id
            LIMIT 1
        ";

        $stmtEquipo = $pdo->prepare($sqlEquipo);

        $stmtEquipo->execute([
            ":id" => $equipoId
        ]);

        $equipoExiste = $stmtEquipo->fetch();


        if (!$equipoExiste) {

            $error = "El equipo seleccionado no existe.";

        } elseif (
            (int) $equipoExiste["cliente_id"]
            !== (int) $clienteId
        ) {

            $error = "El equipo seleccionado no pertenece al cliente.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR TÉCNICO
            |--------------------------------------------------------------------------
            */

            if ($tecnicoId) {

                $sqlTecnico = "
                    SELECT
                        id
                    FROM usuarios
                    WHERE id = :id
                    AND activo = 1
                    LIMIT 1
                ";

                $stmtTecnico = $pdo->prepare(
                    $sqlTecnico
                );

                $stmtTecnico->execute([
                    ":id" => $tecnicoId
                ]);

                $tecnicoExiste =
                    $stmtTecnico->fetch();


                if (!$tecnicoExiste) {

                    $error =
                        "El técnico seleccionado no existe o está inactivo.";
                }
            }


            /*
            |--------------------------------------------------------------------------
            | INSERTAR SERVICIO
            |--------------------------------------------------------------------------
            */

            if ($error === "") {

                $sql = "
                    INSERT INTO servicios
                    (
                        cliente_id,
                        equipo_id,
                        tecnico_id,
                        tipo,
                        descripcion,
                        diagnostico,
                        solucion,
                        costo,
                        estado
                    )
                    VALUES
                    (
                        :cliente_id,
                        :equipo_id,
                        :tecnico_id,
                        :tipo,
                        :descripcion,
                        :diagnostico,
                        :solucion,
                        :costo,
                        :estado
                    )
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([

                    ":cliente_id" =>
                        $clienteId,

                    ":equipo_id" =>
                        $equipoId,

                    ":tecnico_id" =>
                        $tecnicoId ?: null,

                    ":tipo" =>
                        $tipo,

                    ":descripcion" =>
                        $descripcion,

                    ":diagnostico" =>
                        $diagnostico,

                    ":solucion" =>
                        $solucion,

                    ":costo" =>
                        number_format(
                            (float) $costo,
                            2,
                            ".",
                            ""
                        ),

                    ":estado" =>
                        $estado

                ]);


                /*
                |--------------------------------------------------------------------------
                | REDIRECCIÓN
                |--------------------------------------------------------------------------
                */

                header(
                    "Location: servicios.php?mensaje=creado"
                );

                exit;
            }
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
                Nuevo servicio
            </h1>

            <p>
                Registra un nuevo servicio técnico
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
                    Antes de registrar un servicio necesitas
                    tener al menos un cliente.
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
                    href="servicios.php"
                    class="secondary-button"
                    style="margin-left: 8px;"
                >
                    Cancelar
                </a>

            </div>

        </section>


    <?php elseif (count($equipos) === 0): ?>

        <section class="form-card">

            <div class="form-card-header">

                <h2>
                    No hay equipos registrados
                </h2>

                <p>
                    Primero necesitas registrar un equipo
                    para poder crear un servicio.
                </p>

            </div>


            <div class="admin-form">

                <a
                    href="equipo_nuevo.php"
                    class="primary-button"
                >
                    + Registrar equipo
                </a>

                <a
                    href="servicios.php"
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
                    Información del servicio
                </h2>

                <p>
                    Completa los datos del servicio técnico.
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
                                        ($_POST["cliente_id"] ?? "")
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

                        <label for="equipo_id">
                            Equipo *
                        </label>

                        <select
                            id="equipo_id"
                            name="equipo_id"
                            required
                        >

                            <option value="">
                                Selecciona primero un cliente
                            </option>

                            <?php foreach ($equipos as $equipo): ?>

                                <?php

                                $nombreEquipo = trim(
                                    $equipo["marca"]
                                    . " "
                                    . $equipo["modelo"]
                                );

                                if ($nombreEquipo === "") {

                                    $nombreEquipo =
                                        $equipo["tipo"];

                                }

                                ?>

                                <option
                                    value="<?= $equipo["id"] ?>"
                                    data-cliente="<?= $equipo["cliente_id"] ?>"
                                    <?= (
                                        ($_POST["equipo_id"] ?? "")
                                        == $equipo["id"]
                                    )
                                        ? "selected"
                                        : ""
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $nombreEquipo
                                    ) ?>

                                    -

                                    <?= htmlspecialchars(
                                        $equipo["tipo"]
                                    ) ?>

                                    <?php if (
                                        !empty(
                                            $equipo["numero_serie"]
                                        )
                                    ): ?>

                                        -
                                        S/N:
                                        <?= htmlspecialchars(
                                            $equipo["numero_serie"]
                                        ) ?>

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    

                    <div class="form-field">

                        <label for="tipo">
                            Tipo de servicio *
                        </label>

                        <select
                            id="tipo"
                            name="tipo"
                            required
                        >

                            <option value="">
                                Selecciona un servicio
                            </option>

                            <option
                                value="Mantenimiento preventivo"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Mantenimiento preventivo"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Mantenimiento preventivo
                            </option>

                            <option
                                value="Mantenimiento correctivo"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Mantenimiento correctivo"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Mantenimiento correctivo
                            </option>

                            <option
                                value="Diagnóstico"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Diagnóstico"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Diagnóstico
                            </option>

                            <option
                                value="Instalación de software"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Instalación de software"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Instalación de software
                            </option>

                            <option
                                value="Instalación de hardware"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Instalación de hardware"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Instalación de hardware
                            </option>

                            <option
                                value="Formateo"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Formateo"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Formateo
                            </option>

                            <option
                                value="Respaldo de información"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Respaldo de información"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Respaldo de información
                            </option>

                            <option
                                value="Limpieza"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Limpieza"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Limpieza
                            </option>

                            <option
                                value="Reparación"
                                <?= (
                                    ($_POST["tipo"] ?? "")
                                    === "Reparación"
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >
                                Reparación
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

                        <label for="tecnico_id">
                            Técnico
                        </label>

                        <select
                            id="tecnico_id"
                            name="tecnico_id"
                        >

                            <option value="">
                                Sin asignar
                            </option>

                            <?php foreach ($tecnicos as $tecnico): ?>

                                <option
                                    value="<?= $tecnico["id"] ?>"
                                    <?= (
                                        ($_POST["tecnico_id"] ?? "")
                                        == $tecnico["id"]
                                    )
                                        ? "selected"
                                        : ""
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $tecnico["nombre"]
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    

                    <div class="form-field">

                        <label for="estado">
                            Estado
                        </label>

                        <select
                            id="estado"
                            name="estado"
                        >

                            <?php

                            $estados = [
                                "Recibido",
                                "Diagnostico",
                                "En reparacion",
                                "Terminado",
                                "Entregado",
                                "Cancelado"
                            ];

                            ?>

                            <?php foreach ($estados as $estado): ?>

                                <option
                                    value="<?= $estado ?>"
                                    <?= (
                                        ($_POST["estado"] ?? "Recibido")
                                        === $estado
                                    )
                                        ? "selected"
                                        : ""
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $estado
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    

                    <div class="form-field">

                        <label for="costo">
                            Costo
                        </label>

                        <input
                            type="number"
                            id="costo"
                            name="costo"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            value="<?= htmlspecialchars(
                                $_POST["costo"] ?? "0.00"
                            ) ?>"
                        >

                    </div>


                    

                    <div class="form-field full">

                        <label for="descripcion">
                            Descripción del servicio
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            placeholder="Describe el problema reportado por el cliente o el servicio solicitado..."
                        ><?= htmlspecialchars(
                            $_POST["descripcion"] ?? ""
                        ) ?></textarea>

                    </div>


                    

                    <div class="form-field full">

                        <label for="diagnostico">
                            Diagnóstico
                        </label>

                        <textarea
                            id="diagnostico"
                            name="diagnostico"
                            placeholder="Describe el diagnóstico realizado al equipo..."
                        ><?= htmlspecialchars(
                            $_POST["diagnostico"] ?? ""
                        ) ?></textarea>

                    </div>


                    

                    <div class="form-field full">

                        <label for="solucion">
                            Solución
                        </label>

                        <textarea
                            id="solucion"
                            name="solucion"
                            placeholder="Describe el trabajo realizado o la solución aplicada..."
                        ><?= htmlspecialchars(
                            $_POST["solucion"] ?? ""
                        ) ?></textarea>

                    </div>


                </div>


                <div class="form-actions">

                    <a
                        href="servicios.php"
                        class="secondary-button"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        Guardar servicio
                    </button>

                </div>

            </form>

        </section>

    <?php endif; ?>

</main>


<script>

/*
|--------------------------------------------------------------------------
| FILTRAR EQUIPOS POR CLIENTE
|--------------------------------------------------------------------------
*/

const clienteSelect =
    document.getElementById("cliente_id");

const equipoSelect =
    document.getElementById("equipo_id");


function filtrarEquipos() {

    if (!clienteSelect || !equipoSelect) {
        return;
    }

    const clienteId =
        clienteSelect.value;

    const opciones =
        equipoSelect.querySelectorAll(
            "option[data-cliente]"
        );

    let equiposDisponibles = 0;

    opciones.forEach(function(opcion) {

        if (
            clienteId !== "" &&
            opcion.dataset.cliente === clienteId
        ) {

            opcion.hidden = false;

            equiposDisponibles++;

        } else {

            opcion.hidden = true;
        }

    });


    /*
    |--------------------------------------------------------------------------
    | REINICIAR SELECCIÓN
    |--------------------------------------------------------------------------
    */

    const equipoSeleccionado =
        equipoSelect.querySelector(
            "option:checked"
        );


    if (
        !equipoSeleccionado ||
        equipoSeleccionado.dataset.cliente !== clienteId
    ) {

        equipoSelect.value = "";
    }


    /*
    |--------------------------------------------------------------------------
    | MENSAJE
    |--------------------------------------------------------------------------
    */

    if (clienteId === "") {

        equipoSelect.value = "";

        equipoSelect.options[0].text =
            "Selecciona primero un cliente";

    } else if (equiposDisponibles === 0) {

        equipoSelect.value = "";

        equipoSelect.options[0].text =
            "Este cliente no tiene equipos registrados";

    } else {

        equipoSelect.options[0].text =
            "Selecciona un equipo";
    }

}




if (clienteSelect) {

    clienteSelect.addEventListener(
        "change",
        filtrarEquipos
    );

}




filtrarEquipos();

</script>


</div>

</body>

</html>