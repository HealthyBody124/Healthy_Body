<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'nutriologo') {
    header("Location: login.php");
    exit;
}

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener id de usuario de sesión (supongo que es un entero)
$usuario_id = (int)$_SESSION['usuario']['id'];

// Cargar datos completos del nutriólogo desde BD
$stmt = $conexion->prepare("SELECT * FROM nutriologos WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    // Si no existe el nutriólogo, cerrar sesión y redirigir
    session_destroy();
    header("Location: login.php");
    exit;
}

$mensaje_actualizacion = '';

// Procesar actualización si viene POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_datos'])) {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $precio_consulta = $_POST['precio_consulta'] ?? 0;
    $antiguedad = $_POST['antiguedad'] ?? 0;

    $stmt = $conexion->prepare("UPDATE nutriologos SET nombre=?, apellido=?, telefono=?, correo=?, precio_consulta=?, antiguedad=? WHERE id=?");
    $stmt->bind_param("ssssdii", $nombre, $apellido, $telefono, $correo, $precio_consulta, $antiguedad, $usuario_id);

    if ($stmt->execute()) {
        // Actualizar datos en la sesión para mantener sincronizados
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['apellido'] = $apellido;
        $_SESSION['usuario']['telefono'] = $telefono;
        $_SESSION['usuario']['correo'] = $correo;
        $_SESSION['usuario']['precio_consulta'] = $precio_consulta;
        $_SESSION['usuario']['antiguedad'] = $antiguedad;

        $mensaje_actualizacion = "<p style='color:green;'>Datos actualizados correctamente.</p>";

        // También actualizar $usuario para reflejar cambios en el formulario
        $usuario['nombre'] = $nombre;
        $usuario['apellido'] = $apellido;
        $usuario['telefono'] = $telefono;
        $usuario['correo'] = $correo;
        $usuario['precio_consulta'] = $precio_consulta;
        $usuario['antiguedad'] = $antiguedad;
    } else {
        $mensaje_actualizacion = "<p style='color:red;'>Error al actualizar los datos.</p>";
    }
}


// Obtener lista de pacientes con los que ha chateado el nutriólogo
$stmt = $conexion->prepare("
    SELECT DISTINCT p.id, p.nombre, p.apellido
    FROM pacientes p
    WHERE p.id IN (
        SELECT CASE
            WHEN m.tipo_remitente = 'nutriologo' THEN m.destinatario_id
            ELSE m.remitente_id
        END
        FROM mensajes m
        WHERE (m.remitente_id = ? AND m.tipo_remitente = 'nutriologo')
        OR (m.destinatario_id = ? AND m.tipo_remitente = 'paciente')
    )
    ORDER BY p.nombre ASC
");
$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();
$chats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);



// Obtener citas pendientes para el nutriólogo actual
$stmt = $conexion->prepare("
    SELECT c.id, p.nombre, p.apellido, c.fecha, c.hora, c.motivo 
    FROM citas c 
    JOIN pacientes p ON c.paciente_id = p.id 
    WHERE c.nutriologo_id = ? AND c.estado = 'pendiente'
    ORDER BY c.fecha ASC, c.hora ASC
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$citas_pendientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


$conexion->close();
?>



<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Inicio Nutriólogo</title>
<style>
    body { font-family: Arial, sans-serif; background: #f0f9ff; margin: 0; padding: 20px; }
.section {
    background: white;
    padding: 20px;
    margin: 0 auto 20px auto;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    /* max-width: 300px;  <- eliminar o comentar */
}

/* Para la sección de info personal sí queremos restringir ancho */
#seccion-info {
    max-width: 300px;
    margin: 0 auto 20px auto;
}
.section {
    padding: 20px;
}

.info-item {
    display: flex;
    margin-bottom: 10px;
}

.info-label {
    font-weight: bold;
    width: 180px;
}

.info-value {
    flex: 1;
}

.update-button {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}


    .info-personal-card h2 {
        text-align: center;
        color: #007bff;
        margin-bottom: 20px;
    }

    .info-item {
        margin-bottom: 15px;
    }

    .info-label {
        font-weight: bold;
        color: #343a40;
    }

    .info-value {
        color: #495057;
        font-size: 16px;
        margin-top: 3px;
    }

    .update-button {
        display: block;
        margin: 20px auto 0;
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s ease;
        text-align: center;
        text-decoration: none;
    }

    .update-button:hover {
        background-color: #0056b3;
    }


    
#seccion-chat.section {
    max-width: none;
    width: 100%;
}


    .btn-logout {
        background: #dc3545;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        float: right;
        margin-bottom: 20px;
    }

