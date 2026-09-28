<?php
/*
|--------------------------------------------------------------------------
| INFOFIX - SITIO PÚBLICO V2
|--------------------------------------------------------------------------
| Esta primera versión usa contenido visual estático.
| En el siguiente paso conectaremos los servicios con MySQL.
|--------------------------------------------------------------------------
*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Infofix - Servicio, reparación y soporte técnico para computadoras y laptops."
    >

    <title>Infofix | Servicio y reparación</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >
</head>

<body>

<!-- =========================================================
     HEADER
     ========================================================= -->

<header class="site-header">

    <div class="container header-inner">

        <a href="#inicio" class="brand">

            <img
                src="assets/img/logoinff.png"
                alt="Infofix"
                class="brand-logo"
            >

        </a>


        <nav class="main-nav" id="mainNav">

            <a href="#inicio" class="nav-link active">
                Inicio
            </a>

            <a href="#servicios" class="nav-link">
                Servicios
            </a>

            <a href="#nosotros" class="nav-link">
                Nosotros
            </a>

            <a href="#contacto" class="nav-link">
                Contacto
            </a>

        </nav>


        <a
            href="https://wa.me/528661361257"
            target="_blank"
            rel="noopener noreferrer"
            class="header-whatsapp"
        >
            <svg class="whatsapp-svg" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M20.52 3.48A11.86 11.86 0 0 0 12.07 0C5.52 0 .19 5.33.19 11.88c0 2.09.55 4.13 1.59 5.93L.1 24l6.34-1.66a11.88 11.88 0 0 0 5.63 1.43h.01c6.55 0 11.88-5.33 11.88-11.89 0-3.17-1.23-6.14-3.44-8.4Zm-8.45 18.27h-.01a9.86 9.86 0 0 1-5.03-1.38l-.36-.21-3.76.98 1-3.67-.23-.38a9.88 9.88 0 0 1-1.51-5.21C2.17 6.43 6.61 2 12.08 2c2.65 0 5.14 1.03 7.01 2.91a9.86 9.86 0 0 1 2.9 7.02c0 5.47-4.45 9.92-9.92 9.92Zm5.44-7.43c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.76-1.66-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35Z"/>
</svg>
            <span>WhatsApp</span>
        </a>


        <button
            type="button"
            class="menu-toggle"
            id="menuToggle"
            aria-label="Abrir menú"
            aria-expanded="false"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>

</header>


<main>

<!-- =========================================================
     HERO
     ========================================================= -->

<section class="hero" id="inicio">

    <div class="hero-glow hero-glow-one"></div>
    <div class="hero-glow hero-glow-two"></div>

    <div class="container hero-grid">

        <div class="hero-content">

            <div class="eyebrow">
                <span class="eyebrow-line"></span>
                SERVICIO Y REPARACIÓN
            </div>


            <h1>
                Tu equipo en
                <span>las mejores manos</span>
            </h1>


            <p class="hero-description">
                Mantenimiento, reparación y soporte técnico
                para computadoras, laptops y equipos de cómputo.
            </p>


            <div class="hero-services">

                <div class="hero-service">
                    <span class="icon-circle">
                        ⚙
                    </span>
                    <span>Mantenimiento</span>
                </div>

                <div class="hero-divider"></div>

                <div class="hero-service">
                    <span class="icon-circle">
                        🔧
                    </span>
                    <span>Reparación</span>
                </div>

                <div class="hero-divider"></div>

                <div class="hero-service">
                    <span class="icon-circle">
                        ◉
                    </span>
                    <span>Soporte técnico</span>
                </div>

            </div>


            <a href="#contacto" class="button button-primary">
                Solicitar servicio
                <span>→</span>
            </a>

        </div>


        <div class="hero-visual">

            <div class="hero-photo-placeholder">

                <div class="device-glow"></div>

                <div class="laptop-shape">

                    <div class="laptop-screen">

                        <div class="screen-bar"></div>

                        <div class="screen-content">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                    </div>

                    <div class="laptop-base"></div>

                </div>


                <div class="tool tool-one">⚙</div>
                <div class="tool tool-two">⌁</div>
                <div class="tool tool-three">▣</div>


                <div class="hero-photo-label">
                    <strong>Servicio profesional</strong>
                    <span>Diagnóstico y reparación</span>
                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     SELECTOR DE SERVICIO
     ========================================================= -->

<section class="service-selector">

    <div class="container">

        <div class="section-heading centered">

            <span class="section-kicker">
                ¿QUÉ NECESITAS?
            </span>

            <h2>
                Selecciona el servicio que necesitas
            </h2>

        </div>


        <div class="service-cards">

            <article class="service-card">

                <div class="service-icon">
                    💻
                </div>

                <h3>
                    Mantenimiento
                </h3>

                <p>
                    Limpieza, revisión y optimización de equipos.
                </p>

            </article>


            <article class="service-card">

                <div class="service-icon">
                    🔧
                </div>

                <h3>
                    Reparación
                </h3>

                <p>
                    Diagnóstico y solución de problemas de hardware y software.
                </p>

            </article>


            <article class="service-card">

                <div class="service-icon">
                    ◉
                </div>

                <h3>
                    Software
                </h3>

                <p>
                    Instalación y configuración de sistemas y programas.
                </p>

            </article>


            <article class="service-card">

                <div class="service-icon">
                    SSD
                </div>

                <h3>
                    Mejora de equipos
                </h3>

                <p>
                    Instalación de SSD, ampliación de memoria y más.
                </p>

            </article>

        </div>

    </div>

</section>


<!-- =========================================================
     SERVICIOS
     ========================================================= -->

<section class="services-section" id="servicios">

    <div class="container services-grid">

        <div class="services-copy">

            <span class="section-kicker">
                NUESTROS SERVICIOS
            </span>

            <h2>
                Soluciones a la medida
                <span>de tus necesidades</span>
            </h2>

            <p>
                Contamos con las herramientas adecuadas para ayudarte
                a recuperar, mantener y mejorar tus equipos.
            </p>


            <ul class="check-list">

                <li>
                    <span>✓</span>
                    Mantenimiento preventivo y correctivo
                </li>

                <li>
                    <span>✓</span>
                    Reparación de equipos de cómputo
                </li>

                <li>
                    <span>✓</span>
                    Instalación de sistemas operativos y software
                </li>

                <li>
                    <span>✓</span>
                    Clonación de discos y migración de datos
                </li>

                <li>
                    <span>✓</span>
                    Solución de pantallas azules (BSOD)
                </li>

                <li>
                    <span>✓</span>
                    Soporte remoto y en sitio
                </li>

            </ul>


            <a href="#contacto" class="button button-dark">
                Ver todos los servicios
                <span>→</span>
            </a>

        </div>


        <div class="repair-card">

            <div class="repair-visual">

                <div class="repair-board">

                    <div class="board-chip chip-one"></div>
                    <div class="board-chip chip-two"></div>
                    <div class="board-chip chip-three"></div>
                    <div class="board-chip chip-four"></div>

                    <div class="board-lines"></div>

                    <div class="board-fan">
                        <span></span>
                    </div>

                </div>


                <div class="repair-overlay">

                    <span class="repair-overlay-icon">
                        ⚙
                    </span>

                    <div>
                        <strong>Diagnóstico</strong>
                        <small>Atención profesional</small>
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     NOSOTROS / CONFIANZA
     ========================================================= -->

<section class="trust-section" id="nosotros">

    <div class="trust-background"></div>

    <div class="container">

        <div class="section-heading centered light">

            <span class="section-kicker">
                ¿POR QUÉ ELEGIR INFOFIX?
            </span>

            <h2>
                Tu confianza es lo más importante
            </h2>

        </div>


        <div class="trust-grid">

            <article class="trust-item">

                <div class="trust-icon">
                    ✓
                </div>

                <h3>
                    Atención personalizada
                </h3>

                <p>
                    Escuchamos tu problema y buscamos la solución adecuada.
                </p>

            </article>


            <article class="trust-item">

                <div class="trust-icon">
                    ◈
                </div>

                <h3>
                    Servicio profesional
                </h3>

                <p>
                    Trabajamos con cuidado, orden y responsabilidad.
                </p>

            </article>


            <article class="trust-item">

                <div class="trust-icon">
                    ◷
                </div>

                <h3>
                    Rapidez en el servicio
                </h3>

                <p>
                    Buscamos entregar tu equipo en el menor tiempo posible.
                </p>

            </article>


            <article class="trust-item">

                <div class="trust-icon">
                    ♡
                </div>

                <h3>
                    Precios justos
                </h3>

                <p>
                    Calidad y confianza en cada servicio realizado.
                </p>

            </article>

        </div>

    </div>

</section>



<section class="contact-section" id="contacto">

    <div class="container">

        <div class="contact-card">

            <div class="contact-symbol">
                ◔
            </div>


            <div class="contact-copy">

                <span>
                    ¿Tu equipo presenta algún problema?
                </span>

                <h2>
                    Contáctanos por WhatsApp
                </h2>

                <p>
                    Cuéntanos qué necesitas y te ayudaremos.
                </p>

            </div>


            <a
                href="https://wa.me/528661361257"
                target="_blank"
                rel="noopener noreferrer"
                class="button button-whatsapp"
            >
                <svg class="whatsapp-svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M20.52 3.48A11.86 11.86 0 0 0 12.07 0C5.52 0 .19 5.33.19 11.88c0 2.09.55 4.13 1.59 5.93L.1 24l6.34-1.66a11.88 11.88 0 0 0 5.63 1.43h.01c6.55 0 11.88-5.33 11.88-11.89 0-3.17-1.23-6.14-3.44-8.4Zm-8.45 18.27h-.01a9.86 9.86 0 0 1-5.03-1.38l-.36-.21-3.76.98 1-3.67-.23-.38a9.88 9.88 0 0 1-1.51-5.21C2.17 6.43 6.61 2 12.08 2c2.65 0 5.14 1.03 7.01 2.91a9.86 9.86 0 0 1 2.9 7.02c0 5.47-4.45 9.92-9.92 9.92Zm5.44-7.43c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.76-1.66-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35Z"/>
                </svg>
                Escribir por WhatsApp
                <span>→</span>
            </a>

        </div>

    </div>

</section>

</main>



<footer class="site-footer">

    <div class="container footer-top">

        <a href="#inicio" class="footer-brand">

            <img
                src="assets/img/logoinff.png"
                alt="Infofix"
            >

        </a>


        <div class="footer-info">

            <div class="footer-info-item">

                <span class="footer-info-icon">⌖</span>

                <div>
                    <strong>Monclova, Coahuila</strong>
                    <small>Atención local</small>
                </div>

            </div>


            <div class="footer-info-item">

                <span class="footer-info-icon"></span>

                <div>
                    <strong>866 136 1257</strong>
                    <small>WhatsApp</small>
                </div>

            </div>


            <div class="footer-info-item">

                <span class="footer-info-icon">◷</span>

                <div>
                    <strong>Lun - Sáb</strong>
                    <small>9:00 a.m. - 7:00 p.m.</small>
                </div>

            </div>

        </div>

    </div>


    <div class="container footer-bottom">

        <p>
            © 2026 Infofix | Servicio y reparación
        </p>

        <div class="footer-socials">

            <a href="#" aria-label="Facebook">f</a>
            
            <a href="https://wa.me/528661361257" aria-label="WhatsApp">
                <svg class="whatsapp-svg" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M20.52 3.48A11.86 11.86 0 0 0 12.07 0C5.52 0 .19 5.33.19 11.88c0 2.09.55 4.13 1.59 5.93L.1 24l6.34-1.66a11.88 11.88 0 0 0 5.63 1.43h.01c6.55 0 11.88-5.33 11.88-11.89 0-3.17-1.23-6.14-3.44-8.4Zm-8.45 18.27h-.01a9.86 9.86 0 0 1-5.03-1.38l-.36-.21-3.76.98 1-3.67-.23-.38a9.88 9.88 0 0 1-1.51-5.21C2.17 6.43 6.61 2 12.08 2c2.65 0 5.14 1.03 7.01 2.91a9.86 9.86 0 0 1 2.9 7.02c0 5.47-4.45 9.92-9.92 9.92Zm5.44-7.43c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.76-1.66-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35Z"/>
</svg>
            </a>

        </div>

    </div>

</footer>


<script>
const menuToggle = document.getElementById("menuToggle");
const mainNav = document.getElementById("mainNav");

if (menuToggle && mainNav) {

    menuToggle.addEventListener("click", () => {

        const abierto =
            mainNav.classList.toggle("open");

        menuToggle.classList.toggle(
            "open",
            abierto
        );

        menuToggle.setAttribute(
            "aria-expanded",
            abierto ? "true" : "false"
        );

    });


    document
        .querySelectorAll(".nav-link")
        .forEach(link => {

            link.addEventListener("click", () => {

                mainNav.classList.remove("open");
                menuToggle.classList.remove("open");

                menuToggle.setAttribute(
                    "aria-expanded",
                    "false"
                );

            });

        });

}
</script>

</body>
</html>
