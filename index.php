<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Infofix Manager</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <header class="navbar">

        <div class="logo">
            <span class="logo-icon">IF</span>

            <div>
                <h1>Infofix</h1>
                <span>Manager</span>
            </div>
        </div>

        <nav>
            <a href="#inicio">Inicio</a>
            <a href="#servicios">Servicios</a>
            <a href="#nosotros">Nosotros</a>
            <a href="#contacto">Contacto</a>
        </nav>

        <a href="admin/login.php" class="btn-login">
            Iniciar sesión
        </a>

    </header>


    <main>

        <section class="hero" id="inicio">

            <div class="hero-content">

                <span class="badge">
                    SISTEMA DE GESTIÓN
                </span>

                <h2>
                    Gestiona tu negocio
                    <span>de soporte técnico.</span>
                </h2>

                <p>
                    Administra clientes, equipos, servicios, tickets
                    y garantías desde una sola plataforma.
                </p>

                <div class="hero-buttons">

                    <a href="admin/login.php" class="btn-primary">
                        Acceder al sistema
                    </a>

                    <a href="#servicios" class="btn-secondary">
                        Conocer más
                    </a>

                </div>

            </div>


            <div class="hero-card">

                <div class="dashboard-preview">

                    <div class="preview-header">
                        <span>Dashboard</span>
                        <span class="status">● Activo</span>
                    </div>

                    <div class="preview-cards">

                        <div>
                            <small>Clientes</small>
                            <strong>128</strong>
                        </div>

                        <div>
                            <small>Servicios</small>
                            <strong>24</strong>
                        </div>

                        <div>
                            <small>Tickets</small>
                            <strong>8</strong>
                        </div>

                    </div>

                    <div class="preview-chart">

                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>

                    </div>

                </div>

            </div>

        </section>


        <section class="services" id="servicios">

            <div class="section-title">

                <span>SERVICIOS</span>

                <h2>
                    Todo lo que necesitas
                </h2>

                <p>
                    Una plataforma diseñada para organizar
                    y controlar tu negocio.
                </p>

            </div>


            <div class="service-grid">

                <article class="service-card">

                    <div class="service-icon">
                        👥
                    </div>

                    <h3>Clientes</h3>

                    <p>
                        Registra y administra toda la información
                        de tus clientes.
                    </p>

                </article>


                <article class="service-card">

                    <div class="service-icon">
                        💻
                    </div>

                    <h3>Equipos</h3>

                    <p>
                        Mantén un historial de los equipos
                        de cada cliente.
                    </p>

                </article>


                <article class="service-card">

                    <div class="service-icon">
                        🔧
                    </div>

                    <h3>Servicios</h3>

                    <p>
                        Controla reparaciones, mantenimientos
                        y servicios técnicos.
                    </p>

                </article>


                <article class="service-card">

                    <div class="service-icon">
                        🎫
                    </div>

                    <h3>Tickets</h3>

                    <p>
                        Gestiona solicitudes y problemas
                        de soporte.
                    </p>

                </article>

            </div>

        </section>


        <section class="cta" id="contacto">

            <div>

                <span>INFOFIX MANAGER</span>

                <h2>
                    Todo tu negocio,
                    en un solo lugar.
                </h2>

            </div>

            <a href="admin/login.php" class="btn-primary">
                Comenzar
            </a>

        </section>

    </main>


    <footer>

        <p>
            © 2026 Infofix Manager
        </p>

        <p>
            Sistema de gestión para soporte técnico
        </p>

    </footer>


    <script src="assets/js/app.js"></script>

</body>
</html>