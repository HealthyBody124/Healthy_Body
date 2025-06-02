<?php
session_start();

// Conexión directa a la base de datos (ajusta los datos si es necesario)
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_nutricion"; // <-- cámbialo por el nombre real

$conexion = new mysqli($host, $user, $pass, $db);

// Verifica la conexión
if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    die("Debes iniciar sesión.");
}

// Obtén el id del paciente desde la sesión
$paciente_id = $_SESSION['usuario']['id'];

// Obtén los datos del formulario
$nutriologo_id = $_POST['nutriologo_id'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$hora = $_POST['hora'] ?? null;
$motivo = $_POST['motivo'] ?? '';

// Validación básica
if (empty($nutriologo_id) || empty($fecha) || empty($hora)) {
    die("Faltan datos para agendar la cita.");
}

// Preparar y ejecutar la inserción
$stmt = $conexion->prepare("INSERT INTO citas (paciente_id, nutriologo_id, fecha, hora, estado, motivo) VALUES (?, ?, ?, ?, 'pendiente', ?)");
$stmt->bind_param("iisss", $paciente_id, $nutriologo_id, $fecha, $hora, $motivo);

if ($stmt->execute()) {
    // Puedes redirigir o mostrar una alerta
    header("Location: inicio_paciente.php?cita=agendada");
    exit;
} else {
    echo "❌ Error al agendar cita: " . $stmt->error;
}
?>