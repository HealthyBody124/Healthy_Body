<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'paciente') {
    header("Location: login.php");
    exit;
}

$nutriologo_id = 1; // o recuperarlo desde la base de datos según corresponda
$usuario = $_SESSION['usuario'];

$conexion = new mysqli("localhost", "root", "", "sistema_nutricion");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Nutriólogo asignado (fijo por ahora)
$usuario_id = (int)$_SESSION['usuario']['id'];

// Obtener datos nutriólogo
$stmt = $conexion->prepare("SELECT * FROM nutriologos WHERE id = ?");
$stmt->bind_param("i", $nutriologo_id);
$stmt->execute();
$resultado = $stmt->get_result();
$nutriologo = $resultado->fetch_assoc();

// Actualizar datos paciente (solo nombre, apellido y teléfono)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_datos'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $telefono = $_POST['telefono'];

    $stmt = $conexion->prepare("UPDATE pacientes SET nombre=?, apellido=?, telefono=? WHERE id=?");
    $stmt->bind_param("sssi", $nombre, $apellido, $telefono, $usuario['id']);
    if ($stmt->execute()) {
        $_SESSION['usuario']['nombre'] = $nombre;
        $_SESSION['usuario']['apellido'] = $apellido;
        $_SESSION['usuario']['telefono'] = $telefono;
        $usuario = $_SESSION['usuario'];
        $mensaje_actualizacion = "<p style='color:green;'>Datos actualizados correctamente.</p>";
    } else {
        $mensaje_actualizacion = "<p style='color:red;'>Error al actualizar los datos.</p>";
    }
}

if (isset($_GET['exito']) && $_GET['exito'] == 1): ?>
    <div id="toast" class="toast">
        ✅ Cita agendada correctamente.
    </div>

<?php endif; ?>




<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Inicio Paciente - Chat con Nutriólogo</title>
<style>
    body {
    font-family: Arial, sans-serif;
    background: #f0f9ff;
    margin: 0;
    padding: 20px;
}

.section {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

h2 {
    color: #007bff;
}

.btn-chat {
    background: #28a745;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

/* ... y el resto del CSS ... */

    #chat-box {
        border: 1px solid #ccc;
        border-radius: 8px;
        height: 400px;
        overflow-y: auto;
        padding: 10px;
        background: #fafafa;
    }
    #chat-box p {
        margin: 5px 0;
        padding: 10px 15px;
        border-radius: 20px;
        max-width: 60%;
        word-wrap: break-word;
        font-size: 14px;
        clear: both;
    }
    #chat-box .msg-tuyo {
        background: #1976d2;
        color: white;
        float: right;
        text-align: right;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    #chat-box .msg-otro {
        background: #e0e0e0;
        color: #333;
        float: left;
        text-align: left;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    #chat-form {
        padding: 10px 0;
        display: flex;
        gap: 10px;
    }
    #chat-form input[type="text"] {
        flex-grow: 1;
        padding: 10px;
        border-radius: 20px;
        border: 1px solid #ccc;
        outline: none;
        font-size: 14px;
    }
    #chat-form button, #chat-form input[type="submit"] {
        padding: 10px 20px;
        border: none;
        background: #1976d2;
        color: white;
        border-radius: 20px;
        cursor: pointer;
        font-weight: bold;
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
</style>

<style>
    .rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }

    .rating input[type="radio"] {
        display: none;
    }

    .rating label {
        font-size: 24px;
        color: #ccc;
        cursor: pointer;
        transition: color 0.3s;
        padding: 0 2px;
    }

    .rating input[type="radio"]:checked ~ label,
    .rating label:hover,
    .rating label:hover ~ label {
        color: #f7d106;
    }

    .rating label:hover {
        transform: scale(1.2);
    }

    .boton-chat {
    background-color: #28a745;
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 5px;
    border: none;
    transition: background-color 0.3s;
    }

    .boton-chat:hover {
    background-color: #218838;
    }
</style>

</head>

<body>

<nav style="background:#218838; padding: 10px; border-radius: 10px; margin-bottom: 20px;">
    <select id="menu-desplegable" onchange="mostrarSeccion(this.value)" style="padding: 10px; border-radius: 5px; font-size: 16px;">
        <option value="seccion-busqueda">Buscar Nutriólogo</option>
        <option value="seccion-info">Información Personal</option>
        <option value="seccion-chat">Chat del Nutriólogo</option>
    </select>
</nav>   

<!-- Botón Cerrar sesión -->
<form action="logout.php" method="POST" style="text-align: right;">
    <button type="submit" class="btn-logout">Cerrar sesión</button>
