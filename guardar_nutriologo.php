<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $telefono = $_POST['telefono'];
    $antiguedad = $_POST['antiguedad'];
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];
    $confirmar_contraseña = $_POST['confirmar_contraseña'];

    if ($contraseña !== $confirmar_contraseña) {
        die("Las contraseñas no coinciden.");
    }

    // Hashear la contraseña
    $hash = password_hash($contraseña, PASSWORD_DEFAULT);

    // Manejo de archivos INE y cédula (subir imágenes)
    $ine_nombre = $_FILES['ine']['name'];
    $ine_tmp = $_FILES['ine']['tmp_name'];
    $ine_ruta = "uploads/ine_" . time() . "_" . $ine_nombre;
    move_uploaded_file($ine_tmp, $ine_ruta);

    $cedula_nombre = $_FILES['cedula']['name'];
    $cedula_tmp = $_FILES['cedula']['tmp_name'];
    $cedula_ruta = "uploads/cedula_" . time() . "_" . $cedula_nombre;
    move_uploaded_file($cedula_tmp, $cedula_ruta);

    // Conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    $stmt = $conexion->prepare("INSERT INTO nutriologos (nombre, apellido, fecha_nacimiento, telefono, antiguedad, correo, contrasena, ine, cedula) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssissss", $nombre, $apellido, $fecha_nacimiento, $telefono, $antiguedad, $correo, $hash, $ine_ruta, $cedula_ruta);

    if ($stmt->execute()) {
        // Registro exitoso, redirigir a index.php
        header("Location: index.php");
        exit;
    } else {
        echo "Error al registrar: " . $conexion->error;
    }

    $stmt->close();
    $conexion->close();
}
?>
