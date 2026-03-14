<?php
session_start();
include 'conexion.php'; // Necesario para sacar el teléfono y nombre actual

// Si alguien entra aquí sin loguearse, lo sacamos al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

// Obtener datos actuales del usuario para rellenar el modal
$id_usuario = $_SESSION['id_usuario'];
$sql_user = "SELECT nombre, cedula, email, telefono FROM usuarios WHERE id_usuario = '$id_usuario'";
$res_user = mysqli_query($conexion, $sql_user);
$datos_usuario = mysqli_fetch_assoc($res_user);

$nombre_actual = $datos_usuario['nombre'] ?? '';
$cedula_actual = $datos_usuario['cedula'] ?? '';
$email_actual = $datos_usuario['email'] ?? '';
$telefono_actual = $datos_usuario['telefono'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barberia Jafeth | Clientes</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>


    <header>
        <div class="header-actions">
            <?php if ($_SESSION['id_rol'] == 3): ?>
                <button onclick="window.location.href='admin.php'" class="btn-filter btn-secondary-dark">
                    <i class="fas fa-user-shield"></i> Vista Admin
                </button>
            <?php endif; ?>
            <button onclick="cerrarSesion()" class="btn-logout">
                Cerrar Sesión
            </button>
        </div>
        <img src="img/logo-oficial.png" alt="Barbería Jafeth Logo" class="logo-img">
        <h1>Barbería Jafeth</h1>
        <p>Bienvenido de nuevo, <?php echo $_SESSION['nombre_usuario']; ?></p>
        <button onclick="abrirModalPerfil()" class="btn-editar-perfil">
            <i class="fas fa-user-edit"></i> Editar Mi Perfil
        </button>


    </header>

    <main class="main-wide">
        <section id="reserva">
            <h2>Agenda tu cita</h2>
            <form id="appointment-form" action="guardar_cita.php" method="POST">
                <label>Servicio:</label>
                <select id="servicio" name="servicio" required>
                    <option value="">Cargando servicios...</option>
                </select>

                <label>Selecciona tu Barbero:</label>
                <select id="barbero" name="barbero" onchange="actualizarHorasDisponibles()" required>
                    <option value="">Cargando barberos...</option>
                </select>

                <label>Selecciona una fecha:</label>
                <input type="date" id="fecha-cita" name="fecha" min="<?php echo date('Y-m-d'); ?>"
                    onchange="actualizarHorasDisponibles()" required>

                <label>Hora disponible:</label>
                <select id="hora-cita" name="hora" required>
                    <option value="">Selecciona un día primero...</option>
                </select>

                <button type="submit" class="btn-principal">Agendar Cita</button>
            </form>
        </section>

        <section id="mis-citas" class="table-container mt-2rem">
            <h2>Mis citas del mes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Barbero</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-tabla-cliente">
                    <tr>
                        <td colspan="6" class="text-center">Cargando tus citas...</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <script src="main.js"></script>
    <div id="toast-container" class="toast-container-fixed"></div>

    <!-- MOVED MODAL HERE TO FIX POSITIONING -->
    <div id="modal-perfil" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModalPerfil()">&times;</span>
            <h2>Actualizar Mis Datos</h2>
            <form id="form-perfil">
                <div class="campo">
                    <label><i class="fas fa-user"></i> Nombre Completo</label>
                    <input type="text" id="perfil-nombre" name="nombre"
                        value="<?php echo htmlspecialchars($nombre_actual); ?>" required>
                </div>
                <div class="campo">
                    <label><i class="fas fa-id-card"></i> Cédula</label>
                    <input type="text" id="perfil-cedula" value="<?php echo htmlspecialchars($cedula_actual); ?>"
                        readonly class="readonly-field">
                </div>
                <div class="campo">
                    <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" id="perfil-email" name="email"
                        value="<?php echo htmlspecialchars($email_actual); ?>" required>
                </div>
                <div class="campo">
                    <label><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="tel" id="perfil-telefono" name="telefono"
                        value="<?php echo htmlspecialchars($telefono_actual); ?>" placeholder="88778877">
                </div>
                <div class="campo">
                    <label><i class="fas fa-lock"></i> Cambiar Contraseña (si deja vacío, no se cambia)</label>
                    <input type="password" id="perfil-pass" name="password" placeholder="Mínimo 6 caracteres">
                </div>
                <button type="submit" class="btn-guardar-cambios">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>
</body>

</html>