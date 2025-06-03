<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'paciente') {
    header("Location: login.php");
    exit;
}

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");

$nombre_tarjeta = $_POST['nombre_tarjeta'] ?? '';
$numero_tarjeta = $_POST['numero_tarjeta'] ?? '';
$fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Nuevas variables necesarias:
$fecha = $_POST['fecha'] ?? '';
$hora = $_POST['hora'] ?? '';
$nutriologo_id = (int)($_POST['nutriologo_id'] ?? 0);
$paciente_id = $_SESSION['usuario']['id'];

// Validar que todos los campos están presentes
if (empty($nombre_tarjeta) || empty($numero_tarjeta) || empty($fecha_vencimiento) || empty($cvv) || empty($fecha) || empty($hora) || empty($nutriologo_id)) {
    echo "Todos los campos del formulario son obligatorios.";
    exit;
}

// Simulación del pago (aquí deberías usar una pasarela real en producción)
$pago_realizado = true;

if ($pago_realizado) {
    // Insertar la cita en la base de datos
    $stmt = $conexion->prepare("INSERT INTO citas (paciente_id, nutriologo_id, fecha, hora, estado) VALUES (?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param("iiss", $paciente_id, $nutriologo_id, $fecha, $hora);
    if ($stmt->execute()) {
        // Notificar al nutriólogo
        $stmt_notif = $conexion->prepare("INSERT INTO notificaciones (nutriologo_id, mensaje, leido) VALUES (?, ?, 0)");
        $mensaje = "Nueva cita agendada para $fecha a las $hora. Pendiente de aceptar.";
        $stmt_notif->bind_param("is", $nutriologo_id, $mensaje);
        $stmt_notif->execute();

        // Redirigir con mensaje
        header("Location: inicio_paciente.php?exito=1");
        exit;
    } else {
        echo "❌ Error al guardar la cita: " . $stmt->error;
    }
} else {
    echo "❌ Error en el pago.";
}
?>
