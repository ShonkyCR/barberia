<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbería Clásica - Estilo y Tradición</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Oswald:wght@500;700&display=swap"
        rel="stylesheet">
    <!-- Main Styles -->
    <link rel="stylesheet" href="styles.css">
    <!-- Landing Specific Styles -->
    <link rel="stylesheet" href="landing.css">
</head>

<body>

    <!-- Botón WhatsApp -->
    <a href="https://wa.me/tu-numero" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Navegación -->
    <nav class="landing-nav" id="navbar">
        <div class="nav-logo">
            <img src="imagenes/logo-rojoof.png" alt="Logo La Barbería">
            <span>LA BARBERÍA</span>
        </div>
        <div class="nav-links" id="nav-links">
            <a href="#inicio">Inicio</a>
            <a href="#quienes-somos">Nuestra Historia</a>
            <a href="#servicios">Servicios</a>
            <a href="login.html" class="btn-logout">Mi cuenta</a>
        </div>
        <div class="menu-toggle" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="inicio">
        <!-- Slider de fondo -->
        <div class="hero-slider">
            <div class="hero-slide" style="background-image: url('imagenes/carrusel-header.jpg');"></div>
            <div class="hero-slide" style="background-image: url('imagenes/carrusel-header2.jpg');"></div>
            <div class="hero-slide" style="background-image: url('imagenes/carrusel-header3.jpg');"></div>
            <div class="hero-slide" style="background-image: url('imagenes/carrusel-header4.jpg');"></div>
        </div>

        <div class="reveal">
            <h1>CONVIERTE<span>TU ESTILO</span>EN ARTE</h1>
            <p>Donde la tradición se encuentra con la modernidad. Dale a tu imagen el trato que se merece.</p>
            <div class="hero-btns">
                <a href="registro.html" class="btn-hero primary">Agendar Cita</a>
                <a href="#servicios" class="btn-hero secondary">Ver servicios</a>
            </div>
        </div>
    </section>

    <!-- Quiénes Somos -->
    <section id="quienes-somos">
        <div class="about-container">
            <div class="about-text reveal">
                <div class="section-title">
                    <h2>Nuestra Historia</h2>
                </div>
                <h3>Pasión por la barbería</h3>
                <p>En nuestra barbería, cada corte es una obra de arte. Con años de experiencia en el arte de la navaja
                    y las tijeras, nos dedicamos a ofrecer una experiencia única de aseo masculino.</p>
                <p>No solo cortamos cabello; esculpimos identidades. Ven y disfruta de un ambiente relajado, buena
                    música y el mejor servicio de la ciudad.</p>
            </div>
            <div class="about-image reveal">
                <img src="imagenes/quienes-somos.jpg" alt="barbero">
            </div>
        </div>
    </section>

    <!-- Servicios -->
    <section id="servicios" class="services-section">
        <div class="section-title reveal">
            <h2>Nuestros Servicios</h2>
        </div>
        <div class="services-grid">
            <div class="service-card reveal">
                <i class="fas fa-cut"></i>
                <h3>Corte Clásico</h3>
                <p>Corte personalizado adaptado a tu estilo y fisionomía facial.</p>
            </div>
            <div class="service-card reveal">
                <i class="fas fa-user-tie"></i>
                <h3>Barba & Perfilado</h3>
                <p>Diseño y mantenimiento de barba con toalla caliente y navaja.</p>
            </div>
            <div class="service-card reveal">
                <i class="fas fa-chair"></i>
                <h3>Tratamientos Capilares</h3>
                <p>Cuidado profundo para tu cabello y cuero cabelludo.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content reveal">
            <div class="footer-logo">LA BARBERÍA</div>

            <div class="footer-info">
                <h4>Contacto</h4>
                <p>Buenos Aires, Puntarenas</p>
                <p>Horario: Lun - Sáb: 9:00 a.m. - 6:00 p.m.</p>
                <p>Tel: 8721-3942</p>
            </div>

            <div class="social-links">
                <a href="#"><i class="fab fa-instagram"></i> <span>Instagram</span></a>
                <a href="#"><i class="fab fa-facebook"></i> <span>Facebook</span></a>
                <a href="#"><i class="fab fa-whatsapp"></i> <span>WhatsApp</span></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 La Barbería. Todos los derechos reservados. Estilo y Tradición desde siempre.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="landing.js"></script>
</body>

</html>