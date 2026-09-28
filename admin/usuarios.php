<?php

require_once "../includes/auth.php";
require_once "../config/database.php";



requiereAdministrador();





$tituloPagina = "Usuarios";




$busqueda = trim($_GET["busqueda"] ?? "");


/*
|--------------------------------------------------------------------------
| Consulta de usuarios
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        usuario,
        rol,
        activo,
        fecha_creacion
    FROM usuarios
";

$params = [];


if ($busqueda !== "") {

    $sql .= "
        WHERE
            nombre LIKE :busqueda
            OR usuario LIKE :busqueda
            OR rol LIKE :busqueda
    ";

    $params[":busqueda"] = "%" . $busqueda . "%";

}


$sql .= "
    ORDER BY
        activo DESC,
        fecha_creacion DESC
";


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$usuarios = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Estadísticas
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total
    FROM usuarios
");

$totalUsuarios = (int) $stmt->fetch()["total"];


$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total
    FROM usuarios
    WHERE activo = 1
");

$usuariosActivos = (int) $stmt->fetch()["total"];


$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total
    FROM usuarios
    WHERE activo = 0
");

$usuariosInactivos = (int) $stmt->fetch()["total"];


$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total
    FROM usuarios
    WHERE rol = 'Administrador'
    AND activo = 1
");

$administradores = (int) $stmt->fetch()["total"];


/*
|--------------------------------------------------------------------------
| Mensajes
|--------------------------------------------------------------------------
*/

$mensaje = $_GET["mensaje"] ?? "";

?>


<?php require_once "../includes/admin_header.php"; ?>

<?php require_once "../includes/admin_sidebar.php"; ?>