</form>
<div class="section" id="seccion-info" style="display:none;">
    <h2>Información Personal</h2>
    <?php if (isset($mensaje_actualizacion)) echo $mensaje_actualizacion; ?>

    <div class="info-personal">
        <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
        <p><strong>Apellido:</strong> <?= htmlspecialchars($usuario['apellido']) ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($usuario['telefono']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
    </div>

    <a href="actualizar_paciente.php" class="btn-actualizar">Actualizar Datos</a>
</div>

<style>
    .info-personal {
        background: #e9f5ff;
        padding: 15px 20px;
        border-radius: 8px;
        border: 1px solid #a1caff;
        max-width: 400px;
        font-size: 16px;
        color: #333;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .info-personal p {
        margin: 8px 0;
    }

    .btn-actualizar {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 10px 18px;
        text-decoration: none;
        border-radius: 8px;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .btn-actualizar:hover {
        background-color: #0056b3;
    }
</style>



<div class="section" id="seccion-cita" style="display:none;">
    <h2>Agendar Cita</h2>
    <form method="POST" action="pago.php">

        <label for="fecha">Fecha:</label><br />
        <input type="date" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>" required><br /><br />

        <label for="hora">Hora:</label><br />
        <select id="hora" name="hora" required>
        <?php
        for ($h = 9; $h <= 17; $h++) {
            $hora_formateada = str_pad($h, 2, '0', STR_PAD_LEFT) . ":00";
            echo "<option value='$hora_formateada'>$hora_formateada</option>";
        }
        ?>
        </select><br /><br />
        
        <!-- Este input se actualizará dinámicamente -->
        <input type="hidden" name="nutriologo_id" id="input_nutriologo_id" value="" />

        <button type="submit" class="boton-chat">Confirmar Cita</button>
    </form>
</div>



<div class="section" id="seccion-busqueda" style="display:block;">

    <h2>Buscar Nutriólogo por Precio</h2>
    <form method="GET">
        <label for="precio_max">Precio máximo ($):</label>
        <input type="number" name="precio_max" id="precio_max" value="<?= isset($_GET['precio_max']) ? htmlspecialchars($_GET['precio_max']) : '' ?>" />
        <button type="submit" class="btn-chat">Buscar</button>
    </form>

    <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
        <?php
    $precio_max = isset($_GET['precio_max']) ? (int)$_GET['precio_max'] : null;

    $query = "
        SELECT n.id, n.nombre, n.apellido, n.precio_consulta,
        ROUND(AVG(c.estrellas), 1) AS promedio
        FROM nutriologos n
        LEFT JOIN calificaciones c ON n.id = c.nutriologo_id
    ";
    if ($precio_max) {
        $query .= " WHERE n.precio_consulta <= ?";
    }
    $query .= " GROUP BY n.id";

    if ($precio_max) {
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $precio_max);
    } else {
        $stmt = $conexion->prepare($query);
    }

$stmt->execute();
$result = $stmt->get_result();
$nutriologos = $result->fetch_all(MYSQLI_ASSOC);


    if ($nutriologos):
    foreach ($nutriologos as $n):
    ?>
<script>
function mostrarSeccionCita(idNutriologo) {
    // Mostrar la sección de agendar cita
    document.getElementById('seccion-cita').style.display = 'block';

    // Rellenar el campo oculto con el ID del nutriólogo
    document.getElementById('input_nutriologo_id').value = idNutriologo;

    // Opcional: hacer scroll hacia esa parte
    document.getElementById('seccion-cita').scrollIntoView({ behavior: 'smooth' });
}
</script>

<div style="background: #f8f9fa; border: 1px solid #ddd; border-radius: 10px; padding: 15px; width: 220px;">
    <h4><?= htmlspecialchars($n['nombre'] . ' ' . $n['apellido']) ?></h4>
    <p><strong>Precio:</strong> $<?= htmlspecialchars(number_format($n['precio_consulta'], 2)) ?></p>

    <p><strong>Calificación promedio:</strong>
        <?php
            if ($n['promedio'] !== null) {
                echo number_format($n['promedio'], 1) . " / 5 ⭐";
            } else {
                echo "Sin calificaciones aún";
            }
        ?>
    </p>

    <form method="POST" action="calificar.php">
        <input type="hidden" name="nutriologo_id" value="<?= $n['id'] ?>">
        <div style="margin: 10px 0;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <label>
                    <input type="radio" name="estrellas" value="<?= $i ?>" required />
                    ⭐
                </label>
            <?php endfor; ?>
        </div>
        <button type="submit" class="btn-chat">Calificar</button>
    
<button type="button" class="boton-chat" onclick="mostrarSeccionCita(<?= $n['id'] ?>)">Agendar cita</button>


</form>


</div>
        <?php
            endforeach;
        else:
            echo "<p style='color:red;'>No se encontraron nutriólogos con ese criterio.</p>";
        endif;
        ?>
    </div>
</div>

<!-- Conversaciones -->
<div class="section" id="seccion-chat" style="display:none;">
    <h2>Conversaciones con Nutriólogos</h2>
    <div class="chat-container">
        <div class="conversaciones" id="listaConversaciones">
            <?php
            // Obtener nutriólogos con los que el paciente ha conversado
            $paciente_id = $usuario['id'];
            $stmt = $conexion->prepare("
                SELECT DISTINCT n.id, n.nombre, n.apellido
                FROM nutriologos n
                WHERE n.id IN (
                    SELECT CASE
                        WHEN m.tipo_remitente = 'paciente' THEN m.destinatario_id
                        ELSE m.remitente_id
                    END AS nutriologo_id
                    FROM mensajes m
                    WHERE (m.remitente_id = ? AND m.tipo_remitente = 'paciente')
                    OR (m.destinatario_id = ? AND m.tipo_remitente = 'nutriologo')
                )
                ORDER BY n.nombre ASC
            ");
            $stmt->bind_param("ii", $paciente_id, $paciente_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $conversaciones = $result->fetch_all(MYSQLI_ASSOC);
            ?>

            <?php foreach ($conversaciones as $nutriologo): ?>
                <div class="chat-item" onclick="seleccionarContacto(<?= $nutriologo['id'] ?>, '<?= htmlspecialchars($nutriologo['nombre'] . ' ' . $nutriologo['apellido']) ?>')">
                    <?= htmlspecialchars($nutriologo['nombre'] . ' ' . $nutriologo['apellido']) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="chat-box">
            <div id="chat-mensajes">Selecciona un nutriólogo para iniciar el chat.</div>
            <form id="chat-form">
                <input type="text" id="mensaje" name="mensaje" placeholder="Escribe tu mensaje..." required />
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>
</div>

<!-- Estilos y Script para chat -->
<style>
    .chat-container {
        display: flex;
        height: 500px;
    }
    .conversaciones {
        width: 25%;
        border-right: 1px solid #ccc;
        overflow-y: auto;
        background: #fff;
        padding: 10px;
    }
    .chat-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }
    .chat-item:hover {
        background-color: #f0f0f0;
    }
    .chat-box {
        width: 75%;
        padding: 10px;
        display: flex;
        flex-direction: column;
    }
    #chat-mensajes {
        flex-grow: 1;
        overflow-y: auto;
        background-color: #f5f5f5;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
    }
    .msg-tuyo, .msg-otro {
        max-width: 70%;
        padding: 8px 12px;
        margin-bottom: 10px;
        border-radius: 15px;
        word-wrap: break-word;
    }
    .msg-tuyo {
        background-color: #DCF8C6;
        align-self: flex-end;
        text-align: right;
    }
    .msg-otro {
        background-color: #FFFFFF;
        align-self: flex-start;
        text-align: left;
    }
    #chat-form {
        display: flex;
    }
    #chat-form input[type="text"] {
        flex-grow: 1;
        padding: 10px;
        border-radius: 20px;
        border: 1px solid #ccc;
        outline: none;
        font-size: 14px;
    }
    #chat-form button {
        margin-left: 10px;
        padding: 10px 20px;
        border: none;
        background: #1976d2;
        color: white;
        border-radius: 20px;
        cursor: pointer;
        font-weight: bold;
    }