.chat-container {
    display: flex;
    height: 600px; /* un poco más alto si quieres */
    max-width: 100%; /* para que ocupe todo el ancho posible */
    border: 1px solid #ccc;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.conversaciones {
    width: 30%; /* Más ancho para la lista */
    border-right: 1px solid #ccc;
    overflow-y: auto;
    background: #fff;
    padding: 15px;
    box-sizing: border-box;
}

.chat-box {
    width: 70%; /* Más ancho para el chat */
    padding: 15px;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
}

#chat-mensajes {
    height: 400px;
    overflow-y: scroll;
    display: flex;
    flex-direction: column;
    padding: 10px;
}

.msg-tuyo, .msg-otro {
    max-width: 70%;
    padding: 10px 14px;
    margin-bottom: 10px;
    border-radius: 20px;
    font-size: 15px;
    line-height: 1.3;
    word-wrap: break-word;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.msg-tuyo {
    background-color: #dcf8c6;
    align-self: flex-end;
    text-align: right;
    border-bottom-right-radius: 5px;
}

.msg-otro {
    background-color: #fff;
    align-self: flex-start;
    text-align: left;
    border-bottom-left-radius: 5px;
}

#chat-form {
    display: flex;
    gap: 10px;
}

#chat-form input[type="text"] {
    flex-grow: 1;
    padding: 12px 20px;
    border-radius: 25px;
    border: none;
    font-size: 15px;
    outline: none;
    box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
}

#chat-form button {
    padding: 12px 25px;
    border: none;
    background: #25d366;
    color: white;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

