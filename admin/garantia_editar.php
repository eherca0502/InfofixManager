<?php

require_once "../includes/auth.php";
require_once "../config/database.php";


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

if (!$id) {

    header("Location: garantias.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener garantía
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            servicio_id,
            fecha_inicio,
            fecha_fin,
            descripcion,
            estado

        FROM garantias

        WHERE id = :id

        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$garantia = $stmt->fetch();


if (!$garantia) {

    header(
        "Location: garantias.php?mensaje=no_encontrado"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| Cargar servicios
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            servicios.id,
            servicios.tipo,
            servicios.estado,
            clientes.nombre AS cliente,
            equipos.tipo AS equipo_tipo,
            equipos.marca AS equipo_marca,
            equipos.modelo AS equipo_modelo

        FROM servicios

        INNER JOIN clientes
            ON servicios.cliente_id = clientes.id

        INNER JOIN equipos
            ON servicios.equipo_id = equipos.id

        ORDER BY servicios.id DESC";

$stmt = $pdo->query($sql);

$servicios = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$errores = [];

$servicio_id = $garantia["servicio_id"];
$fecha_inicio = $garantia["fecha_inicio"];
$fecha_fin = $garantia["fecha_fin"];
$descripcion = $garantia["descripcion"] ?? "";
$estado = $garantia["estado"];


/*
|--------------------------------------------------------------------------
| Procesar formulario
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verificarTokenCSRF();

    $servicio_id = filter_input(
        INPUT_POST,
        "servicio_id",
        FILTER_VALIDATE_INT
    );

    $fecha_inicio = trim(
        $_POST["fecha_inicio"] ?? ""
    );

    $fecha_fin = trim(
        $_POST["fecha_fin"] ?? ""
    );

    $descripcion = trim(
        $_POST["descripcion"] ?? ""
    );

    $estado = trim(
        $_POST["estado"] ?? "Activa"
    );


    /*
    |--------------------------------------------------------------------------
    | Validar servicio
    |--------------------------------------------------------------------------
    */

    if (!$servicio_id) {

        $errores[] =
            "Debes seleccionar un servicio.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validar fechas
    |--------------------------------------------------------------------------
    */

    if ($fecha_inicio === "") {

        $errores[] =
            "Debes indicar la fecha de inicio.";

    }

    if ($fecha_fin === "") {

        $errores[] =
            "Debes indicar la fecha de vencimiento.";

    }


    if (
        $fecha_inicio !== ""
        &&
        $fecha_fin !== ""
        &&
        $fecha_fin < $fecha_inicio
    ) {

        $errores[] =
            "La fecha de vencimiento no puede ser anterior a la fecha de inicio.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validar estado
    |--------------------------------------------------------------------------
    */

    $estadosPermitidos = [
        "Activa",
        "Vencida",
        "Cancelada"
    ];

    if (!in_array(
        $estado,
        $estadosPermitidos,
        true
    )) {

        $errores[] =
            "El estado seleccionado no es válido.";

    }


    /*
    |--------------------------------------------------------------------------
    | Verificar servicio
    |--------------------------------------------------------------------------
    */

    if ($servicio_id) {

        $sql = "SELECT id
                FROM servicios
                WHERE id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ":id" => $servicio_id
        ]);

        $servicioExiste = $stmt->fetch();

        if (!$servicioExiste) {

            $errores[] =
                "El servicio seleccionado no existe.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Evitar garantía duplicada
    |--------------------------------------------------------------------------
    */

    if ($servicio_id) {

        $sql = "SELECT id

                FROM garantias

                WHERE servicio_id = :servicio_id

                AND id <> :id

                LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ":servicio_id" => $servicio_id,

            ":id" => $id

        ]);

        $garantiaExiste = $stmt->fetch();

        if ($garantiaExiste) {

            $errores[] =
                "Este servicio ya tiene otra garantía registrada.";

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar
    |--------------------------------------------------------------------------
    */

    if (empty($errores)) {

        $sql = "UPDATE garantias

                SET
                    servicio_id = :servicio_id,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    descripcion = :descripcion,
                    estado = :estado

                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ":servicio_id" => $servicio_id,

            ":fecha_inicio" => $fecha_inicio,

            ":fecha_fin" => $fecha_fin,

            ":descripcion" =>
                $descripcion !== ""
                    ? $descripcion
                    : null,

            ":estado" => $estado,

            ":id" => $id

        ]);


        header(
            "Location: garantias.php?mensaje=actualizado"
        );

        exit;

    }

}

?>

<?php

$tituloPagina = "Editar garantía";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="admin-main">

    <div class="admin-topbar">

        <div>

            <h1>Editar garantía</h1>

            <p>
                Modifica la información de la garantía.
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


    <?php if (!empty($errores)): ?>

        <div class="alert-error">

            <strong>
                No se pudo actualizar la garantía:
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

                <h2>Información de la garantía</h2>

                <p>
                    Actualiza los datos necesarios.
                </p>

            </div>

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

                    <label for="servicio_id">
                        Servicio *
                    </label>

                    <select
                        name="servicio_id"
                        id="servicio_id"
                        required
                    >

                        <option value="">
                            Selecciona un servicio
                        </option>

                        <?php foreach ($servicios as $servicio): ?>

                            <?php

                            $textoServicio =
                                "#"
                                . $servicio["id"]
                                . " - "
                                . $servicio["cliente"]
                                . " - "
                                . $servicio["tipo"]
                                . " - "
                                . $servicio["equipo_tipo"];

                            if (
                                $servicio["equipo_marca"]
                            ) {

                                $textoServicio .=
                                    " "
                                    . $servicio["equipo_marca"];

                            }

                            if (
                                $servicio["equipo_modelo"]
                            ) {

                                $textoServicio .=
                                    " "
                                    . $servicio["equipo_modelo"];

                            }

                            ?>

                            <option
                                value="<?= (int)$servicio["id"] ?>"
                                <?= (
                                    (string)$servicio_id
                                    ===
                                    (string)$servicio["id"]
                                )
                                    ? "selected"
                                    : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $textoServicio
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                

                <div class="form-field">

                    <label for="fecha_inicio">
                        Fecha de inicio *
                    </label>

                    <input
                        type="date"
                        name="fecha_inicio"
                        id="fecha_inicio"
                        value="<?= htmlspecialchars($fecha_inicio) ?>"
                        required
                    >

                </div>


                

                <div class="form-field">

                    <label for="fecha_fin">
                        Fecha de vencimiento *
                    </label>

                    <input
                        type="date"
                        name="fecha_fin"
                        id="fecha_fin"
                        value="<?= htmlspecialchars($fecha_fin) ?>"
                        required
                    >

                </div>


                

                <div class="form-field">

                    <label for="estado">
                        Estado *
                    </label>

                    <select
                        name="estado"
                        id="estado"
                        required
                    >

                        <option
                            value="Activa"
                            <?= $estado === "Activa"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Activa
                        </option>

                        <option
                            value="Vencida"
                            <?= $estado === "Vencida"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Vencida
                        </option>

                        <option
                            value="Cancelada"
                            <?= $estado === "Cancelada"
                                ? "selected"
                                : ""
                            ?>
                        >
                            Cancelada
                        </option>

                    </select>

                </div>


                

                <div class="form-field full">

                    <label for="descripcion">
                        Descripción
                    </label>

                    <textarea
                        name="descripcion"
                        id="descripcion"
                        rows="5"
                        placeholder="Describe qué cubre la garantía..."
                    ><?= htmlspecialchars($descripcion) ?></textarea>

                </div>


            </div>


            <div class="form-actions">

                <a
                    href="garantias.php"
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