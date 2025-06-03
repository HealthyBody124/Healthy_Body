<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$correo = $_POST['correo'];
$contraseña = $_POST['contraseña'];

// Buscar en pacientes
$sql_p = "SELECT * FROM pacientes WHERE correo = ?";
$stmt_p = $conexion->prepare($sql_p);
$stmt_p->bind_param("s", $correo);
$stmt_p->execute();
$result_p = $stmt_p->get_result();

if ($row = $result_p->fetch_assoc()) {
    if (password_verify($contraseña, $row['contraseña'])) {
        $_SESSION['usuario'] = $row;
        $_SESSION['rol'] = 'paciente';
        header("Location: inicio_paciente.php");
        exit;
    }
}

// Buscar en nutriólogos
$sql_n = "SELECT * FROM nutriologos WHERE correo = ?";
$stmt_n = $conexion->prepare($sql_n);
$stmt_n->bind_param("s", $correo);
$stmt_n->execute();
$result_n = $stmt_n->get_result();

if ($row = $result_n->fetch_assoc()) {
    if (password_verify($contraseña, $row['contraseña'])) {
        $_SESSION['usuario'] = $row;
        $_SESSION['rol'] = 'nutriologo';
        header("Location: inicio_nutriologo.php");
        exit;
    }
}

// Si no coincide
echo "<script>
    alert('Correo o contraseña incorrectos.');
    window.location.href='login.php';
</script>";
?>
