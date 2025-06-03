<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'nutriologo') {
    http_response_code(403);
    exit;
}

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
if ($conexion->connect_error) {
    http_response_code(500);
    exit;
}

// Obtener citas pendientes
$query = "SELECT c.id, p.nombre, p.apellido, c.fecha, c.hora, c.motivo 
          FROM citas c JOIN pacientes p ON c.paciente_id = p.id 
          WHERE c.estado = 'pendiente' AND c.nutriologo_id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($cita = $result->fetch_assoc()) {
    echo "<li>
        <strong>" . htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido']) . "</strong><br>" .
        htmlspecialchars($cita['fecha']) . " - " . htmlspecialchars($cita['hora']) . "<br>" .
        htmlspecialchars($cita['motivo']) . "<br>" .
        '<form onsubmit="return actualizarEstadoCita(event, ' . $cita['id'] . ')">' .
        '<button type="submit" name="accion" value="confirmar">✅</button>' .
        '<button type="submit" name="accion" value="rechazar">❌</button>' .
        '</form></li>';
}
$conexion->close();
?>
