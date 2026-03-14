<?php
include 'conexion.php';

$usuario = mysqli_real_escape_string($conexion, $_POST['usuario'] ?? '');
$cedula = mysqli_real_escape_string($conexion, $_POST['cedula'] ?? '');
$email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
$telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($usuario) || empty($password) || empty($cedula)) {
    echo "<script>alert('Nombre, cédula y clave son obligatorios'); window.history.back();</script>";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('El correo electrónico no es válido.'); window.history.back();</script>";
    exit;
}

// 1. Verificar si la cédula ya existe
$checkCedula = mysqli_prepare($conexion, "SELECT id_usuario FROM usuarios WHERE cedula = ?");
mysqli_stmt_bind_param($checkCedula, "s", $cedula);
mysqli_stmt_execute($checkCedula);
mysqli_stmt_store_result($checkCedula);
if (mysqli_stmt_num_rows($checkCedula) > 0) {
    echo "<script>alert('Error: Ya existe un usuario registrado con esta cédula.'); window.history.back();</script>";
    exit;
}
mysqli_stmt_close($checkCedula);

// 2. Verificar si el correo ya existe
$checkEmail = mysqli_prepare($conexion, "SELECT id_usuario FROM usuarios WHERE email = ?");
mysqli_stmt_bind_param($checkEmail, "s", $email);
mysqli_stmt_execute($checkEmail);
mysqli_stmt_store_result($checkEmail);
if (mysqli_stmt_num_rows($checkEmail) > 0) {
    echo "<script>alert('Error: El correo electrónico ya está registrado.'); window.history.back();</script>";
    exit;
}
mysqli_stmt_close($checkEmail);

$passHash = password_hash($password, PASSWORD_DEFAULT);

// 3. Insertar usuario (estandarizando usuario = cedula)
$sql = "INSERT INTO usuarios (nombre, cedula, usuario, email, telefono, password, estado_usuario, id_rol) 
        VALUES (?, ?, ?, ?, ?, ?, 'activo', 2)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ssssss", $usuario, $cedula, $cedula, $email, $telefono, $passHash);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('¡Usuario creado! Inicia sesión ahora.'); window.location.href='login.html';</script>";
} else {
    echo "Error crítico: " . mysqli_error($conexion);
}
mysqli_stmt_close($stmt);
?>