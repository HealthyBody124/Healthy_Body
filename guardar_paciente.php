<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibe datos
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $confirmar_contraseña = $_POST['confirmar_contraseña'] ?? '';

    // Validar contraseñas iguales
    if ($contraseña !== $confirmar_contraseña) {
        die("Las contraseñas no coinciden.");
    }

    // Encriptar contraseña
    $hash_contraseña = password_hash($contraseña, PASSWORD_DEFAULT);

    // Conectar a BD
    $conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Insertar paciente
    $stmt = $conexion->prepare("INSERT INTO pacientes (nombre, apellido, fecha_nacimiento, telefono, correo, contrasena) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nombre, $apellido, $fecha_nacimiento, $telefono, $correo, $hash_contraseña);

    if ($stmt->execute()) {
        header("Location: login.php"); // o donde quieras redirigir
        exit;
    } else {
        echo "Error al registrar paciente: " . $conexion->error;
    }
}
?>
