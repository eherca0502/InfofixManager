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

    header("Location: inventario.php");

    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener producto
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            nombre,
            categoria,
            cantidad,
            stock_minimo,
            precio,
            proveedor

        FROM inventario

        WHERE id = :id

        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

$producto = $stmt->fetch();


if (!$producto) {

    header(
        "Location: inventario.php?mensaje=no_encontrado"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$errores = [];

$nombre = $producto["nombre"];

$categoria =
    $producto["categoria"] ?? "";

$cantidad =
    $producto["cantidad"];

$stock_minimo =
    $producto["stock_minimo"];

$precio =
    $producto["precio"];

$proveedor =
    $producto["proveedor"] ?? "";


/*
|--------------------------------------------------------------------------
| Procesar formulario
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verificarTokenCSRF();

    $nombre = trim(
        $_POST["nombre"] ?? ""
    );

    $categoria = trim(
        $_POST["categoria"] ?? ""
    );

    $cantidad = filter_input(
        INPUT_POST,
        "cantidad",
        FILTER_VALIDATE_INT
    );

    $stock_minimo = filter_input(
        INPUT_POST,
        "stock_minimo",
        FILTER_VALIDATE_INT
    );

    $precio = trim(
        $_POST["precio"] ?? "0"
    );

    $proveedor = trim(
        $_POST["proveedor"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | Validar nombre
    |--------------------------------------------------------------------------
    */

    if ($nombre === "") {

        $errores[] =
            "El nombre del producto es obligatorio.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validar cantidad
    |--------------------------------------------------------------------------
    */

    if (
        $cantidad === false
        ||
        $cantidad === null
        ||
        $cantidad < 0
    ) {

        $errores[] =
            "La cantidad debe ser un número entero mayor o igual a 0.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validar stock mínimo
    |--------------------------------------------------------------------------
    */

    if (
        $stock_minimo === false
        ||
        $stock_minimo === null
        ||
        $stock_minimo < 0
    ) {

        $errores[] =
            "El stock mínimo debe ser un número entero mayor o igual a 0.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validar precio
    |--------------------------------------------------------------------------
    */

    if (
        $precio === ""
        ||
        !is_numeric($precio)
        ||
        (float)$precio < 0
    ) {

        $errores[] =
            "El precio debe ser un número mayor o igual a 0.";

    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar
    |--------------------------------------------------------------------------
    */

    if (empty($errores)) {

        $sql = "UPDATE inventario

                SET
                    nombre = :nombre,
                    categoria = :categoria,
                    cantidad = :cantidad,
                    stock_minimo = :stock_minimo,
                    precio = :precio,
                    proveedor = :proveedor

                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            ":nombre" => $nombre,

            ":categoria" =>
                $categoria !== ""
                    ? $categoria
                    : null,

            ":cantidad" => $cantidad,

            ":stock_minimo" => $stock_minimo,

            ":precio" => (float)$precio,

            ":proveedor" =>
                $proveedor !== ""
                    ? $proveedor
                    : null,

            ":id" => $id

        ]);


        header(
            "Location: inventario.php?mensaje=actualizado"
        );

        exit;

    }

}

?>

<?php

$tituloPagina = "Editar producto";

require_once "../includes/admin_header.php";
require_once "../includes/admin_sidebar.php";

?>

<main class="admin-main">

    <div class="admin-topbar">

        <div>

            <h1>Editar producto</h1>

            <p>
                Modifica la información del producto.
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
                No se pudo actualizar el producto:
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

                <h2>Información del producto</h2>

                <p>
                    Actualiza los datos del artículo.
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


                <div class="form-field">

                    <label for="nombre">
                        Nombre del producto *
                    </label>

                    <input
                        type="text"
                        name="nombre"
                        id="nombre"
                        value="<?= htmlspecialchars($nombre) ?>"
                        maxlength="150"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="categoria">
                        Categoría
                    </label>

                    <input
                        type="text"
                        name="categoria"
                        id="categoria"
                        value="<?= htmlspecialchars($categoria) ?>"
                        maxlength="100"
                    >

                </div>


                <div class="form-field">

                    <label for="cantidad">
                        Cantidad
                    </label>

                    <input
                        type="number"
                        name="cantidad"
                        id="cantidad"
                        value="<?= htmlspecialchars((string)$cantidad) ?>"
                        min="0"
                        step="1"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="stock_minimo">
                        Stock mínimo
                    </label>

                    <input
                        type="number"
                        name="stock_minimo"
                        id="stock_minimo"
                        value="<?= htmlspecialchars((string)$stock_minimo) ?>"
                        min="0"
                        step="1"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="precio">
                        Precio
                    </label>

                    <input
                        type="number"
                        name="precio"
                        id="precio"
                        value="<?= htmlspecialchars((string)$precio) ?>"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>


                <div class="form-field">

                    <label for="proveedor">
                        Proveedor
                    </label>

                    <input
                        type="text"
                        name="proveedor"
                        id="proveedor"
                        value="<?= htmlspecialchars($proveedor) ?>"
                        maxlength="150"
                    >

                </div>


            </div>


            <div class="form-actions">

                <a
                    href="inventario.php"
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