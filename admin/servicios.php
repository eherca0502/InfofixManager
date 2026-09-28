<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Servicios";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";


/*
|--------------------------------------------------------------------------
| BUSCAR SERVICIOS
|--------------------------------------------------------------------------
*/

$busqueda = trim($_GET["buscar"] ?? "");

if ($busqueda !== "") {

    $sql = "
        SELECT
            servicios.id,
            servicios.tipo,
            servicios.estado,
            servicios.costo,
            servicios.fecha_recepcion,

            clientes.nombre AS cliente,

            equipos.tipo AS equipo_tipo,
            equipos.marca AS equipo_marca,
            equipos.modelo AS equipo_modelo,

            usuarios.nombre AS tecnico

        FROM servicios

        INNER JOIN clientes
            ON servicios.cliente_id = clientes.id

        INNER JOIN equipos
            ON servicios.equipo_id = equipos.id

        LEFT JOIN usuarios
            ON servicios.tecnico_id = usuarios.id

        WHERE servicios.tipo LIKE :buscar
           OR servicios.estado LIKE :buscar
           OR clientes.nombre LIKE :buscar
           OR equipos.marca LIKE :buscar
           OR equipos.modelo LIKE :buscar
           OR usuarios.nombre LIKE :buscar

        ORDER BY servicios.id DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":buscar" => "%" . $busqueda . "%"
    ]);

} else {

    $sql = "
        SELECT
            servicios.id,
            servicios.tipo,
            servicios.estado,
            servicios.costo,
            servicios.fecha_recepcion,

            clientes.nombre AS cliente,

            equipos.tipo AS equipo_tipo,
            equipos.marca AS equipo_marca,
            equipos.modelo AS equipo_modelo,

            usuarios.nombre AS tecnico

        FROM servicios

        INNER JOIN clientes
            ON servicios.cliente_id = clientes.id

        INNER JOIN equipos
            ON servicios.equipo_id = equipos.id

        LEFT JOIN usuarios
            ON servicios.tecnico_id = usuarios.id

        ORDER BY servicios.id DESC
    ";

    $stmt = $pdo->query($sql);
}

$servicios = $stmt->fetchAll();

$mensaje = $_GET["mensaje"] ?? "";

?>

<main class="main-content">

    <header class="topbar">

        <div>

            <h1>
                Servicios
            </h1>

            <p>
                Gestiona las reparaciones y servicios técnicos
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


    <section class="page-card">

        <div class="page-card-header">

            <div>

                <h2>
                    Lista de servicios
                </h2>

                <p>
                    <?= count($servicios) ?>
                    servicio(s) encontrado(s)
                </p>

            </div>


            <a
                href="servicio_nuevo.php"
                class="primary-button"
            >
                + Nuevo servicio
            </a>

        </div>


        <div class="search-container">

            <form method="GET">

                <div class="search-box">

                    <span>
                        🔎
                    </span>

                    <input
                        type="text"
                        name="buscar"
                        placeholder="Buscar por cliente, equipo, servicio o técnico..."
                        value="<?= htmlspecialchars($busqueda) ?>"
                    >

                </div>


                <?php if ($busqueda !== ""): ?>

                    <a
                        href="servicios.php"
                        class="clear-search"
                    >
                        Limpiar
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <?php if ($mensaje === "creado"): ?>

            <div class="alert-success">
                El servicio fue registrado correctamente.
            </div>

        <?php elseif ($mensaje === "actualizado"): ?>

            <div class="alert-success">
                El servicio fue actualizado correctamente.
            </div>

        <?php elseif ($mensaje === "eliminado"): ?>

            <div class="alert-success">
                El servicio fue eliminado correctamente.
            </div>

        <?php endif; ?>


        <div class="table-wrapper clients-table">

            <?php if (count($servicios) > 0): ?>

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
                                Técnico
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Costo
                            </th>

                            <th>
                                Recepción
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($servicios as $servicio): ?>

                            <?php

                            $equipoNombre = trim(
                                $servicio["equipo_marca"]
                                . " "
                                . $servicio["equipo_modelo"]
                            );

                            if ($equipoNombre === "") {

                                $equipoNombre =
                                    $servicio["equipo_tipo"];

                            }

                            ?>


                            <tr>

                                

                                <td>

                                    <div class="client-cell">

                                        <div class="client-avatar">

                                            🔧

                                        </div>

                                        <div>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $servicio["tipo"]
                                                ) ?>

                                            </strong>

                                            <small>

                                                Servicio #<?= $servicio["id"] ?>

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

                                    <strong>

                                        <?= htmlspecialchars(
                                            $equipoNombre
                                        ) ?>

                                    </strong>

                                    <small>

                                        <?= htmlspecialchars(
                                            $servicio["equipo_tipo"]
                                        ) ?>

                                    </small>

                                </td>


                                

                                <td>

                                    <?= htmlspecialchars(
                                        $servicio["tecnico"]
                                        ?? "Sin asignar"
                                    ) ?>

                                </td>


                                

                                <td>

                                    <span
                                        class="status status-<?= strtolower(
                                            str_replace(
                                                " ",
                                                "-",
                                                $servicio["estado"]
                                            )
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $servicio["estado"]
                                        ) ?>

                                    </span>

                                </td>


                                

                                <td>

                                    $<?= number_format(
                                        $servicio["costo"],
                                        2
                                    ) ?>

                                </td>


                                

                                <td>

                                    <?= date(
                                        "d/m/Y H:i",
                                        strtotime(
                                            $servicio["fecha_recepcion"]
                                        )
                                    ) ?>

                                </td>



                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="servicio_editar.php?id=<?= $servicio["id"] ?>"
                                            class="action-button edit"
                                            title="Editar"
                                        >
                                            ✏️
                                        </a>


                                        <a
                                            href="servicio_eliminar.php?id=<?= $servicio["id"] ?>"
                                            class="action-button delete"
                                            title="Eliminar"
                                            onclick="return confirm('¿Seguro que deseas eliminar este servicio?');"
                                        >
                                            🗑️
                                        </a>

                                    </div>

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
                        Comienza registrando el primer servicio
                        técnico.
                    </p>

                    <a
                        href="servicio_nuevo.php"
                        class="primary-button"
                    >
                        + Registrar servicio
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

</div>

</body>

</html>