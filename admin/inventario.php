<?php

require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Búsqueda
|--------------------------------------------------------------------------
*/

$buscar = trim($_GET["buscar"] ?? "");


/*
|--------------------------------------------------------------------------
| Listar inventario
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            nombre,
            categoria,
            cantidad,
            stock_minimo,
            precio,
            proveedor,
            fecha_registro

        FROM inventario

        WHERE
            nombre LIKE :buscar
            OR categoria LIKE :buscar
            OR proveedor LIKE :buscar

        ORDER BY id DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":buscar" => "%" . $buscar . "%"
]);

$inventario = $stmt->fetchAll();

$mensaje = $_GET["mensaje"] ?? "";

?>

<?php

$tituloPagina = "Inventario";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="admin-main">

    <div class="admin-topbar">

        <div>

            <h1>Inventario</h1>

            <p>
                Controla las piezas, accesorios y productos disponibles.
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
            Producto agregado al inventario correctamente.
        </div>

    <?php elseif ($mensaje === "actualizado"): ?>

        <div class="alert-success">
            Producto actualizado correctamente.
        </div>

    <?php elseif ($mensaje === "eliminado"): ?>

        <div class="alert-success">
            Producto eliminado del inventario correctamente.
        </div>

    <?php endif; ?>


    <div class="page-card">

        <div class="page-card-header">

            <div>

                <h2>Inventario</h2>

                <p>
                    <?= count($inventario) ?>
                    producto(s) encontrado(s).
                </p>

            </div>


            <a
                href="inventario_nuevo.php"
                class="primary-button"
            >
                + Nuevo producto
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
                        placeholder="Buscar por nombre, categoría o proveedor..."
                    >

                </div>


                <?php if ($buscar !== ""): ?>

                    <a
                        href="inventario.php"
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
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Proveedor</th>
                        <th>Registro</th>
                        <th>Acciones</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (empty($inventario)): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="empty-state"
                            >
                                No hay productos registrados en el inventario.
                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($inventario as $producto): ?>

                            <?php

                            $cantidad =
                                (int)$producto["cantidad"];

                            $stockMinimo =
                                (int)$producto["stock_minimo"];


                            if ($cantidad <= 0) {

                                $stockClase =
                                    "status-danger";

                                $stockTexto =
                                    "Agotado";

                            } elseif (
                                $cantidad <= $stockMinimo
                            ) {

                                $stockClase =
                                    "status-warning";

                                $stockTexto =
                                    "Stock bajo";

                            } else {

                                $stockClase =
                                    "status-success";

                                $stockTexto =
                                    "Disponible";

                            }

                            ?>


                            <tr>


                                

                                <td>



                                </td>


                                

                                <td>

                                    <div class="client-cell">

                                        <div class="client-avatar">
                                            📦
                                        </div>

                                        <div>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $producto["nombre"]
                                                ) ?>
                                            </strong>

                                        </div>

                                    </div>

                                </td>


                                

                                <td>

                                    <?= htmlspecialchars(
                                        $producto["categoria"]
                                        ?? "Sin categoría"
                                    ) ?>

                                </td>


                                

                                <td>

                                    <strong>
                                        <?= $cantidad ?>
                                    </strong>

                                    <br>

                                    <span
                                        class="status-badge <?= $stockClase ?>"
                                    >
                                        <?= $stockTexto ?>
                                    </span>

                                    <br>

                                    <small>
                                        Mínimo:
                                        <?= $stockMinimo ?>
                                    </small>

                                </td>


                                

                                <td>

                                    $
                                    <?= number_format(
                                        (float)$producto["precio"],
                                        2
                                    ) ?>

                                </td>


                                

                                <td>

                                    <?= htmlspecialchars(
                                        $producto["proveedor"]
                                        ?? "Sin proveedor"
                                    ) ?>

                                </td>


                                

                                <td>

                                    <?= date(
                                        "d/m/Y",
                                        strtotime(
                                            $producto["fecha_registro"]
                                        )
                                    ) ?>

                                </td>


                                

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="inventario_editar.php?id=<?= (int)$producto["id"] ?>"
                                            class="action-button edit"
                                            title="Editar"
                                        >
                                            ✏️
                                        </a>


                                        <a
                                            href="inventario_eliminar.php?id=<?= (int)$producto["id"] ?>"
                                            class="action-button delete"
                                            title="Eliminar"
                                            onclick="return confirm('¿Estás seguro de eliminar este producto del inventario?');"
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