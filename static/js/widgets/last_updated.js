/* last_updated.js */
let lastTimestamp = 0;
let lastDiff = 0;

// Recalcula cada segundo la diferencia real
function updateSeconds() {
    if (!lastTimestamp) return;
    const now = Math.floor(Date.now() / 1000);
    let diff = now - lastTimestamp;

    // Evita números negativos o absurdos
    if(diff < 0) diff = 0;

    // Solo actualizar si el valor cambió
    if(diff !== lastDiff) {
        document.getElementById('pws-status-time-ago').textContent = 'Hace ' + diff + ' segundos';
        lastDiff = diff;
    }
}

// Consulta PHP
async function fetchLastUpdate() {
    try {
        const url = './static/modules/widgets/get_last_update.php?_=' + new Date().getTime();
        const response = await fetch(url, {cache: "no-store"});
        const data = await response.json();

        if(data.last_update_timestamp && data.last_update_timestamp !== lastTimestamp) {
            lastTimestamp = data.last_update_timestamp;

            // Actualiza contador inmediatamente
            updateSeconds();

            // Llamar al widget solo si cambió el timestamp
            if(typeof updateWindWidget === "function") updateWindWidget();
        }
    } catch(err) {
        console.error('Error fetch:', err);
    }
}

// Inicializar
fetchLastUpdate();
setInterval(fetchLastUpdate, 10000); // Consultar PHP cada 10s
setInterval(updateSeconds, 1000);    // Actualizar contador cada segundo
