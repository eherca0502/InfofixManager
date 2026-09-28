<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Reportes";

$fechaDesde = $_GET["desde"] ?? date("Y-m-01");
$fechaHasta = $_GET["hasta"] ?? date("Y-m-d");

/*
|--------------------------------------------------------------------------
| Validar fechas
|--------------------------------------------------------------------------
*/

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
    $fechaDesde = date("Y-m-01");
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
    $fechaHasta = date("Y-m-d");
}

/*
|--------------------------------------------------------------------------
| Si el periodo está invertido, intercambiar fechas
|--------------------------------------------------------------------------
*/

if ($fechaDesde > $fechaHasta) {

    $temporal = $fechaDesde;
    $fechaDesde = $fechaHasta;
    $fechaHasta = $temporal;

}

/*
|--------------------------------------------------------------------------
| Resumen general
|--------------------------------------------------------------------------
*/


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM servicios
    WHERE DATE(fecha_recepcion) BETWEEN :desde AND :hasta
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$totalServicios = (int) $stmt->fetchColumn();



$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(costo), 0)
    FROM servicios
    WHERE DATE(fecha_recepcion) BETWEEN :desde AND :hasta
    AND estado <> 'Cancelado'
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$totalIngresos = (float) $stmt->fetchColumn();



$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM tickets
    WHERE DATE(fecha_creacion) BETWEEN :desde AND :hasta
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$totalTickets = (int) $stmt->fetchColumn();