<main class="admin-main">

    <!-- =========================================================
         TOPBAR
    ========================================================== -->

    <div class="admin-topbar">

        <div>

            <h1>Usuarios</h1>

            <p>
                Administra los usuarios y accesos del sistema.
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


    <!-- =========================================================
         MENSAJES
    ========================================================== -->

    <?php if ($mensaje === "creado"): ?>

        <div class="alert-success">
            Usuario creado correctamente.
        </div>

    <?php elseif ($mensaje === "actualizado"): ?>

        <div class="alert-success">
            Usuario actualizado correctamente.
        </div>

    <?php elseif ($mensaje === "activado"): ?>

        <div class="alert-success">
            Usuario activado correctamente.
        </div>

    <?php elseif ($mensaje === "desactivado"): ?>

        <div class="alert-success">
            Usuario desactivado correctamente.
        </div>

    <?php endif; ?>


    <!-- =========================================================
         ESTADÍSTICAS
    ========================================================== -->

    <div class="user-stats-grid">

        <div class="user-stat-card">

            <div class="user-stat-icon">
                👥
            </div>

            <div>

                <span>
                    Total usuarios
                </span>

                <strong>
                    <?= $totalUsuarios ?>
                </strong>

            </div>

        </div>


        <div class="user-stat-card">

            <div class="user-stat-icon">
                🟢
            </div>

            <div>

                <span>
                    Usuarios activos
                </span>

                <strong>
                    <?= $usuariosActivos ?>
                </strong>

            </div>

        </div>


        <div class="user-stat-card">

            <div class="user-stat-icon">
                ⚫
            </div>

            <div>

                <span>
                    Usuarios inactivos
                </span>

                <strong>
                    <?= $usuariosInactivos ?>
                </strong>

            </div>

        </div>


        <div class="user-stat-card">

            <div class="user-stat-icon">
                🛡️
            </div>

            <div>

                <span>
                    Administradores
                </span>

                <strong>
                    <?= $administradores ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- =========================================================
         CONTENIDO
    ========================================================== -->

    <div class="page-card">

        <div class="page-card-header">

            <div>

                <h2>
                    Lista de usuarios
                </h2>

                <p>
                    Gestiona las cuentas que tienen acceso a Infofix Manager.
                </p>

            </div>

            <a
                href="usuario_nuevo.php"
                class="primary-button"
            >
                + Nuevo usuario
            </a>

        </div>


        <!-- =====================================================
             BÚSQUEDA
        ====================================================== -->

        <div class="search-container">

            <form
                method="GET"
                action="usuarios.php"
                class="search-box"
            >

                <span class="search-icon">
                    🔎
                </span>

                <input
                    type="text"
                    name="busqueda"
                    placeholder="Buscar por nombre, usuario o rol..."
                    value="<?= htmlspecialchars($busqueda) ?>"
                >

                <?php if ($busqueda !== ""): ?>

                    <a
                        href="usuarios.php"
                        class="search-clear"
                        title="Limpiar búsqueda"
                    >
                        ✕
                    </a>

                <?php endif; ?>

                <button
                    type="submit"
                    class="secondary-button"
                >
                    Buscar
                </button>

            </form>

        </div>


        <!-- =====================================================
             TABLA
        ====================================================== -->

        <div class="table-container">

            <table class="users-table">

                <thead>

                    <tr>

                        <th>
                            Usuario
                        </th>

                        <th>
                            Nombre
                        </th>

                        <th>
                            Rol
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Fecha de registro
                        </th>

                        <th>
                            Acciones
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <?php if (empty($usuarios)): ?>

                        <tr>

                            <td
                                colspan="6"
                                style="text-align:center; padding:40px;"
                            >

                                <strong>
                                    No se encontraron usuarios.
                                </strong>

                                <br>

                                <span style="color:var(--text-muted);">
                                    Intenta con otra búsqueda.
                                </span>

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($usuarios as $usuario): ?>

                            <tr>

                                <!-- =================================
                                     USUARIO
                                ================================== -->

                                <td>

                                    <div class="user-table-cell">

                                        <div class="user-table-avatar">

                                            <?= strtoupper(
                                                substr(
                                                    $usuario["nombre"],
                                                    0,
                                                    1
                                                )
                                            ) ?>

                                        </div>

                                        <div>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $usuario["usuario"]
                                                ) ?>
                                            </strong>

                                            <small>
                                                ID #<?= (int) $usuario["id"] ?>
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <!-- =================================
                                     NOMBRE
                                ================================== -->

                                <td>

                                    <?= htmlspecialchars(
                                        $usuario["nombre"]
                                    ) ?>

                                </td>


                                <!-- =================================
                                     ROL
                                ================================== -->

                                <td>

                                    <?php if (
                                        $usuario["rol"] === "Administrador"
                                    ): ?>

                                        <span class="role-badge admin">
                                            🛡️ Administrador
                                        </span>

                                    <?php else: ?>

                                        <span class="role-badge technician">
                                            🔧 Técnico
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- =================================
                                     ESTADO
                                ================================== -->

                                <td>

                                    <?php if (
                                        (int) $usuario["activo"] === 1
                                    ): ?>

                                        <span class="status-badge status-success">
                                            Activo
                                        </span>

                                    <?php else: ?>

                                        <span class="status-badge status-danger">
                                            Inactivo
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- =================================
                                     FECHA
                                ================================== -->

                                <td class="user-date">

                                    <?= date(
                                        "d/m/Y H:i",
                                        strtotime(
                                            $usuario["fecha_creacion"]
                                        )
                                    ) ?>

                                </td>


                                <!-- =================================
                                     ACCIONES
                                ================================== -->

                                <td>

                                    <div class="action-buttons">

                                        

                                        <a
                                            href="usuario_editar.php?id=<?= (int) $usuario["id"] ?>"
                                            class="action-button edit"
                                            title="Editar usuario"
                                        >
                                            ✏️
                                        </a>


                                        

                                        <?php if (
                                            (int) $usuario["activo"] === 1
                                        ): ?>

                                            <?php if (
                                                (int) $usuario["id"] !==
                                                (int) $_SESSION["usuario_id"]
                                            ): ?>

                                                <a
                                                    href="usuario_estado.php?id=<?= (int) $usuario["id"] ?>"
                                                    class="action-button delete"
                                                    title="Desactivar usuario"
                                                    onclick="return confirm('¿Deseas desactivar este usuario?');"
                                                >
                                                    🔴
                                                </a>

                                            <?php else: ?>

                                                <span
                                                    class="action-button disabled"
                                                    title="No puedes desactivarte a ti mismo"
                                                >
                                                    🔒
                                                </span>

                                            <?php endif; ?>

                                        <?php else: ?>

                                            <a
                                                href="usuario_estado.php?id=<?= (int) $usuario["id"] ?>"
                                                class="action-button edit"
                                                title="Activar usuario"
                                                onclick="return confirm('¿Deseas activar este usuario?');"
                                            >
                                                🟢
                                            </a>

                                        <?php endif; ?>

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