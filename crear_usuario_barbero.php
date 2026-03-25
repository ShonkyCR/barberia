<?php
header('Content-Type: application/json');
include 'conexion.php';

$id_barbero = (int) ($_POST['id_barbero'] ?? 0);
$password = $_POST['password'] ?? '';

if ($id_barbero <= 0 || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

// 1. Obtener datos del barbero
$stmt = mysqli_prepare($conexion, "SELECT nombre, cedula, email_barbero FROM barberos WHERE id_barbero = ?");
mysqli_stmt_bind_param($stmt, "i", $id_barbero);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $nombre, $cedula, $email);
if (!mysqli_stmt_fetch($stmt)) {
    echo json_encode(['success' => false, 'message' => 'Barbero no encontrado.']);
    exit;
}
mysqli_stmt_close($stmt);

if (empty($cedula)) {
    echo json_encode(['success' => false, 'message' => 'El barbero no tiene cédula asignada. Edítalo primero.']);
    exit;
}

// 2. Verificar si ya existe un usuario con esa cédula
$stmt_check = mysqli_prepare($conexion, "SELECT id_usuario FROM usuarios WHERE cedula = ?");
mysqli_stmt_bind_param($stmt_check, "s", $cedula);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);
if (mysqli_stmt_num_rows($stmt_check) > 0) {
    echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con la cédula ' . $cedula]);
    exit;
}
mysqli_stmt_close($stmt_check);

// 3. Crear el usuario
$passHash = password_hash($password, PASSWORD_DEFAULT);
// Usamos la cédula como 'usuario' ya que es el estándar actual del proyecto
$sql = "INSERT INTO usuarios (nombre, cedula, usuario, email, password, id_rol, estado_usuario) VALUES (?, ?, ?, ?, ?, 4, 'activo')";
$stmt_ins = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt_ins, "sssss", $nombre, $cedula, $cedula, $email, $passHash);

if (mysqli_stmt_execute($stmt_ins)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . mysqli_stmt_error($stmt_ins)]);
}
mysqli_stmt_close($stmt_ins);
?>