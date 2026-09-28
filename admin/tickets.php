<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$buscar = trim($_GET["buscar"] ?? "");

$sql = "SELECT
            tickets.id,
            tickets.asunto,
            tickets.prioridad,
            tickets.estado,
            tickets.fecha_creacion,

            clientes.nombre AS cliente,

            equipos.tipo AS equipo_tipo,
            equipos.marca AS equipo_marca,
            equipos.modelo AS equipo_modelo,

            usuarios.nombre AS usuario

        FROM tickets

        LEFT JOIN clientes
            ON tickets.cliente_id = clientes.id

        LEFT JOIN equipos
            ON tickets.equipo_id = equipos.id

        LEFT JOIN usuarios
            ON tickets.usuario_id = usuarios.id

        WHERE
            tickets.asunto LIKE :buscar
            OR tickets.descripcion LIKE :buscar
            OR clientes.nombre LIKE :buscar
            OR tickets.prioridad LIKE :buscar
            OR tickets.estado LIKE :buscar

        ORDER BY tickets.id DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":buscar" => "%" . $buscar . "%"
]);

$tickets = $stmt->fetchAll();

$mensaje = $_GET["mensaje"] ?? "";

?>

<?php

$tituloPagina = "Tickets";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="admin-main">

    <div class="admin-topbar">

        <div>

            <h1>Tickets</h1>

            <p>
                Gestiona las solicitudes de soporte técnico.
            </p>

        </div>

        <div class="topbar-user">

            <div class="topbar-avatar">
                <?= strtoupper(substr($nombreUsuario, 0, 1)) ?>
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
            Ticket creado correctamente.
        </div>

    <?php elseif ($mensaje === "actualizado"): ?>

        <div class="alert-success">
            Ticket actualizado correctamente.
        </div>

    <?php elseif ($mensaje === "eliminado"): ?>

        <div class="alert-success">
            Ticket eliminado correctamente.
        </div>

    <?php endif; ?>


    <div class="page-card">

        <div class="page-card-header">

            <div>

                <h2>Listado de tickets</h2>

                <p>
                    <?= count($tickets) ?> ticket(s) encontrado(s).
                </p>

            </div>

            <a
                href="ticket_nuevo.php"
                class="primary-button"
            >
                + Nuevo ticket
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
                        placeholder="Buscar por asunto, cliente, prioridad o estado..."
                    >

                </div>

                <?php if ($buscar !== ""): ?>

                    <a
                        href="tickets.php"
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

                        <th>Ticket</th>

                        <th>Cliente</th>

                        <th>Equipo</th>

                        <th>Prioridad</th>

                        <th>Estado</th>

                        <th>Usuario</th>

                        <th>Fecha</th>

                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (empty($tickets)): ?>

                        <tr>

                            <td
                                colspan="9"
                                class="empty-state"
                            >
                                No hay tickets registrados.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($tickets as $ticket): ?>

                            <tr>

                                <td>

                                </td>


                                <td>

                                    <div class="client-cell">

                                        <div class="client-avatar">
                                            T
                                        </div>

                                        <div>

                                            <strong>
                                                <?= htmlspecialchars($ticket["asunto"]) ?>
                                            </strong>

                                        </div>

                                    </div>

                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $ticket["cliente"] ?? "Sin cliente"
                                    ) ?>
                                </td>


                                <td>

                                    <?php if ($ticket["equipo_tipo"]): ?>

                                        <?= htmlspecialchars($ticket["equipo_tipo"]) ?>

                                        <?php if ($ticket["equipo_marca"]): ?>

                                            <br>

                                            <small>
                                                <?= htmlspecialchars(
                                                    $ticket["equipo_marca"]
                                                ) ?>

                                                <?= htmlspecialchars(
                                                    $ticket["equipo_modelo"] ?? ""
                                                ) ?>
                                            </small>

                                        <?php endif; ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Sin equipo
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php

                                    $clasePrioridad = match (
                                        $ticket["prioridad"]
                                    ) {

                                        "Critica" => "status-danger",

                                        "Alta" => "status-warning",

                                        "Media" => "status-info",

                                        default => "status-success"

                                    };

                                    ?>

                                    <span class="status-badge <?= $clasePrioridad ?>">
                                        <?= htmlspecialchars($ticket["prioridad"]) ?>
                                    </span>

                                </td>


                                <td>

                                    <?php

                                    $claseEstado = match (
                                        $ticket["estado"]
                                    ) {

                                        "Abierto" => "status-warning",

                                        "En proceso" => "status-info",

                                        "Resuelto" => "status-success",

                                        "Cerrado" => "status-success",

                                        default => "status-info"

                                    };

                                    ?>

                                    <span class="status-badge <?= $claseEstado ?>">
                                        <?= htmlspecialchars($ticket["estado"]) ?>
                                    </span>

                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $ticket["usuario"] ?? "Sin asignar"
                                    ) ?>
                                </td>


                                <td>

                                    <?= date(
                                        "d/m/Y H:i",
                                        strtotime($ticket["fecha_creacion"])
                                    ) ?>

                                </td>


                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="ticket_editar.php?id=<?= (int)$ticket["id"] ?>"
                                            class="action-button edit"
                                            title="Editar"
                                        >
                                            ✏️
                                        </a>

                                        <a
                                            href="ticket_eliminar.php?id=<?= (int)$ticket["id"] ?>"
                                            class="action-button delete"
                                            title="Eliminar"
                                            onclick="return confirm('¿Estás seguro de eliminar este ticket?');"
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