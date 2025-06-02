function cargarChat() {
    fetch(`chat.php?emisor_id=${remitente}&receptor_id=${receptor}`)
        .then(res => res.json())
        .then(mensajes => {
            let html = '';
            mensajes.forEach(msg => {
                const clase = (msg.emisor_id == remitente) ? 'msg-tuyo' : 'msg-otro';
                html += `<p class="${clase}">${msg.mensaje}</p>`;
            });
            document.getElementById("chat-box").innerHTML = html;
            document.getElementById("chat-box").scrollTop = document.getElementById("chat-box").scrollHeight;
        });
}

function enviarMensaje() {
    const mensaje = document.getElementById("mensaje").value;
    if (mensaje.trim() === "") return;

    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `emisor_id=${remitente}&receptor_id=${receptor}&mensaje=${encodeURIComponent(mensaje)}`
    }).then(() => {
        document.getElementById("mensaje").value = "";
        cargarChat();
    });
}

setInterval(cargarChat, 2000);