#chat-form button:hover {
    background-color: #128c21;
}


    <style>
    .notification-bell {
    position: relative;
    cursor: pointer;
    font-size: 24px;
    color: #333;
    }

    .notification-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    }

    .notification-panel {
    display: none;
    position: absolute;
    right: 0;
    top: 35px;
    width: 320px;
    background-color: white;
    border: 1px solid #ccc;
    z-index: 999;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .notification-panel ul {
    list-style: none;
    padding: 0;
    margin: 0;
    }

    .notification-panel li {
    border-bottom: 1px solid #eee;
    padding: 8px 0;
    }
    </style>

</style>
</head>
<body>

<form action="logout.php" method="POST" style="text-align: right;">
    <button type="submit" class="btn-logout">Cerrar sesión</button>
    <div style="margin-bottom: 20px;">
    <label for="menu-secciones" style="font-weight:bold; margin-right:10px;">Selecciona una sección:</label>

    <select id="menu-secciones" onchange="mostrarSeccion(this.value)" style="padding:8px; border-radius:5px;">
        <option value="info">Información Personal</option>
        <option value="chat">Chat con Pacientes</option>
    </select>
</div>



    <div style="position: relative; display: inline-block;" id="notification-container">
    <div class="notification-bell" onclick="toggleNotifications()">
        🔔
        <?php if (!empty($citas_pendientes)): ?>
        <span class="notification-count"><?= count($citas_pendientes) ?></span>
        <?php endif; ?>
    </div>

    <div class="notification-panel" id="notificationPanel">
        <h4>Citas pendientes</h4>
        <ul>
        <?php foreach ($citas_pendientes as $cita): ?>
            <li>
            <strong><?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido']) ?></strong><br>
            <?= htmlspecialchars($cita['fecha']) ?> - <?= htmlspecialchars($cita['hora']) ?><br>
            <?= htmlspecialchars($cita['motivo']) ?><br>
            <form method="post" action="actualizar_estado_cita.php">
                <input type="hidden" name="cita_id" value="<?= $cita['id'] ?>">
                <button type="submit" name="accion" value="confirmar">✅</button>
                <button type="submit" name="accion" value="rechazar">❌</button>
            </form>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    </div>



</form>
<div id="seccion-info" class="section">
    <h2>Información Personal</h2>

    <div class="info-item">
        <div class="info-label">Nombre:</div>
        <div class="info-value"><?= htmlspecialchars($usuario['nombre']) ?></div>
    </div>

    <div class="info-item">
        <div class="info-label">Apellido:</div>
        <div class="info-value"><?= htmlspecialchars($usuario['apellido']) ?></div>
    </div>

    <div class="info-item">
        <div class="info-label">Teléfono:</div>
        <div class="info-value"><?= htmlspecialchars($usuario['telefono']) ?></div>
    </div>

    <div class="info-item">
        <div class="info-label">Correo:</div>
        <div class="info-value"><?= htmlspecialchars($usuario['correo']) ?></div>
    </div>

    <div class="info-item">
        <div class="info-label">Precio de consulta:</div>
        <div class="info-value"><?= htmlspecialchars($usuario['precio_consulta']) ?> MXN</div>
    </div>

    <div class="info-item">
    <p><strong>Antigüedad:</strong> 
        <?= isset($usuario['antiguedad']) ? htmlspecialchars($usuario['antiguedad']) . ' año(s)' : 'No especificado' ?>
    </p>
    </div>

    <a href="actualizar_nutriologo.php" class="update-button">Actualizar Datos</a>
</div>





</form>

    </form>
</div>

</div>

<div id="seccion-chat" class="section" style="display:none;">
    <h2>Chat con Pacientes</h2>

    <div class="chat-container">
        <div class="conversaciones">
            <h3>Pacientes</h3>
            <ul>
                <?php foreach ($chats as $chat): ?>
                    <li onclick="seleccionarContacto(<?= $chat['id'] ?>)">
                        <?= htmlspecialchars($chat['nombre'] . ' ' . $chat['apellido']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="chat-box">
            <div id="chat-mensajes"></div>

            <form id="chat-form">
                <input type="hidden" id="paciente_id" value="">
                <input type="text" id="mensaje" placeholder="Escribe un mensaje...">
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>
</div>


<script>
const remitente = <?= json_encode($usuario['id']) ?>;
let contactoSeleccionado = null;

function seleccionarContacto(id) {
    contactoSeleccionado = id;
    cargarMensajes();
}

function cargarMensajes() {
    if (!contactoSeleccionado) return;
    fetch(`chat.php?otro_id=${contactoSeleccionado}`)
        .then(res => res.text())
        .then(data => {
            const chatMensajes = document.getElementById('chat-mensajes');
            chatMensajes.innerHTML = data;
            chatMensajes.scrollTop = chatMensajes.scrollHeight;
        });
}

document.getElementById('chat-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const mensajeInput = document.getElementById('mensaje');
    const mensaje = mensajeInput.value.trim();
    if (!mensaje || !contactoSeleccionado) return;

    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `mensaje=${encodeURIComponent(mensaje)}&destinatario_id=${contactoSeleccionado}`
    }).then(() => {
        mensajeInput.value = '';
        cargarMensajes();
    });
});

// Actualizar mensajes cada 2 segundos
setInterval(() => {
    if (contactoSeleccionado) {
        cargarMensajes();
    }
}, 2000);
</script>




<script>
function mostrarSeccion(seccion) {
    document.getElementById('seccion-info').style.display = (seccion === 'info') ? 'block' : 'none';
    document.getElementById('seccion-chat').style.display = (seccion === 'chat') ? 'block' : 'none';
}

    // Cerrar si se hace clic fuera
    window.addEventListener("click", function(e) {
    const bell = document.getElementById("notification-container");
    const panel = document.getElementById("notificationPanel");
    if (!bell.contains(e.target)) {
        panel.style.display = "none";
    }
    });
    </script>


    <script>
    function toggleNotifications() {
    const panel = document.getElementById("notificationPanel");
    panel.style.display = panel.style.display === "block" ? "none" : "block";
    }
</script>

</body>
</html>
