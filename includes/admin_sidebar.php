<?php


if (!isset($_SESSION)) {
    session_start();
}

$nombreUsuario = $_SESSION["usuario_nombre"] ?? "Usuario";
$rolUsuario = $_SESSION["usuario_rol"] ?? "";



$paginaActual = basename($_SERVER["PHP_SELF"]);




$moduloActivo = "";




if ($paginaActual === "dashboard.php") {

    $moduloActivo = "dashboard";

}




elseif (
    in_array(
        $paginaActual,
        [
            "clientes.php",
            "cliente_nuevo.php",
            "cliente_editar.php",
            "cliente_eliminar.php"
        ],
        true
    )
) {

    $moduloActivo = "clientes";

}



elseif (
    in_array(
        $paginaActual,
        [
            "equipos.php",
            "equipo_nuevo.php",
            "equipo_editar.php",
            "equipo_eliminar.php"
        ],
        true
    )
) {

    $moduloActivo = "equipos";

}



elseif (
    in_array(
        $paginaActual,
        [
            "servicios.php",
            "servicio_nuevo.php",
            "servicio_editar.php",
            "servicio_eliminar.php"
        ],
        true
    )
) {

    $moduloActivo = "servicios";

}


elseif (
    in_array(
        $paginaActual,
        [
            "tickets.php",
            "ticket_nuevo.php",
            "ticket_editar.php",
            "ticket_eliminar.php"
        ],
        true
    )
) {

    $moduloActivo = "tickets";

}




elseif (
    in_array(
        $paginaActual,
        [
            "garantias.php",
            "garantia_nuevo.php",
            "garantia_editar.php",
            "garantia_eliminar.php"
        ],
        true
    )
) {

    $moduloActivo = "garantias";

}




elseif (
    in_array(
        $paginaActual,
        [
            "inventario.php",
            "inventario_nuevo.php",
            "inventario_editar.php",
            "inventario_eliminar.php"
        ],
        true
    )
) {

    $moduloActivo = "inventario";

}



elseif (
    $paginaActual === "reportes.php"
) {

    $moduloActivo = "reportes";

}




elseif (
    in_array(
        $paginaActual,
        [
            "usuarios.php",
            "usuario_nuevo.php",
            "usuario_editar.php",
            "usuario_eliminar.php",
            "usuario_estado.php"
        ],
        true
    )
) {

    $moduloActivo = "usuarios";

}



function menuActivo($modulo)
{
    global $moduloActivo;

    return $moduloActivo === $modulo
        ? "active"
        : "";
}

?>


<aside class="sidebar">


    <div class="sidebar-brand">

        <div class="sidebar-logo">
            IF
        </div>

        <div class="sidebar-brand-text">

            <strong>
                Infofix
            </strong>

            <span>
                Manager
            </span>

        </div>

    </div>



    <nav class="sidebar-nav">



        <div class="nav-section-title">
            PRINCIPAL
        </div>


        <a
            href="dashboard.php"
            class="nav-item <?= menuActivo("dashboard") ?>"
        >

            <span class="nav-icon">
                ⌂
            </span>

            <span>
                Dashboard
            </span>

        </a>



        <div class="nav-section-title">
            GESTIÓN
        </div>


        

        <a
            href="clientes.php"
            class="nav-item <?= menuActivo("clientes") ?>"
        >

            <span class="nav-icon">
                👥
            </span>

            <span>
                Clientes
            </span>

        </a>


        

        <a
            href="equipos.php"
            class="nav-item <?= menuActivo("equipos") ?>"
        >

            <span class="nav-icon">
                💻
            </span>

            <span>
                Equipos
            </span>

        </a>


        

        <a
            href="servicios.php"
            class="nav-item <?= menuActivo("servicios") ?>"
        >

            <span class="nav-icon">
                🔧
            </span>

            <span>
                Servicios
            </span>

        </a>


        

        <a
            href="tickets.php"
            class="nav-item <?= menuActivo("tickets") ?>"
        >

            <span class="nav-icon">
                🎫
            </span>

            <span>
                Tickets
            </span>

        </a>


        

        <a
            href="garantias.php"
            class="nav-item <?= menuActivo("garantias") ?>"
        >

            <span class="nav-icon">
                🛡️
            </span>

            <span>
                Garantías
            </span>

        </a>



        <div class="nav-section-title">
            CONTROL
        </div>


        

        <a
            href="inventario.php"
            class="nav-item <?= menuActivo("inventario") ?>"
        >

            <span class="nav-icon">
                📦
            </span>

            <span>
                Inventario
            </span>

        </a>


        

        <a
            href="reportes.php"
            class="nav-item <?= menuActivo("reportes") ?>"
        >

            <span class="nav-icon">
                📊
            </span>

            <span>
                Reportes
            </span>

        </a>


        <!-- =====================================================
             SISTEMA
        ====================================================== -->

        <?php if ($rolUsuario === "Administrador"): ?>

            <div class="nav-section-title">
                SISTEMA
            </div>


            <a
                href="usuarios.php"
                class="nav-item <?= menuActivo("usuarios") ?>"
            >

                <span class="nav-icon">
                    ⚙️
                </span>

                <span>
                    Configuración
                </span>

            </a>

        <?php endif; ?>


    </nav>



    <div class="sidebar-footer">

        <form
            method="POST"
            action="logout.php"
            class="logout-form"
        >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(obtenerTokenCSRF(), ENT_QUOTES, "UTF-8") ?>"
            >

            <button
                type="submit"
                class="logout-button"
            >

                <span class="nav-icon">
                    🚪
                </span>

                <span>
                    Cerrar sesión
                </span>

            </button>

        </form>

    </div>


</aside>