<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['id_rol']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 3)) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo | Barbería Jafeth</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <nav class="admin-nav">
        <h2 id="admin-title" onclick="location.reload()">Barbería Admin</h2>
        <div class="header-actions">
            <span><i class="fas fa-user-shield"></i> <?php echo $_SESSION['nombre_usuario']; ?></span>
            <?php if ($_SESSION['id_rol'] == 3): ?>
                <button onclick="window.location.href='clientes.php'" class="btn-filter btn-grey">
                    <i class="fas fa-user"></i> Vista Cliente
                </button>
            <?php endif; ?>
            <button onclick="cerrarSesion()" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </button>
        </div>
    </nav>

    <header>
        <img src="img/logo-oficial.png" alt="Barbería Jafeth Logo" class="logo-img">
        <h1>Panel de Gestión</h1>
        <div class="nav-buttons-container mt-15">
            <button onclick="mostrarSeccion('citas')" class="btn-filter">
                <i class="fas fa-calendar-alt"></i> Citas
            </button>
            <button onclick="mostrarSeccion('usuarios')" class="btn-filter">
                <i class="fas fa-users"></i> Usuarios
            </button>
            <button onclick="mostrarSeccion('barberos')" class="btn-filter">
                <i class="fas fa-user-tie"></i> Barberos
            </button>
            <button onclick="mostrarSeccion('servicios')" class="btn-filter">
                <i class="fas fa-cut"></i> Servicios
            </button>
        </div>
    </header>

    <main class="admin-main">

        <div id="seccion-citas">

            <section class="admin-filters">
                <label for="filter-date">Buscar por fecha:</label>
                <input type="date" id="filter-date">
                <button onclick="filtrarPorFecha()" class="btn-filter">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <button onclick="cargarTablaCitas()" class="btn-reset" title="Restablecer">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </section>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Servicio</th>
                            <th>Barbero</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla">
                    </tbody>
                </table>
            </div>
        </div>

        <div id="seccion-usuarios" class="seccion-oculta">
            <header class="header-ajustado-wide header-flex">
                <h2>Clientes Registrados</h2>
            </header>

            <div class="user-search-container">
                <label for="filter-cedula"><i class="fas fa-search"></i> Buscar Cliente:</label>
                <input type="text" id="filter-cedula" placeholder="108520456..."
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-usuarios">
                    </tbody>
                </table>
            </div>
        </div>

        <div id="seccion-barberos" class="seccion-oculta">
            <header class="header-ajustado-wide header-flex">
                <h2>Gestión de Barberos</h2>
                <button onclick="abrirModalGestion('barbero')" class="btn-principal btn-auto">
                    <i class="fas fa-plus"></i> Añadir Barbero
                </button>
            </header>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Admin</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-barberos">
                    </tbody>
                </table>
            </div>
        </div>

        <div id="seccion-servicios" class="seccion-oculta">
            <header class="header-ajustado-wide header-flex">
                <h2>Gestión de Servicios</h2>
                <button onclick="abrirModalGestion('servicio')" class="btn-principal btn-auto">
                    <i class="fas fa-plus"></i> Añadir Servicio
                </button>
            </header>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo-tabla-servicios">
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <div id="modal-editar" class="modal centered-modal">
        <div class="modal-content">
            <h3>Editar Detalles de Cita</h3>
            <form id="form-editar">
                <input type="hidden" id="edit-id" name="id">
                <label>Servicio:</label>
                <select id="edit-servicio" name="servicio">
                    <!-- Dinámico -->
                </select>
                <label>Barbero:</label>
                <select id="edit-barbero" name="barbero">
                    <!-- Dinámico -->
                </select>
                <label>Nueva Fecha:</label>
                <input type="date" id="edit-fecha" name="fecha" required>
                <label>Nueva Hora:</label>
                <input type="time" id="edit-hora" name="hora" required>
                <div class="modal-buttons">
                    <button type="submit" class="btn-principal">Guardar Cambios</button>
                    <button type="button" onclick="cerrarModal()" class="btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <div id="toast-container" class="toast-container-fixed"></div>

    <div id="modal-gestion" class="modal centered-modal">
        <div class="modal-content">
            <h3 id="gestion-titulo">Añadir/Editar</h3>
            <form id="form-gestion">
                <input type="hidden" id="gestion-id" name="id">
                <input type="hidden" id="gestion-tipo" name="tipo">
                <input type="hidden" id="gestion-accion" name="accion">

                <div id="campo-nombre">
                    <label>Nombre:</label>
                    <input type="text" id="gestion-nombre" name="nombre" required>
                </div>

                <div id="campo-email-barbero">
                    <label>Cédula:</label>
                    <input type="text" id="gestion-cedula" name="cedula" placeholder="Ej: 108520456"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <label>Email:</label>
                    <input type="email" id="gestion-email" name="email_barbero" placeholder="ejemplo@gmail.com">
                </div>

                <div id="campo-categoria">
                    <label>Categoría:</label>
                    <select id="gestion-categoria" name="categoria" required>
                        <option value="Cabello">Cabello</option>
                        <option value="Barba">Barba</option>
                        <option value="Especiales">Especiales</option>
                    </select>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn-principal">Guardar</button>
                    <button type="button" onclick="cerrarModalGestion()" class="btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-editar-usuario" class="modal centered-modal">
        <div class="modal-content">
            <h3>Gestión de Usuarios</h3>
            <form id="form-editar-usuario">
                <input type="hidden" id="edit-user-id" name="id_usuario">
                <label>Nombre del Cliente:</label>
                <input type="text" id="edit-user-nombre" name="nombre" required>

                <label>Cédula:</label>
                <input type="text" id="edit-user-cedula" name="cedula" placeholder="Solo números"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>

                <label>Correo Electrónico:</label>
                <input type="email" id="edit-user-email" name="email" required>

                <label>Teléfono:</label>
                <input type="tel" id="edit-user-telefono" name="telefono" placeholder="8888 8888" required>

                <label>Nueva Contraseña (dejar en blanco para no cambiar):</label>
                <input type="password" id="edit-user-pass" name="password" placeholder="Mínimo 6 caracteres">

                <div class="modal-buttons">
                    <button type="submit" class="btn-principal">Actualizar Datos</button>
                    <button type="button" onclick="cerrarModalUser()" class="btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <div id="modal-crear-usuario-barbero" class="modal centered-modal">
        <div class="modal-content">
            <h3>Crear Usuario para Barbero</h3>
            <p id="crear-usuario-info" class="mb-15 font-small text-muted"></p>
            <form id="form-crear-usuario-barbero">
                <input type="hidden" id="crear-usuario-id-barbero" name="id_barbero">
                <label>Asignar Contraseña:</label>
                <input type="password" id="crear-usuario-pass" name="password" required
                    placeholder="Mínimo 6 caracteres">
                <div class="modal-buttons">
                    <button type="submit" class="btn-principal">Crear Cuenta</button>
                    <button type="button" onclick="cerrarModalCrearUsuario()" class="btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="main.js"></script>
</body>

</html>