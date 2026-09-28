<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$buscar = trim($_GET["buscar"] ?? "");

/*
|--------------------------------------------------------------------------
| Listar garantías
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            garantias.id,
            garantias.fecha_inicio,
            garantias.fecha_fin,
            garantias.descripcion,
            garantias.estado,

            servicios.tipo AS servicio_tipo,
            servicios.costo AS servicio_costo,

            clientes.nombre AS cliente,

            equipos.tipo AS equipo_tipo,
            equipos.marca AS equipo_marca,
            equipos.modelo AS equipo_modelo

        FROM garantias

        INNER JOIN servicios
            ON garantias.servicio_id = servicios.id

        INNER JOIN clientes
            ON servicios.cliente_id = clientes.id

        INNER JOIN equipos
            ON servicios.equipo_id = equipos.id

        WHERE
            clientes.nombre LIKE :buscar
            OR servicios.tipo LIKE :buscar
            OR garantias.descripcion LIKE :buscar
            OR garantias.estado LIKE :buscar

        ORDER BY garantias.id DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":buscar" => "%" . $buscar . "%"
]);

$garantias = $stmt->fetchAll();

$mensaje = $_GET["mensaje"] ?? "";

?>

<?php

$tituloPagina = "Garantías";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="admin-main">

    <div class="admin-topbar">

        <div>

            <h1>Garantías</h1>

            <p>
                Controla las garantías de los servicios realizados.
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


    <?php if ($mensaje === "creado"): ?>

        <div class="alert-success">
            Garantía creada correctamente.
        </div>

    <?php elseif ($mensaje === "actualizado"): ?>

        <div class="alert-success">
            Garantía actualizada correctamente.
        </div>

    <?php elseif ($mensaje === "eliminado"): ?>

        <div class="alert-success">
            Garantía eliminada correctamente.
        </div>

    <?php endif; ?>


    <div class="page-card">

        <div class="page-card-header">

            <div>

                <h2>Listado de garantías</h2>

                <p>
                    <?= count($garantias) ?>
                    garantía(s) encontrada(s).
                </p>

            </div>

            <a
                href="garantia_nuevo.php"
                class="primary-button"
            >
                + Nueva garantía
            </a>

        </div>


        <div class="search-container">

            <form method="GET">

                <div class="search-box">

                    <span>🔎</span>

                    <input
                        type="text"
                        name="buscar"
                        value="<?= htmlspecialchars($buscar) ?>"
                        placeholder="Buscar por cliente, servicio, estado..."
                    >

                </div>

                <?php if ($buscar !== ""): ?>

                    <a
                        href="garantias.php"
                        class="clear-search"
                    >
                        Limpiar
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <div class="table-container">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Cliente</th>

                        <th>Equipo</th>

                        <th>Servicio</th>

                        <th>Inicio</th>

                        <th>Vencimiento</th>

                        <th>Estado</th>

                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (empty($garantias)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="empty-state"
                            >
                                No hay garantías registradas.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($garantias as $garantia): ?>

                            <?php

                          
                            $fechaFin =
                                strtotime(
                                    $garantia["fecha_fin"]
                                );

                            $hoy = strtotime(
                                date("Y-m-d")
                            );

                            $estadoMostrar =
                                $garantia["estado"];

                            if (
                                $garantia["estado"] === "Activa"
                                &&
                                $fechaFin < $hoy
                            ) {

                                $estadoMostrar = "Vencida";

                            }

                            ?>

                            <tr>

                                <td>



                                </td>


                                <td>

                                    <div class="client-cell">

                                        <div class="client-avatar">

                                            <?= strtoupper(
                                                substr(
                                                    $garantia["cliente"],
                                                    0,
                                                    1
                                                )
                                            ) ?>

                                        </div>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $garantia["cliente"]
                                            ) ?>

                                        </strong>

                                    </div>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $garantia["equipo_tipo"]
                                    ) ?>

                                    <?php if (
                                        $garantia["equipo_marca"]
                                    ): ?>

                                        <br>

                                        <small>

                                            <?= htmlspecialchars(
                                                $garantia["equipo_marca"]
                                            ) ?>

                                            <?= htmlspecialchars(
                                                $garantia["equipo_modelo"] ?? ""
                                            ) ?>

                                        </small>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $garantia["servicio_tipo"]
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime(
                                            $garantia["fecha_inicio"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime(
                                            $garantia["fecha_fin"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <?php

                                    $claseEstado = match (
                                        $estadoMostrar
                                    ) {

                                        "Activa" =>
                                            "status-success",

                                        "Vencida" =>
                                            "status-warning",

                                        "Cancelada" =>
                                            "status-danger",

                                        default =>
                                            "status-info"

                                    };

                                    ?>

                                    <span
                                        class="status-badge <?= $claseEstado ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $estadoMostrar
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="garantia_editar.php?id=<?= (int)$garantia["id"] ?>"
                                            class="action-button edit"
                                            title="Editar"
                                        >
                                            ✏️
                                        </a>

                                        <a
                                            href="garantia_eliminar.php?id=<?= (int)$garantia["id"] ?>"
                                            class="action-button delete"
                                            title="Eliminar"
                                            onclick="return confirm('¿Estás seguro de eliminar esta garantía?');"
                                        >
                                            🗑️
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

</div>

</body>

</html>