$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM garantias
    WHERE fecha_inicio <= :hasta
    AND fecha_fin >= :desde
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$totalGarantias = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Servicios por estado
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        estado,
        COUNT(*) AS cantidad
    FROM servicios
    WHERE DATE(fecha_recepcion) BETWEEN :desde AND :hasta
    GROUP BY estado
    ORDER BY cantidad DESC
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$serviciosPorEstado = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Tickets por prioridad
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        prioridad,
        COUNT(*) AS cantidad
    FROM tickets
    WHERE DATE(fecha_creacion) BETWEEN :desde AND :hasta
    GROUP BY prioridad
    ORDER BY cantidad DESC
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$ticketsPorPrioridad = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Tickets por estado
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        estado,
        COUNT(*) AS cantidad
    FROM tickets
    WHERE DATE(fecha_creacion) BETWEEN :desde AND :hasta
    GROUP BY estado
    ORDER BY cantidad DESC
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$ticketsPorEstado = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Inventario
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COUNT(*) AS productos,
        COALESCE(SUM(cantidad), 0) AS existencias,
        COALESCE(SUM(cantidad * precio), 0) AS valor_total,
        COALESCE(
            SUM(
                CASE
                    WHEN cantidad <= stock_minimo
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS stock_bajo
    FROM inventario
");

$inventario = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Productos con stock bajo
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        nombre,
        categoria,
        cantidad,
        stock_minimo,
        precio
    FROM inventario
    WHERE cantidad <= stock_minimo
    ORDER BY cantidad ASC, nombre ASC
    LIMIT 10
");

$productosStockBajo = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Garantías
|--------------------------------------------------------------------------
*/


$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM garantias
    WHERE estado = 'Activa'
    AND fecha_fin >= CURDATE()
");

$garantiasActivas = (int) $stmt->fetchColumn();



$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM garantias
    WHERE estado = 'Activa'
    AND fecha_fin < CURDATE()
");

$garantiasVencidas = (int) $stmt->fetchColumn();



$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM garantias
    WHERE estado = 'Activa'
    AND fecha_fin >= CURDATE()
    AND fecha_fin <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
");

$garantiasPorVencer = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Servicios recientes del periodo
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        servicios.id,
        servicios.tipo,
        servicios.estado,
        servicios.costo,
        servicios.fecha_recepcion,
        clientes.nombre AS cliente,
        equipos.tipo AS equipo_tipo,
        equipos.marca AS equipo_marca,
        equipos.modelo AS equipo_modelo
    FROM servicios

    INNER JOIN clientes
        ON servicios.cliente_id = clientes.id

    INNER JOIN equipos
        ON servicios.equipo_id = equipos.id

    WHERE DATE(servicios.fecha_recepcion)
        BETWEEN :desde AND :hasta

    ORDER BY servicios.fecha_recepcion DESC

    LIMIT 10
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$serviciosRecientes = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Técnicos con más servicios
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        COALESCE(usuarios.nombre, 'Sin asignar') AS tecnico,
        COUNT(servicios.id) AS cantidad,
        COALESCE(SUM(servicios.costo), 0) AS ingresos
    FROM servicios

    LEFT JOIN usuarios
        ON servicios.tecnico_id = usuarios.id

    WHERE DATE(servicios.fecha_recepcion)
        BETWEEN :desde AND :hasta

    AND servicios.estado <> 'Cancelado'

    GROUP BY servicios.tecnico_id, usuarios.nombre

    ORDER BY cantidad DESC

    LIMIT 5
");

$stmt->execute([
    ":desde" => $fechaDesde,
    ":hasta" => $fechaHasta
]);

$serviciosPorTecnico = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Formato de moneda
|--------------------------------------------------------------------------
*/

function formatoMoneda($cantidad)
{
    return "$" . number_format(
        (float) $cantidad,
        2,
        ".",
        ","
    );
}


/*
|--------------------------------------------------------------------------
| Porcentaje
|--------------------------------------------------------------------------
*/

function porcentaje($cantidad, $total)
{
    if ($total <= 0) {
        return 0;
    }

    return round(
        ($cantidad / $total) * 100
    );
}


/*
|--------------------------------------------------------------------------
| Clases visuales
|--------------------------------------------------------------------------
*/

function claseEstadoServicio($estado)
{
    switch ($estado) {

        case "Entregado":
            return "status-success";

        case "Terminado":
            return "status-success";

        case "En reparacion":
            return "status-warning";

        case "Diagnostico":
            return "status-info";

        case "Cancelado":
            return "status-danger";

        default:
            return "status-info";
    }
}


function clasePrioridad($prioridad)
{
    switch ($prioridad) {

        case "Critica":
            return "status-danger";

        case "Alta":
            return "status-danger";

        case "Media":
            return "status-warning";

        default:
            return "status-info";
    }
}


include "../includes/admin_header.php";
include "../includes/admin_sidebar.php";

?>

<main class="admin-main">

    <!-- =====================================================
         ENCABEZADO
    ====================================================== -->

    <div class="admin-topbar">

        <div>

            <div class="breadcrumb">
                Infofix Manager / Reportes
            </div>

            <h1 class="page-title">
                Reportes
            </h1>

            <p class="page-subtitle">
                Consulta el rendimiento y actividad de Infofix Manager.
            </p>

        </div>

        <div class="topbar-user">

            <div class="topbar-user-info">

                <strong>
                    <?= htmlspecialchars($nombreUsuario) ?>
                </strong>

                <span>
                    <?= htmlspecialchars($rolUsuario) ?>
                </span>

            </div>

            <div class="topbar-avatar">
                <?= strtoupper(substr($nombreUsuario, 0, 1)) ?>
            </div>

        </div>

    </div>


    <!-- =====================================================
         FILTRO DE FECHAS
    ====================================================== -->

    <section class="report-filter-card">

        <div class="report-filter-header">

            <div>

                <h2>
                    Periodo del reporte
                </h2>

                <p>
                    Selecciona las fechas que deseas analizar.
                </p>

            </div>

            <div class="report-filter-icon">
                📅
            </div>

        </div>


        <form
            method="GET"
            action="reportes.php"
            class="report-filter-form"
        >

            <div class="report-date-field">

                <label for="desde">
                    Desde
                </label>

                <input
                    type="date"
                    id="desde"
                    name="desde"
                    value="<?= htmlspecialchars($fechaDesde) ?>"
                >

            </div>


            <div class="report-date-field">

                <label for="hasta">
                    Hasta
                </label>

                <input
                    type="date"
                    id="hasta"
                    name="hasta"
                    value="<?= htmlspecialchars($fechaHasta) ?>"
                >

            </div>


            <button
                type="submit"
                class="primary-button"
            >
                📊 Generar reporte
            </button>


            <a
                href="reportes.php"
                class="secondary-button"
            >
                Restablecer
            </a>

        </form>

    </section>


    <!-- =====================================================
         TARJETAS PRINCIPALES
    ====================================================== -->

    <div class="report-main-stats">

        <div class="report-stat-card">

            <div class="report-stat-icon blue">
                🔧
            </div>

            <div>

                <span>
                    Servicios
                </span>

                <strong>
                    <?= number_format($totalServicios) ?>
                </strong>

                <small>
                    En el periodo seleccionado
                </small>

            </div>

        </div>


        <div class="report-stat-card">

            <div class="report-stat-icon green">
                $
            </div>

            <div>

                <span>
                    Ingresos
                </span>

                <strong>
                    <?= formatoMoneda($totalIngresos) ?>
                </strong>

                <small>
                    Servicios no cancelados
                </small>

            </div>

        </div>


        <div class="report-stat-card">

            <div class="report-stat-icon orange">
                🎫
            </div>

            <div>

                <span>
                    Tickets
                </span>

                <strong>
                    <?= number_format($totalTickets) ?>
                </strong>

                <small>
                    Creados en el periodo
                </small>

            </div>

        </div>


        <div class="report-stat-card">

            <div class="report-stat-icon purple">
                🛡️
            </div>

            <div>

                <span>
                    Garantías
                </span>

                <strong>
                    <?= number_format($totalGarantias) ?>
                </strong>

                <small>
                    Vigentes dentro del periodo
                </small>

            </div>

        </div>

    </div>


    <!-- =====================================================
         SERVICIOS / TICKETS
    ====================================================== -->

    <div class="report-grid-two">


        

        <section class="page-card">

            <div class="page-card-header">

                <div>

                    <h2>
                        Servicios por estado
                    </h2>

                    <p>
                        Distribución de los servicios registrados.
                    </p>

                </div>

                <a
                    href="servicios.php"
                    class="secondary-button"
                >
                    Ver servicios
                </a>

            </div>


            <div class="report-breakdown">

                <?php if (count($serviciosPorEstado) > 0): ?>

                    <?php foreach ($serviciosPorEstado as $fila): ?>

                        <?php
                        $porcentajeEstado = porcentaje(
                            $fila["cantidad"],
                            $totalServicios
                        );
                        ?>

                        <div class="report-breakdown-item">

                            <div class="report-breakdown-top">

                                <span>
                                    <?= htmlspecialchars($fila["estado"]) ?>
                                </span>

                                <strong>
                                    <?= number_format($fila["cantidad"]) ?>
                                </strong>

                            </div>

                            <div class="report-progress">

                                <div
                                    class="report-progress-bar <?= claseEstadoServicio($fila["estado"]) ?>"
                                    style="width: <?= $porcentajeEstado ?>%;"
                                ></div>

                            </div>

                            <small>
                                <?= $porcentajeEstado ?>%
                            </small>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-state compact">

                        <div class="empty-state-icon">
                            🔧
                        </div>

                        <h3>
                            Sin servicios
                        </h3>

                        <p>
                            No existen servicios en este periodo.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>


        

        <section class="page-card">

            <div class="page-card-header">

                <div>

                    <h2>
                        Tickets por prioridad
                    </h2>

                    <p>
                        Distribución de prioridades.
                    </p>

                </div>

                <a
                    href="tickets.php"
                    class="secondary-button"
                >
                    Ver tickets
                </a>

            </div>


            <div class="report-breakdown">

                <?php if (count($ticketsPorPrioridad) > 0): ?>

                    <?php foreach ($ticketsPorPrioridad as $fila): ?>

                        <?php
                        $porcentajePrioridad = porcentaje(
                            $fila["cantidad"],
                            $totalTickets
                        );
                        ?>

                        <div class="report-breakdown-item">

                            <div class="report-breakdown-top">

                                <span>
                                    <?= htmlspecialchars($fila["prioridad"]) ?>
                                </span>

                                <strong>
                                    <?= number_format($fila["cantidad"]) ?>
                                </strong>

                            </div>

                            <div class="report-progress">

                                <div
                                    class="report-progress-bar <?= clasePrioridad($fila["prioridad"]) ?>"
                                    style="width: <?= $porcentajePrioridad ?>%;"
                                ></div>

                            </div>

                            <small>
                                <?= $porcentajePrioridad ?>%
                            </small>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-state compact">

                        <div class="empty-state-icon">
                            🎫
                        </div>

                        <h3>
                            Sin tickets
                        </h3>

                        <p>
                            No existen tickets en este periodo.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    </div>


    <!-- =====================================================
         TICKETS POR ESTADO / GARANTÍAS
    ====================================================== -->

    <div class="report-grid-two">


        

        <section class="page-card">

            <div class="page-card-header">

                <div>

                    <h2>
                        Tickets por estado
                    </h2>

                    <p>
                        Situación actual de los tickets.
                    </p>

                </div>

            </div>


            <div class="report-simple-list">

                <?php if (count($ticketsPorEstado) > 0): ?>

                    <?php foreach ($ticketsPorEstado as $fila): ?>

                        <div class="report-simple-item">

                            <div class="report-simple-icon">
                                🎫
                            </div>

                            <div class="report-simple-content">

                                <strong>
                                    <?= htmlspecialchars($fila["estado"]) ?>
                                </strong>

                                <span>
                                    Tickets registrados
                                </span>

                            </div>

                            <div class="report-simple-number">
                                <?= number_format($fila["cantidad"]) ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-state compact">

                        <div class="empty-state-icon">
                            🎫
                        </div>

                        <h3>
                            Sin información
                        </h3>

                        <p>
                            No hay tickets registrados.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>


        

        <section class="page-card">

            <div class="page-card-header">

                <div>

                    <h2>
                        Estado de garantías
                    </h2>

                    <p>
                        Situación general de las garantías.
                    </p>

                </div>

                <a
                    href="garantias.php"
                    class="secondary-button"
                >
                    Ver garantías
                </a>

            </div>


            <div class="guarantee-report-grid">

                <div class="guarantee-report-item success">

                    <span>
                        Activas
                    </span>

                    <strong>
                        <?= number_format($garantiasActivas) ?>
                    </strong>

                </div>


                <div class="guarantee-report-item warning">

                    <span>
                        Por vencer
                    </span>

                    <strong>
                        <?= number_format($garantiasPorVencer) ?>
                    </strong>

                </div>


                <div class="guarantee-report-item danger">

                    <span>
                        Vencidas
                    </span>

                    <strong>
                        <?= number_format($garantiasVencidas) ?>
                    </strong>

                </div>

            </div>

        </section>

    </div>


    <!-- =====================================================
         INVENTARIO
    ====================================================== -->

    <section class="page-card report-inventory-card">

        <div class="page-card-header">

            <div>

                <h2>
                    Inventario
                </h2>

                <p>
                    Resumen del inventario actual.
                </p>

            </div>

            <a
                href="inventario.php"
                class="secondary-button"
            >
                Ver inventario
            </a>

        </div>


        <div class="inventory-report-stats">

            <div class="inventory-report-stat">

                <span>
                    Productos
                </span>

                <strong>
                    <?= number_format((int) $inventario["productos"]) ?>
                </strong>

            </div>


            <div class="inventory-report-stat">

                <span>
                    Existencias
                </span>

                <strong>
                    <?= number_format((int) $inventario["existencias"]) ?>
                </strong>

            </div>


            <div class="inventory-report-stat">

                <span>
                    Valor del inventario
                </span>

                <strong>
                    <?= formatoMoneda($inventario["valor_total"]) ?>
                </strong>

            </div>


            <div class="inventory-report-stat warning">

                <span>
                    Stock bajo
                </span>

                <strong>
                    <?= number_format((int) $inventario["stock_bajo"]) ?>
                </strong>

            </div>

        </div>

    </section>


    <!-- =====================================================
         STOCK BAJO / TÉCNICOS
    ====================================================== -->

    <div class="report-grid-two">


        

        <section class="page-card">

            <div class="page-card-header">

                <div>

                    <h2>
                        Productos con stock bajo
                    </h2>

                    <p>
                        Productos que requieren atención.
                    </p>

                </div>

            </div>


            <?php if (count($productosStockBajo) > 0): ?>

                <div class="report-table-container">

                    <table class="report-table">

                        <thead>

                            <tr>

                                <th>
                                    Producto
                                </th>

                                <th>
                                    Categoría
                                </th>

                                <th>
                                    Existencias
                                </th>

                                <th>
                                    Mínimo
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($productosStockBajo as $producto): ?>

                                <tr>

                                    <td>

                                        <strong>
                                            <?= htmlspecialchars($producto["nombre"]) ?>
                                        </strong>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $producto["categoria"] ?: "Sin categoría"
                                        ) ?>
                                    </td>

                                    <td>

                                        <?php if ((int) $producto["cantidad"] <= 0): ?>

                                            <span class="status-badge status-danger">
                                                Agotado
                                            </span>

                                        <?php else: ?>

                                            <span class="status-badge status-warning">
                                                <?= number_format(
                                                    (int) $producto["cantidad"]
                                                ) ?>
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= number_format(
                                            (int) $producto["stock_minimo"]
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state compact">

                    <div class="empty-state-icon">
                        📦
                    </div>

                    <h3>
                        Inventario en orden
                    </h3>

                    <p>
                        No hay productos con stock bajo.
                    </p>

                </div>

            <?php endif; ?>

        </section>


        

        <section class="page-card">

            <div class="page-card-header">

                <div>

                    <h2>
                        Servicios por técnico
                    </h2>

                    <p>
                        Técnicos con mayor cantidad de servicios.
                    </p>

                </div>

            </div>


            <?php if (count($serviciosPorTecnico) > 0): ?>

                <div class="report-technician-list">

                    <?php
                    $posicion = 1;
                    ?>

                    <?php foreach ($serviciosPorTecnico as $tecnico): ?>

                        <div class="report-technician-item">

                            <div class="report-technician-position">
                                <?= $posicion ?>
                            </div>

                            <div class="report-technician-info">

                                <strong>
                                    <?= htmlspecialchars($tecnico["tecnico"]) ?>
                                </strong>

                                <span>
                                    <?= number_format(
                                        (int) $tecnico["cantidad"]
                                    ) ?>
                                    servicio(s)
                                </span>

                            </div>

                            <div class="report-technician-income">

                                <strong>
                                    <?= formatoMoneda(
                                        $tecnico["ingresos"]
                                    ) ?>
                                </strong>

                            </div>

                        </div>

                        <?php
                        $posicion++;
                        ?>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state compact">

                    <div class="empty-state-icon">
                        👨‍🔧
                    </div>

                    <h3>
                        Sin información
                    </h3>

                    <p>
                        No hay servicios asignados a técnicos.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </div>


    <!-- =====================================================
         SERVICIOS RECIENTES
    ====================================================== -->

    <section class="page-card">

        <div class="page-card-header">

            <div>

                <h2>
                    Servicios del periodo
                </h2>

                <p>
                    Los últimos servicios registrados.
                </p>

            </div>

            <a
                href="servicios.php"
                class="secondary-button"
            >
                Ver todos
            </a>

        </div>


        <?php if (count($serviciosRecientes) > 0): ?>

            <div class="report-table-container">

                <table class="report-table">

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

                            <th>
                                Fecha
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($serviciosRecientes as $servicio): ?>

                            <tr>

                                <td>

                                    <div class="report-service-cell">

                                        <div class="report-service-icon">
                                            🔧
                                        </div>

                                        <div>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $servicio["tipo"]
                                                ) ?>
                                            </strong>

                                            <small>

                                            </small>

                                        </div>

                                    </div>

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
                                            . $servicio["equipo_marca"]
                                            . " "
                                            . $servicio["equipo_modelo"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <span
                                        class="status-badge <?= claseEstadoServicio(
                                            $servicio["estado"]
                                        ) ?>"
                                    >
                                        <?= htmlspecialchars(
                                            $servicio["estado"]
                                        ) ?>
                                    </span>

                                </td>


                                <td>

                                    <strong class="report-money">
                                        <?= formatoMoneda(
                                            $servicio["costo"]
                                        ) ?>
                                    </strong>

                                </td>


                                <td>

                                    <span class="report-date">
                                        <?= date(
                                            "d/m/Y H:i",
                                            strtotime(
                                                $servicio["fecha_recepcion"]
                                            )
                                        ) ?>
                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty-state compact">

                <div class="empty-state-icon">
                    🔧
                </div>

                <h3>
                    Sin servicios
                </h3>

                <p>
                    No existen servicios registrados en el periodo seleccionado.
                </p>

            </div>

        <?php endif; ?>

    </section>

</main>

</div>

</body>
</html>