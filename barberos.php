<?php
session_start();
include 'conexion.php';

// Seguridad: Solo barberos (4) o dev (3)
if (!isset($_SESSION['id_usuario']) || ($_SESSION['id_rol'] != 4 && $_SESSION['id_rol'] != 3)) {
    header("Location: login.html");
    exit();
}

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
    <title>Barbería Jafeth | Panel de Barbero</title>
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
        <h1>Panel de Barbero</h1>
        <p>Bienvenido de nuevo, <?php echo $_SESSION['nombre_usuario']; ?></p>
        <button onclick="abrirModalPerfil()" class="btn-editar-perfil">
            <i class="fas fa-user-edit"></i> Editar Mi Perfil
        </button>
    </header>

    <main class="main-wide">
        <!-- SECCIÓN: AGENDA DEL BARBERO -->
        <section id="agenda-barbero" class="table-container mt-2rem" style="display: block;">
            <h2 class="text-rojo-accent title-padding"><i class="fas fa-cut"></i> Mi Agenda</h2>
            <p class="text-muted mb-15 title-padding">Citas programadas</p>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Servicio</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-tabla-agenda">
                    <tr>
                        <td colspan="7" class="text-center">Cargando tu agenda...</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <script src="main.js"></script>
    <div id="toast-container" class="toast-container-fixed"></div>

    <!-- MODAL PERFIL -->
    <div id="modal-perfil" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModalPerfil()">&times;</span>
            <h2>Actualizar Mis Datos</h2>
            <form id="form-perfil">
                <div class="campo">
                    <label><i class="fas fa-user"></i> Nombre Completo</label>
                    <input type="text" id="perfil-nombre" name="nombre" value="<?php echo htmlspecialchars($nombre_actual); ?>" required>
                </div>
                <div class="campo">
                    <label><i class="fas fa-id-card"></i> Cédula</label>
                    <input type="text" id="perfil-cedula" value="<?php echo htmlspecialchars($cedula_actual); ?>" readonly class="readonly-field">
                </div>
                <div class="campo">
                    <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" id="perfil-email" name="email" value="<?php echo htmlspecialchars($email_actual); ?>" required>
                </div>
                <div class="campo">
                    <label><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="tel" id="perfil-telefono" name="telefono" value="<?php echo htmlspecialchars($telefono_actual); ?>" placeholder="88778877">
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
