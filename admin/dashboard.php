<?php

require_once "../includes/auth.php";
require_once "../config/database.php";


$tituloPagina = "Dashboard";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";


/*
|--------------------------------------------------------------------------
| DATOS DEL USUARIO
|--------------------------------------------------------------------------
*/

$nombreUsuario =
    $_SESSION["usuario_nombre"] ?? "Usuario";

$rolUsuario =
    $_SESSION["usuario_rol"] ?? "Usuario";


/*
|--------------------------------------------------------------------------
| ESTADÍSTICAS GENERALES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT COUNT(*) FROM clientes"
);

$totalClientes =
    (int)$stmt->fetchColumn();


$stmt = $pdo->query(
    "SELECT COUNT(*) FROM equipos"
);

$totalEquipos =
    (int)$stmt->fetchColumn();


$stmt = $pdo->query(
    "SELECT COUNT(*) FROM servicios"
);

$totalServicios =
    (int)$stmt->fetchColumn();


$stmt = $pdo->query(
    "SELECT COUNT(*) FROM tickets"
);

$totalTickets =
    (int)$stmt->fetchColumn();


$stmt = $pdo->query(
    "SELECT COUNT(*) FROM garantias"
);

$totalGarantias =
    (int)$stmt->fetchColumn();


$stmt = $pdo->query(
    "SELECT COUNT(*) FROM inventario"
);

$totalInventario =
    (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| SERVICIOS PENDIENTES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT COUNT(*)

     FROM servicios

     WHERE estado NOT IN (
         'Entregado',
         'Cancelado'
     )"
);

$serviciosPendientes =
    (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| TICKETS ABIERTOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT COUNT(*)

     FROM tickets

     WHERE estado IN (
         'Abierto',
         'En proceso'
     )"
);

$ticketsAbiertos =
    (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| INVENTARIO BAJO / AGOTADO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT COUNT(*)

     FROM inventario

     WHERE cantidad <= stock_minimo"
);

$stockBajo =
    (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| PRODUCTOS AGOTADOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT COUNT(*)

     FROM inventario

     WHERE cantidad <= 0"
);

$productosAgotados =
    (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| GARANTÍAS PRÓXIMAS A VENCER
|--------------------------------------------------------------------------
|
| Se consideran las garantías activas cuya fecha de vencimiento
| esté entre hoy y los próximos 30 días.
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT COUNT(*)

     FROM garantias

     WHERE estado = 'Activa'

     AND fecha_fin >= CURDATE()

     AND fecha_fin <= DATE_ADD(
         CURDATE(),
         INTERVAL 30 DAY
     )"
);

$garantiasPorVencer =
    (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| GARANTÍAS VENCIDAS POR FECHA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query(
    "SELECT COUNT(*)

     FROM garantias

     WHERE estado = 'Activa'

     AND fecha_fin < CURDATE()"
);

$garantiasVencidas =
    (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| SERVICIOS RECIENTES
|--------------------------------------------------------------------------
*/

$sqlServicios = "

    SELECT

        servicios.id,
        servicios.tipo,
        servicios.estado,
        servicios.costo,
        servicios.fecha_recepcion,

        clientes.nombre AS cliente,

        equipos.tipo AS equipo_tipo,
        equipos.marca,
        equipos.modelo

    FROM servicios

    INNER JOIN clientes
        ON servicios.cliente_id = clientes.id

    INNER JOIN equipos
        ON servicios.equipo_id = equipos.id

    ORDER BY servicios.id DESC

    LIMIT 5

";

$stmt = $pdo->query($sqlServicios);

$serviciosRecientes =
    $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| TICKETS RECIENTES
|--------------------------------------------------------------------------
*/

$sqlTickets = "

    SELECT

        tickets.id,
        tickets.asunto,
        tickets.prioridad,
        tickets.estado,
        tickets.fecha_creacion,

        clientes.nombre AS cliente

    FROM tickets

    LEFT JOIN clientes
        ON tickets.cliente_id = clientes.id

    ORDER BY tickets.id DESC

    LIMIT 5

";

$stmt = $pdo->query($sqlTickets);

$ticketsRecientes =
    $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| PRODUCTOS CON STOCK BAJO
|--------------------------------------------------------------------------
*/

