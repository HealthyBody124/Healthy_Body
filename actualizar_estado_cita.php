<?php
include 'conexion.php';
session_start();

if (!isset($_POST['cita_id'], $_POST['accion'])) {
    die("Datos inválidos.");
}

$cita_id = $_POST['cita_id'];
$accion = $_POST['accion'];

if (!in_array($accion, ['confirmar', 'rechazar'])) {
    die("Acción no válida.");
}

$estado = $accion === 'confirmar' ? 'confirmada' : 'rechazada';

$stmt = $conexion->prepare("UPDATE citas SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $estado, $cita_id);

if ($stmt->execute()) {
    header("Location: inicio_nutriologo.php?estado_actualizado=true");
} else {
    echo "❌ Error al actualizar estado: " . $stmt->error;
}
?>
