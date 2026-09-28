<?php

require_once "../includes/auth.php";
require_once "../config/database.php";

$tituloPagina = "Clientes";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";


/*
|--------------------------------------------------------------------------
| BUSCAR CLIENTES
|--------------------------------------------------------------------------
*/

$busqueda = trim($_GET["buscar"] ?? "");


if ($busqueda !== "") {

    $sql = "
        SELECT *
        FROM clientes
        WHERE nombre LIKE :buscar
           OR telefono LIKE :buscar
           OR email LIKE :buscar
        ORDER BY id DESC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":buscar" => "%" . $busqueda . "%"
    ]);

} else {

    $sql = "
        SELECT *
        FROM clientes
        ORDER BY id DESC
    ";

    $stmt = $pdo->query($sql);

}


$clientes = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| MENSAJES
|--------------------------------------------------------------------------
*/

$mensaje = $_GET["mensaje"] ?? "";

?>

<main class="main-content">


    

    <header class="topbar">

        <div>

            <h1>
                Clientes
            </h1>

            <p>
                Gestiona los clientes de Infofix Manager
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
                    Lista de clientes
                </h2>

                <p>
                    <?= count($clientes) ?>
                    cliente(s) encontrado(s)
                </p>

            </div>


            <a
                href="cliente_nuevo.php"
                class="primary-button"
            >

                + Nuevo cliente

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
                        placeholder="Buscar por nombre, teléfono o correo..."
                        value="<?= htmlspecialchars($busqueda) ?>"
                    >

                </div>


                <?php if ($busqueda !== ""): ?>

                    <a
                        href="clientes.php"
                        class="clear-search"
                    >
                        Limpiar
                    </a>

                <?php endif; ?>

            </form>

        </div>


        

        <?php if ($mensaje === "creado"): ?>

            <div class="alert-success">
                El cliente fue registrado correctamente.
            </div>

        <?php elseif ($mensaje === "actualizado"): ?>

            <div class="alert-success">
                El cliente fue actualizado correctamente.
            </div>

        <?php elseif ($mensaje === "eliminado"): ?>

            <div class="alert-success">
                El cliente fue eliminado correctamente.
            </div>

        <?php endif; ?>


        

        <div class="table-wrapper clients-table">

            <?php if (count($clientes) > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Cliente
                            </th>

                            <th>
                                Teléfono
                            </th>

                            <th>
                                Correo
                            </th>

                            <th>
                                Dirección
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

                        <?php foreach ($clientes as $cliente): ?>

                            <tr>

                                <td>

                                    <div class="client-cell">

                                        <div class="client-avatar">

                                            <?= strtoupper(
                                                substr(
                                                    $cliente["nombre"],
                                                    0,
                                                    1
                                                )
                                            ) ?>

                                        </div>

                                        <div>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $cliente["nombre"]
                                                ) ?>

                                            </strong>

                                            <small>
                                                Cliente #<?= $cliente["id"] ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $cliente["telefono"] ?: "—"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $cliente["email"] ?: "—"
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $cliente["direccion"] ?: "—"
                                    ) ?>

                                </td>


                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime(
                                            $cliente["fecha_registro"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="cliente_editar.php?id=<?= $cliente["id"] ?>"
                                            class="action-button edit"
                                            title="Editar"
                                        >
                                            ✏️
                                        </a>


                                        <a
                                            href="cliente_eliminar.php?id=<?= $cliente["id"] ?>"
                                            class="action-button delete"
                                            title="Eliminar"
                                            onclick="return confirm('¿Seguro que deseas eliminar este cliente?');"
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
                        👥
                    </div>

                    <h4>
                        No hay clientes registrados
                    </h4>

                    <p>
                        Comienza agregando tu primer cliente
                        a Infofix Manager.
                    </p>


                    <a
                        href="cliente_nuevo.php"
                        class="primary-button"
                    >
                        + Agregar cliente
                    </a>

                </div>

            <?php endif; ?>

        </div>


    </section>


</main>


</div>

</body>

</html>