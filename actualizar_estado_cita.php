<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'nutriologo') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
    if ($conexion->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Error de conexión']);
        exit;
    }

    $cita_id = isset($_POST['cita_id']) ? (int)$_POST['cita_id'] : 0;
    $accion = $_POST['accion'] ?? '';

    if ($cita_id > 0 && in_array($accion, ['confirmar', 'rechazar'])) {
        $nuevo_estado = $accion === 'confirmar' ? 'confirmada' : 'rechazada';
        $stmt = $conexion->prepare("UPDATE citas SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $cita_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar base de datos']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    }

    $conexion->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>
