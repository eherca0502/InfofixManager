<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Editar servicio";

$error = "";


/*
|--------------------------------------------------------------------------
| OBTENER ID DEL SERVICIO
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {

    header("Location: servicios.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| OBTENER SERVICIO
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM servicios
    WHERE id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$servicio = $stmt->fetch();


if (!$servicio) {

    header("Location: servicios.php");
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
*/

$sqlEquipos = "
    SELECT
        id,
        cliente_id,
        tipo,
        marca,
        modelo,
        numero_serie
    FROM equipos
    ORDER BY id DESC
";

$stmtEquipos = $pdo->query($sqlEquipos);

$equipos = $stmtEquipos->fetchAll();


/*
|--------------------------------------------------------------------------
| ACTUALIZAR SERVICIO
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

            $error =
                "El equipo seleccionado no pertenece al cliente.";

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
            | ACTUALIZAR
            |--------------------------------------------------------------------------
            */

            if ($error === "") {

                $sqlUpdate = "
                    UPDATE servicios
                    SET
                        cliente_id = :cliente_id,
                        equipo_id = :equipo_id,
                        tecnico_id = :tecnico_id,
                        tipo = :tipo,
                        descripcion = :descripcion,
                        diagnostico = :diagnostico,
                        solucion = :solucion,
                        costo = :costo,
                        estado = :estado
                    WHERE id = :id
                ";

                $stmtUpdate =
                    $pdo->prepare($sqlUpdate);

                $stmtUpdate->execute([

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
                        $estado,

                    ":id" =>
                        $id

                ]);


                /*
                |--------------------------------------------------------------------------
                | FECHA DE ENTREGA
                |--------------------------------------------------------------------------
                |
                | Cuando el servicio pasa a "Entregado",
                | registramos automáticamente la fecha.
                |
                */

                if ($estado === "Entregado") {

                    $sqlEntrega = "
                        UPDATE servicios
                        SET fecha_entrega = COALESCE(
                            fecha_entrega,
                            NOW()
                        )
                        WHERE id = :id
                    ";

                    $stmtEntrega =
                        $pdo->prepare($sqlEntrega);

                    $stmtEntrega->execute([
                        ":id" => $id
                    ]);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | SI YA NO ESTÁ ENTREGADO
                    |--------------------------------------------------------------------------
                    */

                    $sqlEntrega = "
                        UPDATE servicios
                        SET fecha_entrega = NULL
                        WHERE id = :id
                    ";

                    $stmtEntrega =
                        $pdo->prepare($sqlEntrega);

                    $stmtEntrega->execute([
                        ":id" => $id
                    ]);
                }


                header(
                    "Location: servicios.php?mensaje=actualizado"
                );

                exit;
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| VALORES DEL FORMULARIO
|--------------------------------------------------------------------------
*/

$valorCliente =
    $_POST["cliente_id"]
    ?? $servicio["cliente_id"];

$valorEquipo =
    $_POST["equipo_id"]
    ?? $servicio["equipo_id"];

$valorTecnico =
    $_POST["tecnico_id"]
    ?? $servicio["tecnico_id"];

$valorTipo =
    $_POST["tipo"]
    ?? $servicio["tipo"];

$valorDescripcion =
    $_POST["descripcion"]
    ?? $servicio["descripcion"];

$valorDiagnostico =
    $_POST["diagnostico"]
    ?? $servicio["diagnostico"];

$valorSolucion =
    $_POST["solucion"]
    ?? $servicio["solucion"];

$valorCosto =
    $_POST["costo"]
    ?? $servicio["costo"];

$valorEstado =
    $_POST["estado"]
    ?? $servicio["estado"];


require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="main-content">

    <header class="topbar">

        <div>

            <h1>
                Editar servicio
            </h1>

            <p>
                Actualiza la información del servicio técnico
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
                Información del servicio
            </h2>

            <p>
                Servicio #<?= $servicio["id"] ?>
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

                    <label for="equipo_id">
                        Equipo *
                    </label>

                    <select
                        id="equipo_id"
                        name="equipo_id"
                        required
                    >

                        <option value="">
                            Selecciona un equipo
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
                                    $valorEquipo
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
                            <?= $valorTipo ===
                                "Mantenimiento preventivo"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Mantenimiento preventivo
                        </option>

                        <option
                            value="Mantenimiento correctivo"
                            <?= $valorTipo ===
                                "Mantenimiento correctivo"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Mantenimiento correctivo
                        </option>

                        <option
                            value="Diagnóstico"
                            <?= $valorTipo ===
                                "Diagnóstico"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Diagnóstico
                        </option>

                        <option
                            value="Instalación de software"
                            <?= $valorTipo ===
                                "Instalación de software"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Instalación de software
                        </option>

                        <option
                            value="Instalación de hardware"
                            <?= $valorTipo ===
                                "Instalación de hardware"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Instalación de hardware
                        </option>

                        <option
                            value="Formateo"
                            <?= $valorTipo ===
                                "Formateo"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Formateo
                        </option>

                        <option
                            value="Respaldo de información"
                            <?= $valorTipo ===
                                "Respaldo de información"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Respaldo de información
                        </option>

                        <option
                            value="Limpieza"
                            <?= $valorTipo ===
                                "Limpieza"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Limpieza
                        </option>

                        <option
                            value="Reparación"
                            <?= $valorTipo ===
                                "Reparación"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Reparación
                        </option>

                        <option
                            value="Otro"
                            <?= $valorTipo ===
                                "Otro"
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
                                    $valorTecnico
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
                                    $valorEstado ===
                                    $estado
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
                        value="<?= htmlspecialchars(
                            $valorCosto
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
                        placeholder="Describe el problema reportado o servicio solicitado..."
                    ><?= htmlspecialchars(
                        $valorDescripcion ?? ""
                    ) ?></textarea>

                </div>


                

                <div class="form-field full">

                    <label for="diagnostico">
                        Diagnóstico
                    </label>

                    <textarea
                        id="diagnostico"
                        name="diagnostico"
                        placeholder="Describe el diagnóstico realizado..."
                    ><?= htmlspecialchars(
                        $valorDiagnostico ?? ""
                    ) ?></textarea>

                </div>


                

                <div class="form-field full">

                    <label for="solucion">
                        Solución
                    </label>

                    <textarea
                        id="solucion"
                        name="solucion"
                        placeholder="Describe la solución aplicada..."
                    ><?= htmlspecialchars(
                        $valorSolucion ?? ""
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
                    Guardar cambios
                </button>

            </div>

        </form>

    </section>

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
    | COMPROBAR EQUIPO SELECCIONADO
    |--------------------------------------------------------------------------
    */

    const equipoActual =
        equipoSelect.querySelector(
            "option:checked"
        );


    if (
        !equipoActual ||
        equipoActual.dataset.cliente !== clienteId
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