<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Equipos";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";


/*
|--------------------------------------------------------------------------
| BUSCAR EQUIPOS
|--------------------------------------------------------------------------
*/

$busqueda = trim($_GET["buscar"] ?? "");

if ($busqueda !== "") {

    $sql = "
        SELECT
            equipos.*,
            clientes.nombre AS cliente
        FROM equipos

        INNER JOIN clientes
            ON equipos.cliente_id = clientes.id

        WHERE equipos.tipo LIKE :buscar
           OR equipos.marca LIKE :buscar
           OR equipos.modelo LIKE :buscar
           OR equipos.numero_serie LIKE :buscar
           OR clientes.nombre LIKE :buscar

        ORDER BY equipos.id DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":buscar" => "%" . $busqueda . "%"
    ]);

} else {

    $sql = "
        SELECT
            equipos.*,
            clientes.nombre AS cliente
        FROM equipos

        INNER JOIN clientes
            ON equipos.cliente_id = clientes.id

        ORDER BY equipos.id DESC
    ";

    $stmt = $pdo->query($sql);
}

$equipos = $stmt->fetchAll();

$mensaje = $_GET["mensaje"] ?? "";

?>

<main class="main-content">

    <header class="topbar">

        <div>

            <h1>
                Equipos
            </h1>

            <p>
                Gestiona los equipos registrados de tus clientes
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
                    Lista de equipos
                </h2>

                <p>
                    <?= count($equipos) ?>
                    equipo(s) encontrado(s)
                </p>

            </div>

            <a
                href="equipo_nuevo.php"
                class="primary-button"
            >
                + Nuevo equipo
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
                        placeholder="Buscar por cliente, tipo, marca, modelo o número de serie..."
                        value="<?= htmlspecialchars($busqueda) ?>"
                    >

                </div>

                <?php if ($busqueda !== ""): ?>

                    <a
                        href="equipos.php"
                        class="clear-search"
                    >
                        Limpiar
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <?php if ($mensaje === "creado"): ?>

            <div class="alert-success">
                El equipo fue registrado correctamente.
            </div>

        <?php elseif ($mensaje === "actualizado"): ?>

            <div class="alert-success">
                El equipo fue actualizado correctamente.
            </div>

        <?php elseif ($mensaje === "eliminado"): ?>

            <div class="alert-success">
                El equipo fue eliminado correctamente.
            </div>

        <?php endif; ?>


        <div class="table-wrapper clients-table">

            <?php if (count($equipos) > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Equipo
                            </th>

                            <th>
                                Cliente
                            </th>

                            <th>
                                Número de serie
                            </th>

                            <th>
                                Sistema operativo
                            </th>

                            <th>
                                Registro
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($equipos as $equipo): ?>

                            <tr>

                                <td>

                                    <div class="client-cell">

                                        <div class="client-avatar">

                                            <?php

                                            $inicialEquipo =
                                                substr(
                                                    $equipo["tipo"],
                                                    0,
                                                    1
                                                );

                                            echo strtoupper(
                                                $inicialEquipo
                                            );

                                            ?>

                                        </div>

                                        <div>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    trim(
                                                        $equipo["marca"]
                                                        . " "
                                                        . $equipo["modelo"]
                                                    )
                                                    ?: $equipo["tipo"]
                                                ) ?>

                                            </strong>

                                            <small>

                                                <?= htmlspecialchars(
                                                    $equipo["tipo"]
                                                ) ?>

                                                · Equipo #<?= $equipo["id"] ?>

                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $equipo["cliente"]
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $equipo["numero_serie"]
                                        ?: "—"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $equipo["sistema_operativo"]
                                        ?: "—"
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime(
                                            $equipo["fecha_registro"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="equipo_editar.php?id=<?= $equipo["id"] ?>"
                                            class="action-button edit"
                                            title="Editar"
                                        >
                                            ✏️
                                        </a>


                                        <a
                                            href="equipo_eliminar.php?id=<?= $equipo["id"] ?>"
                                            class="action-button delete"
                                            title="Eliminar"
                                            onclick="return confirm('¿Seguro que deseas eliminar este equipo?');"
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
                        💻
                    </div>

                    <h4>
                        No hay equipos registrados
                    </h4>

                    <p>
                        Comienza agregando el primer equipo
                        de uno de tus clientes.
                    </p>

                    <a
                        href="equipo_nuevo.php"
                        class="primary-button"
                    >
                        + Agregar equipo
                    </a>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

</div>

</body>

</html>