</style>

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
function mostrarSeccion(id) {
    const secciones = ['seccion-info', 'seccion-cita', 'seccion-busqueda', 'seccion-chat'];
    secciones.forEach(sec => {
        document.getElementById(sec).style.display = (sec === id) ? 'block' : 'none';
    });
}
</script>


<script>
function mostrarSeccion(seccionId) {
    const secciones = ["seccion-info", "seccion-cita", "seccion-busqueda", "seccion-chat"];
    secciones.forEach(id => {
        document.getElementById(id).style.display = (id === seccionId) ? "block" : "none";
    });
}

function mostrarSeccionCita(nutriologoId) {
    mostrarSeccion('seccion-cita');

    // Establecer el valor del input hidden del nutriólogo si existe
    const inputNutriologo = document.querySelector('#seccion-cita input[name="nutriologo_id"]');
    if (inputNutriologo) {
        inputNutriologo.value = nutriologoId;
    }

    // También puedes cambiar el valor del select del menú si quieres que se refleje visualmente:
    const menu = document.getElementById('menu-desplegable');
    if (menu) menu.value = 'seccion-cita';
}
</script>


<style>
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #28a745;
    color: white;
    padding: 16px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    font-size: 16px;
    z-index: 9999;
    animation: fadeOut 1s ease-in-out 4s forwards;
}
@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(20px);
    }
}
</style>


<script>
    // Borra el parámetro "exito" de la URL después de mostrar el toast
    window.addEventListener("DOMContentLoaded", () => {
        if (window.location.search.includes("exito=1")) {
            setTimeout(() => {
                const url = new URL(window.location.href);
                url.searchParams.delete("exito");
                window.history.replaceState({}, document.title, url.pathname);
            }, 5000);
        }
    });
</script>


</body>
</html>