$sqlStock = "

    SELECT

        id,
        nombre,
        categoria,
        cantidad,
        stock_minimo

    FROM inventario

    WHERE cantidad <= stock_minimo

    ORDER BY cantidad ASC, id DESC

    LIMIT 5

";

$stmt = $pdo->query($sqlStock);

$productosStockBajo =
    $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| GARANTÍAS PRÓXIMAS A VENCER
|--------------------------------------------------------------------------
*/

$sqlGarantias = "

    SELECT

        garantias.id,
        garantias.fecha_fin,
        garantias.estado,

        clientes.nombre AS cliente,

        servicios.tipo AS servicio_tipo

    FROM garantias

    INNER JOIN servicios
        ON garantias.servicio_id = servicios.id

    INNER JOIN clientes
        ON servicios.cliente_id = clientes.id

    WHERE

        garantias.estado = 'Activa'

        AND garantias.fecha_fin >= CURDATE()

        AND garantias.fecha_fin <= DATE_ADD(
            CURDATE(),
            INTERVAL 30 DAY
        )

    ORDER BY garantias.fecha_fin ASC

    LIMIT 5

";

$stmt = $pdo->query($sqlGarantias);

$garantiasRecientes =
    $stmt->fetchAll();


?>

<main class="main-content">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="topbar">

        <div>

            <h1>
                Dashboard
            </h1>

            <p>
                Resumen general de Infofix Manager
            </p>

        </div>


        <div class="topbar-user">

            <div class="user-avatar">

                <?= strtoupper(
                    substr(
                        $nombreUsuario,
                        0,
                        1
                    )
                ) ?>

            </div>


            <div class="user-info">

                <strong>
                    <?= htmlspecialchars(
                        $nombreUsuario
                    ) ?>
                </strong>

                <span>
                    <?= htmlspecialchars(
                        $rolUsuario
                    ) ?>
                </span>

            </div>

        </div>

    </header>


    <!-- =====================================================
         BIENVENIDA
    ====================================================== -->

    <section class="welcome-card">

        <div>

            <span class="welcome-label">
                PANEL DE CONTROL
            </span>

            <h2>
                ¡Bienvenido,
                <?= htmlspecialchars(
                    $nombreUsuario
                ) ?>!
            </h2>

            <p>
                Aquí tienes un resumen de la actividad
                actual de tu negocio.
            </p>

        </div>


        <div class="welcome-icon">
            IF
        </div>

    </section>


    <!-- =====================================================
         ESTADÍSTICAS PRINCIPALES
    ====================================================== -->

    <section class="stats-grid">


        

        <a
            href="clientes.php"
            class="stat-card dashboard-link-card"
        >

            <div class="stat-icon blue">
                👥
            </div>

            <div class="stat-info">

                <span>
                    Clientes
                </span>

                <strong>
                    <?= $totalClientes ?>
                </strong>

            </div>

        </a>


        

        <a
            href="equipos.php"
            class="stat-card dashboard-link-card"
        >

            <div class="stat-icon purple">
                💻
            </div>

            <div class="stat-info">

                <span>
                    Equipos
                </span>

                <strong>
                    <?= $totalEquipos ?>
                </strong>

            </div>

        </a>


        

        <a
            href="servicios.php"
            class="stat-card dashboard-link-card"
        >

            <div class="stat-icon orange">
                🔧
            </div>

            <div class="stat-info">

                <span>
                    Servicios
                </span>

                <strong>
                    <?= $totalServicios ?>
                </strong>

            </div>

        </a>


        

        <a
            href="tickets.php"
            class="stat-card dashboard-link-card"
        >

            <div class="stat-icon green">
                🎫
            </div>

            <div class="stat-info">

                <span>
                    Tickets
                </span>

                <strong>
                    <?= $totalTickets ?>
                </strong>

            </div>

        </a>


    </section>


    <!-- =====================================================
         SEGUNDA FILA
    ====================================================== -->

    <section class="secondary-stats">


        <a
            href="garantias.php"
            class="mini-stat dashboard-mini-link"
        >

            <div>
                🛡️
            </div>

            <span>
                Garantías
            </span>

            <strong>
                <?= $totalGarantias ?>
            </strong>

        </a>


        <a
            href="inventario.php"
            class="mini-stat dashboard-mini-link"
        >

            <div>
                📦
            </div>

            <span>
                Productos en inventario
            </span>

            <strong>
                <?= $totalInventario ?>
            </strong>

        </a>


        <div class="mini-stat dashboard-mini-link">

            <div>
                🔧
            </div>

            <span>
                Servicios pendientes
            </span>

            <strong>
                <?= $serviciosPendientes ?>
            </strong>

        </div>


        <div class="mini-stat dashboard-mini-link">

            <div>
                🎫
            </div>

            <span>
                Tickets abiertos
            </span>

            <strong>
                <?= $ticketsAbiertos ?>
            </strong>

        </div>


    </section>


    <!-- =====================================================
         ALERTAS
    ====================================================== -->

    <section class="dashboard-alerts">


        

        <a
            href="inventario.php"
            class="dashboard-alert dashboard-alert-warning"
        >

            <div class="dashboard-alert-icon">
                📦
            </div>

            <div>

                <strong>
                    Stock bajo
                </strong>

                <span>

                    <?php if ($stockBajo > 0): ?>

                        <?= $stockBajo ?>
                        producto(s) requieren atención.

                    <?php else: ?>

                        Todo el inventario está en niveles normales.

                    <?php endif; ?>

                </span>

            </div>

            <div class="dashboard-alert-number">
                <?= $stockBajo ?>
            </div>

        </a>


        

        <a
            href="inventario.php"
            class="dashboard-alert dashboard-alert-danger"
        >

            <div class="dashboard-alert-icon">
                🔴
            </div>

            <div>

                <strong>
                    Productos agotados
                </strong>

                <span>

                    <?php if ($productosAgotados > 0): ?>

                        <?= $productosAgotados ?>
                        producto(s) sin existencias.

                    <?php else: ?>

                        No hay productos agotados.

                    <?php endif; ?>

                </span>

            </div>

            <div class="dashboard-alert-number">
                <?= $productosAgotados ?>
            </div>

        </a>


        

        <a
            href="garantias.php"
            class="dashboard-alert dashboard-alert-info"
        >

            <div class="dashboard-alert-icon">
                🛡️
            </div>

            <div>

                <strong>
                    Garantías próximas a vencer
                </strong>

                <span>

                    <?php if ($garantiasPorVencer > 0): ?>

                        <?= $garantiasPorVencer ?>
                        garantía(s) vencen en los próximos 30 días.

                    <?php else: ?>

                        No hay garantías próximas a vencer.

                    <?php endif; ?>

                </span>

            </div>

            <div class="dashboard-alert-number">
                <?= $garantiasPorVencer ?>
            </div>

        </a>


        

        <a
            href="garantias.php"
            class="dashboard-alert dashboard-alert-danger"
        >

            <div class="dashboard-alert-icon">
                ⚠️
            </div>

            <div>

                <strong>
                    Garantías vencidas
                </strong>

                <span>

                    <?php if ($garantiasVencidas > 0): ?>

                        <?= $garantiasVencidas ?>
                        garantía(s) aparecen vencidas.

                    <?php else: ?>

                        No hay garantías vencidas.

                    <?php endif; ?>

                </span>

            </div>

            <div class="dashboard-alert-number">
                <?= $garantiasVencidas ?>
            </div>

        </a>


    </section>


    <!-- =====================================================
         ACCESOS RÁPIDOS
    ====================================================== -->

    <section class="quick-actions dashboard-card">

        <div class="card-header">

            <div>

                <h3>
                    Acciones rápidas
                </h3>

                <p>
                    Accede rápidamente a las funciones principales.
                </p>

            </div>

        </div>


        <div class="quick-actions-grid">


            <a
                href="cliente_nuevo.php"
                class="quick-action"
            >

                <span>
                    👤
                </span>

                <div>

                    <strong>
                        Nuevo cliente
                    </strong>

                    <small>
                        Registrar cliente
                    </small>

                </div>

            </a>


            <a
                href="equipo_nuevo.php"
                class="quick-action"
            >

                <span>
                    💻
                </span>

                <div>

                    <strong>
                        Nuevo equipo
                    </strong>

                    <small>
                        Registrar equipo
                    </small>

                </div>

            </a>


            <a
                href="servicio_nuevo.php"
                class="quick-action"
            >

                <span>
                    🔧
                </span>

                <div>

                    <strong>
                        Nuevo servicio
                    </strong>

                    <small>
                        Registrar servicio
                    </small>

                </div>

            </a>


            <a
                href="ticket_nuevo.php"
                class="quick-action"
            >

                <span>
                    🎫
                </span>

                <div>

                    <strong>
                        Nuevo ticket
                    </strong>

                    <small>
                        Crear ticket
                    </small>

                </div>

            </a>


            <a
                href="inventario_nuevo.php"
                class="quick-action"
            >

                <span>
                    📦
                </span>

                <div>

                    <strong>
                        Nuevo producto
                    </strong>

                    <small>
                        Agregar al inventario
                    </small>

                </div>

            </a>


            <a
                href="garantia_nuevo.php"
                class="quick-action"
            >

                <span>
                    🛡️
                </span>

                <div>

                    <strong>
                        Nueva garantía
                    </strong>

                    <small>
                        Registrar garantía
                    </small>

                </div>

            </a>


        </div>

    </section>


    <!-- =====================================================
         CONTENIDO PRINCIPAL
    ====================================================== -->

    <section class="dashboard-columns">


        <!-- =================================================
             SERVICIOS
        ================================================== -->

        <div class="dashboard-card">

            <div class="card-header">

                <div>

                    <h3>
                        Servicios recientes
                    </h3>

                    <p>
                        Últimos servicios registrados
                    </p>

                </div>

                <a href="servicios.php">
                    Ver todos
                </a>

            </div>


            <div class="table-wrapper">


                <?php if (!empty($serviciosRecientes)): ?>


                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Servicio
                                </th>

                                <th>
                                    Cliente
                                </th>

                                <th>
                                    Equipo
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Costo
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach (
                                $serviciosRecientes
                                as $servicio
                            ): ?>


                                <tr>


                                    <td>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $servicio["tipo"]
                                            ) ?>
                                        </strong>

                                        <small>

                                        </small>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $servicio["cliente"]
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            trim(
                                                $servicio["equipo_tipo"]
                                                . " "
                                                . $servicio["marca"]
                                                . " "
                                                . $servicio["modelo"]
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <?php

                                        $estadoServicio =
                                            $servicio["estado"];

                                        $claseEstado =
                                            match (
                                                $estadoServicio
                                            ) {

                                                "Recibido" =>
                                                    "status-warning",

                                                "Diagnostico" =>
                                                    "status-info",

                                                "En reparacion" =>
                                                    "status-warning",

                                                "Terminado" =>
                                                    "status-success",

                                                "Entregado" =>
                                                    "status-success",

                                                "Cancelado" =>
                                                    "status-danger",

                                                default =>
                                                    "status-info"

                                            };

                                        ?>


                                        <span
                                            class="status-badge <?= $claseEstado ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $estadoServicio
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        $
                                        <?= number_format(
                                            (float)$servicio["costo"],
                                            2
                                        ) ?>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>

                    </table>


                <?php else: ?>


                    <div class="empty-state">

                        <div>
                            🔧
                        </div>

                        <h4>
                            No hay servicios registrados
                        </h4>

                        <p>
                            Los servicios aparecerán aquí
                            cuando registres el primero.
                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </div>


        <!-- =================================================
             TICKETS
        ================================================== -->

        <div class="dashboard-card">

            <div class="card-header">

                <div>

                    <h3>
                        Tickets recientes
                    </h3>

                    <p>
                        Últimos tickets registrados
                    </p>

                </div>

                <a href="tickets.php">
                    Ver todos
                </a>

            </div>


            <div class="tickets-list">


                <?php if (!empty($ticketsRecientes)): ?>


                    <?php foreach (
                        $ticketsRecientes
                        as $ticket
                    ): ?>


                        <div class="ticket-item">


                            <div class="ticket-number">


                                    $ticket["id"],
                                    4,
                                    "0",
                                    STR_PAD_LEFT
                                ) ?>

                            </div>


                            <div class="ticket-content">

                                <strong>

                                    <?= htmlspecialchars(
                                        $ticket["asunto"]
                                    ) ?>

                                </strong>

                                <span>

                                    <?= htmlspecialchars(
                                        $ticket["cliente"]
                                        ?? "Sin cliente"
                                    ) ?>

                                </span>

                            </div>


                            <?php

                            $prioridad =
                                $ticket["prioridad"];

                            ?>


                            <span
                                class="priority priority-<?= strtolower(
                                    $prioridad
                                ) ?>"
                            >

                                <?= htmlspecialchars(
                                    $prioridad
                                ) ?>

                            </span>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty-state">

                        <div>
                            🎫
                        </div>

                        <h4>
                            No hay tickets
                        </h4>

                        <p>
                            Los tickets nuevos aparecerán aquí.
                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </div>


    </section>


    <!-- =====================================================
         STOCK Y GARANTÍAS
    ====================================================== -->

    <section class="dashboard-columns">


        

        <div class="dashboard-card">

            <div class="card-header">

                <div>

                    <h3>
                        📦 Stock que requiere atención
                    </h3>

                    <p>
                        Productos en nivel mínimo o agotados
                    </p>

                </div>

                <a href="inventario.php">
                    Ver inventario
                </a>

            </div>


            <div class="dashboard-list">


                <?php if (!empty($productosStockBajo)): ?>


                    <?php foreach (
                        $productosStockBajo
                        as $producto
                    ): ?>


                        <div class="dashboard-list-item">

                            <div class="dashboard-list-icon">
                                📦
                            </div>


                            <div class="dashboard-list-content">

                                <strong>

                                    <?= htmlspecialchars(
                                        $producto["nombre"]
                                    ) ?>

                                </strong>

                                <span>

                                    <?= htmlspecialchars(
                                        $producto["categoria"]
                                        ?? "Sin categoría"
                                    ) ?>

                                </span>

                            </div>


                            <?php if (
                                (int)$producto["cantidad"] <= 0
                            ): ?>

                                <span class="status-badge status-danger">
                                    Agotado
                                </span>

                            <?php else: ?>

                                <span class="status-badge status-warning">

                                    <?= (int)$producto["cantidad"] ?>
                                    disponibles

                                </span>

                            <?php endif; ?>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty-state compact">

                        <div>
                            ✅
                        </div>

                        <h4>
                            Inventario en orden
                        </h4>

                        <p>
                            No hay productos con stock bajo.
                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </div>


        

        <div class="dashboard-card">

            <div class="card-header">

                <div>

                    <h3>
                        🛡️ Garantías próximas a vencer
                    </h3>

                    <p>
                        Próximos 30 días
                    </p>

                </div>

                <a href="garantias.php">
                    Ver garantías
                </a>

            </div>


            <div class="dashboard-list">


                <?php if (!empty($garantiasRecientes)): ?>


                    <?php foreach (
                        $garantiasRecientes
                        as $garantia
                    ): ?>


                        <?php

                        $fechaVencimiento =
                            new DateTime(
                                $garantia["fecha_fin"]
                            );

                        $hoy =
                            new DateTime();

                        $diferencia =
                            $hoy->diff(
                                $fechaVencimiento
                            );

                        $diasRestantes =
                            (int)$diferencia->format("%r%a");

                        ?>


                        <div class="dashboard-list-item">

                            <div class="dashboard-list-icon">
                                🛡️
                            </div>


                            <div class="dashboard-list-content">

                                <strong>

                                    <?= htmlspecialchars(
                                        $garantia["cliente"]
                                    ) ?>

                                </strong>

                                <span>

                                    <?= htmlspecialchars(
                                        $garantia["servicio_tipo"]
                                    ) ?>

                                </span>

                            </div>


                            <?php if (
                                $diasRestantes <= 7
                            ): ?>

                                <span
                                    class="status-badge status-danger"
                                >

                                    <?= $diasRestantes ?>
                                    día(s)

                                </span>

                            <?php else: ?>

                                <span
                                    class="status-badge status-warning"
                                >

                                    <?= $diasRestantes ?>
                                    día(s)

                                </span>

                            <?php endif; ?>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="empty-state compact">

                        <div>
                            🛡️
                        </div>

                        <h4>
                            Sin vencimientos próximos
                        </h4>

                        <p>
                            No hay garantías que venzan
                            durante los próximos 30 días.
                        </p>

                    </div>


                <?php endif; ?>


            </div>

        </div>


    </section>


</main>


</div>

</body>

</html>