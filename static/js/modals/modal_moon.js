// modal_moon.js
// --- Importar el Catálogo oficial Lunar 100 desde el nuevo archivo ---
// Asumimos que './lunar_data.js' exporta LUNAR_100_FEATURES
import { LUNAR_100_FEATURES } from './lunar_data.js';

// --- Variables de estado ---
let selectedFeatureForZoom = null;
let visibleFeatures = []; // Almacenará la lista de features visibles en el terminador

// --- Funciones auxiliares ---
function wrap180(x){
    let y = ((x + 180) % 360 + 360) % 360 - 180;
    return (y === -180) ? 180 : y;
}

function toVisibleRange90(x){
    let y = wrap180(x);
    if(y > 90) return y - 180;
    if(y < -90) return y + 180;
    return y;
}

function toUTCInputValue(d){
    const pad = n => String(n).padStart(2,'0');
    return `${d.getUTCFullYear()}-${pad(d.getUTCMonth()+1)}-${pad(d.getUTCDate())}T${pad(d.getUTCHours())}:${pad(d.getUTCMinutes())}:${pad(d.getUTCSeconds())}`;
}

function parseUTCInput(val){
    if(!val) return new Date();
    const parts = val.replace('T',' ').split(/[- :]/).map(Number);
    return new Date(Date.UTC(parts[0], parts[1]-1, parts[2], parts[3]||0, parts[4]||0, parts[5]||0));
}

// --- Cálculo principal ---
function computeMoonData(date){
    const ill = SunCalc.getMoonIllumination(date);
    const phase = ill.phase;      // 0..1
    const fraction = ill.fraction;
    const angle = ill.angle;      // rad

    const isWaxing = angle < 0;
    const terminatorName = isWaxing ? "Amanecer lunar" : "Anochecer lunar";

    // Longitud subsolar (Ls)
    const subsolarDeg = 360 * phase - 180;

    // Terminador del amanecer = subsolar + 90°
    let terminatorDeg360 = (-360 * phase - 90) % 360;
    if (terminatorDeg360 < 0) terminatorDeg360 += 360;

    // Normaliza y ajusta a rango visible
    const terminatorDeg180 = wrap180(terminatorDeg360);
    const terminatorVisible90 = toVisibleRange90(terminatorDeg180);
    const onNearSide = (terminatorDeg180 >= -90 && terminatorDeg180 <= 90);

    return {
        dateUTC: date.toISOString().replace('T',' ').replace('Z',' UTC'),
        phaseFraction: phase,
        illuminatedFraction: fraction,
        angleRad: angle,
        waxing: isWaxing,
        terminatorDeg360,
        terminatorDeg180,
        terminatorVisible90,
        onNearSide,
        terminatorName
    };
}

// ==========================================================
// === LÓGICA DEL SEGUNDO MODAL (INTERACTIVO) ================
// ==========================================================

// **MODIFICACIÓN CLAVE**: Añadido `initialFeature` como parámetro
function openInteractiveMoonFeatureModal(features, initialFeature = null) {
    const modal = document.getElementById('moonFeatureModal');
    const canvas = document.getElementById('moonFeatureCanvas');
    const ctx = canvas.getContext('2d');
    const info = document.getElementById('moonFeatureInfo');
    const desc = document.getElementById('moonFeatureDescription');

    // **NUEVA LÓGICA**: Si se proporciona un initialFeature, lo mostramos.
    if (initialFeature) {
        const formattedLong = `${Math.abs(initialFeature.long).toFixed(1)}° ${initialFeature.long < 0 ? 'O' : 'E'}`;
        const formattedLat = `${Math.abs(initialFeature.lat).toFixed(1)}° ${initialFeature.lat > 0 ? 'N' : 'S'}`;

        info.innerHTML = `
            <strong>L${initialFeature.l100} - ${initialFeature.name}</strong>
            <small>[${formattedLat}, ${formattedLong}]</small>
        `;
        desc.textContent = initialFeature.description;
    } else {
        info.textContent = 'Haz clic en un punto para hacer zoom.';
        desc.textContent = '';
    }

    modal.style.display = 'block';

    const width = canvas.width;
    const height = canvas.height;
    const cx = width / 2;
    const cy = height / 2;
    const R = Math.min(cx, cy);

    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = './static/images/full_moon_big.png';

    img.onload = () => {
        // --- Función de Dibujo ---
        const drawFeatures = (hoveredFeature = null) => {
            ctx.save();
            ctx.clearRect(0, 0, width, height);

            // 1. Dibujar la luna
            ctx.beginPath();
            ctx.arc(cx, cy, R, 0, 2 * Math.PI);
            ctx.clip();
            ctx.drawImage(img, 0, 0, width, height);

            // 2. Dibujar el terminador simple
            const grad = ctx.createRadialGradient(cx, cy, 0, cx, cy, R);
            grad.addColorStop(0, 'rgba(0,0,0,0)');
            grad.addColorStop(0.5, 'rgba(0,0,0,0.2)');
            grad.addColorStop(1, 'rgba(0,0,0,0.5)');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, width, height);

            // 3. Dibujar todos los puntos interactivos
            features.forEach(f => {
                const deg2rad = d => d * Math.PI / 180;
                const lon = deg2rad(f.long);
                const lat = deg2rad(f.lat);

                // Proyección Ortográfica
                const x_proj = R * Math.cos(lat) * Math.sin(lon);
                const y_proj = R * Math.sin(lat);
                const xPixel = cx + x_proj;
                const yPixel = cy - y_proj;

                // Si el punto no es visible (cara oculta), no lo dibujamos
                if ((Math.cos(lat) * Math.cos(lon)) < 0) return;

                // Determinar el color: hover, inicial (resaltado), o por defecto (rojo)
                const isHovered = (hoveredFeature && hoveredFeature.name === f.name);
                const isInitial = (initialFeature && initialFeature.name === f.name);

                const pointRadius = (isHovered || isInitial) ? Math.max(7, Math.round(R * 0.02)) : Math.max(5, Math.round(R * 0.015));
                const pointColor = (isHovered || isInitial) ? '#00FF00' : 'red'; // Verde para hover O inicial

                ctx.beginPath();
                ctx.arc(xPixel, yPixel, pointRadius, 0, 2 * Math.PI);

                ctx.fillStyle = pointColor;
                ctx.fill();

                ctx.lineWidth = (isHovered || isInitial) ? 2 : 1;
                ctx.strokeStyle = 'white';
                ctx.stroke();

                // Añadir coordenadas para detección de clics/hover
                f._pixelX = xPixel;
                f._pixelY = yPixel;
                f._radius = pointRadius;
            });

            ctx.restore();
        };

        // DIBUJO INICIAL: Pasa el initialFeature para que se dibuje en verde
        drawFeatures(initialFeature);

        // 4. Implementar Hover (Mousemove)
        canvas.onmousemove = (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            let hoveredFeature = null;
            let cursorChanged = false;

            for (const f of features) {
                if (f._pixelX) {
                    const dist = Math.hypot(mouseX - f._pixelX, mouseY - f._pixelY);

                    if (dist < f._radius + 5) {
                        hoveredFeature = f;
                        cursorChanged = true;
                        break;
                    }
                }
            }

            // Redibujar con el punto en hover (si lo hay)
            drawFeatures(hoveredFeature);

            // Actualizar la información de texto
            if (hoveredFeature) {
                const formattedLong = `${Math.abs(hoveredFeature.long).toFixed(1)}° ${hoveredFeature.long < 0 ? 'O' : 'E'}`;
                const formattedLat = `${Math.abs(hoveredFeature.lat).toFixed(1)}° ${hoveredFeature.lat > 0 ? 'N' : 'S'}`;

                info.innerHTML = `
                    <strong>L${hoveredFeature.l100} - ${hoveredFeature.name}</strong>
                    <small>[${formattedLat}, ${formattedLong}]</small>
                `;
                desc.textContent = hoveredFeature.description;
            } else {
                info.textContent = 'Haz clic en un punto para hacer zoom.';
                desc.textContent = '';
            }

            // Cambiar cursor si está sobre un punto
            canvas.style.cursor = cursorChanged ? 'pointer' : 'default';
        };

        // 5. Implementar Click (para el Zoom)
        canvas.onclick = (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            let clickedFeature = null;

            for (const f of features) {
                if (f._pixelX) {
                    const dist = Math.hypot(mouseX - f._pixelX, mouseY - f._pixelY);
                    if (dist < f._radius + 5) {
                        clickedFeature = f;
                        break;
                    }
                }
            }

            if (clickedFeature) {
                // Disparar la apertura del 3er modal (Zoom)
                openMoonZoomModalFromClick(clickedFeature);
            }
        };

    };

    img.onerror = () => {
        info.textContent = 'Error cargando la imagen de la luna.';
    };
}


// --- NUEVA FUNCIÓN PARA ABRIR EL MODAL DE ZOOM DESDE EL CLIC EN EL CANVAS (3er Modal) ---
function openMoonZoomModalFromClick(feature) {
    const lon = feature.long;
    const lat = feature.lat;

    const zoomDescriptionElement = document.getElementById("zoomDescription");

    // Construcción del texto de la descripción para el modal de zoom (LXX - Nombre: Descripción)
    if (feature.l100 && feature.name && feature.description) {
        zoomDescriptionElement.innerHTML = `
            <strong>L${feature.l100} - ${feature.name}</strong>: ${feature.description}
        `;
    } else {
        zoomDescriptionElement.textContent = "Descripción no disponible.";
    }

    // Abrir el modal de zoom
    moonZoomModal.classList.add("show");
    document.body.style.overflow = "hidden";

    const img = document.getElementById("zoomMoonImage");
    if (img.complete) {
        centerZoomOn(lon, lat);
    } else {
        img.onload = () => centerZoomOn(lon, lat);
    }
}


// --- Función para mostrar datos en el modal (1er Modal) --
function updateMoonModal(){
    const date = new Date();//Date("2025-11-05T14:00:00");
    const moonData = computeMoonData(date);

    // Obtener el valor de la longitud del terminador y redondearlo a 2 decimales
    const terminatorLong = moonData.terminatorVisible90.toFixed(2);
    // Obtener el valor original (numérico) para la comprobación del signo
    const rawTerminatorLong = moonData.terminatorVisible90;

    let formattedTerminator;

    if (rawTerminatorLong < 0) {
        // Es negativo: Oeste lunar (W)
        formattedTerminator = `${Math.abs(terminatorLong)}º O`;
    } else {
        // Es positivo o cero: Este lunar (E)
        formattedTerminator = `${terminatorLong}º E`;
    }
    // Fase y colongitud
    document.getElementById('moon-phase-text').textContent = moonData.waxing ? 'Creciente' : 'Menguante';
    document.getElementById('terminator-visible').textContent = moonData.terminatorName;
    document.getElementById('terminator-long').textContent = formattedTerminator;

    // Filtrar accidentes cercanos al terminador (±10°)
    const tolerance = 10;
    const featuresNearTerminator = LUNAR_100_FEATURES.filter(f => {
        let delta = Math.abs(f.long - moonData.terminatorVisible90);
        return delta <= tolerance;
    });

    // Almacenamos la lista visible para usarla en el Modal 2
    visibleFeatures = featuresNearTerminator;

    // Mostrar Accidentes geográficos lunares
    const container = document.getElementById('moon-features-list');
    container.innerHTML = ''; // Limpiar el contenido

    featuresNearTerminator.forEach(f => {
        // 1. CÁLCULO DE COORDENADAS
        const longDir = f.long < 0 ? 'O' : 'E';
        const latDir = f.lat > 0 ? 'N' : 'S';

        // Formato: Sin el signo y con un decimal
        const formattedLong = `${Math.abs(f.long).toFixed(1)}° ${longDir}`;
        const formattedLat = `${Math.abs(f.lat).toFixed(1)}° ${latDir}`;

        // 2. CREACIÓN DEL CONTENEDOR PRINCIPAL
        const card = document.createElement('div');
        card.classList.add('card');

        // 3. NOMBRE DEL ACCIDENTE (Título)
        const title = document.createElement('h3');
        title.classList.add('feature-card-title');
        title.textContent = f.name;

        // 4. UBICACIÓN (Coordenadas pequeñas)
        const locationInfo = document.createElement('small');
        locationInfo.classList.add('feature-coords-info');
        locationInfo.style.opacity = '0.5';
        locationInfo.textContent = `[${formattedLat}, ${formattedLong}]`;

        // 5. NÚMERO DE CATÁLOGO (#Nº en grande)
        const value = document.createElement('p');
        value.classList.add('seeing-card-value');
        value.textContent = `#${f.l100}`;

        // 6. COMENTARIO (Descripción)
        const desc = document.createElement('span');
        desc.classList.add('seeing-card-desc');
        desc.textContent = f.description;


        // 7. ENSAMBLAR LA TARJETA
        card.appendChild(title);
        card.appendChild(locationInfo);
        card.appendChild(value);
        card.appendChild(desc);
        container.appendChild(card);

        // 8. Añadir eventListener a cada tarjeta:
        card.addEventListener('click', () => {
            // **MODIFICACIÓN CLAVE**: Pasa el objeto completo del feature (f) para el resalte inicial
            openInteractiveMoonFeatureModal(visibleFeatures, f);
        });
    });
}

// Escribir el pie de página.
const moonFooter = document.getElementById('moon-footer');

// Obtener la fecha y hora actuales.
const now = new Date();
const formattedDate = now.toLocaleDateString('es-ES', {
    day: '2-digit', month: 'long', year: 'numeric'
});
const formattedTime = now.toLocaleTimeString('es-ES', {
    hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
});

moonFooter.innerHTML = `
    <p class="seeing-attribution">
        Descargar mapa de Lunar 100 en super Alta Resolución <a href="./static/images/hires/lunar-100.jpg">aquí</a> por <a href="https://www.cloudynights.com/profile/382918-dylanjiva/">Dylan Evans</a>
    </p>
    <p class="seeing-attribution">
        La información mostrada es un **cálculo astronómico en tiempo real** para la fecha y hora: <strong>${formattedDate}, ${formattedTime}</strong>.
    </p>
    <p class="seeing-attribution">
        ⚠️ Estos datos son **estimaciones** basadas en modelos matemáticos y no
        provienen de observaciones astronómicas en vivo contrastadas.
    </p>
    <p class="seeing-attribution">
        Catálogo de accidentes geográficos: **Lunar 100**.
    </p>
`;

// --- Abrir modal al hacer click en la luna (1er Modal) ---
document.querySelector('.moon-card').addEventListener('click', () => {
    document.getElementById('moonModal').style.display = 'block';
    updateMoonModal();
});

// --- Cerrar modal (1er Modal) ---
document.getElementById('closeMoonModal').addEventListener('click', () => {
    document.getElementById('moonModal').style.display = 'none';
});

// --- Cerrar modal al hacer clic fuera del contenido (1er Modal) ---
window.addEventListener('click', (event) => {
    const moonModal = document.getElementById('moonModal');
    if (event.target === moonModal) {
        moonModal.style.display = 'none';
    }
});

// --- Cerrar modal secundario (moonFeatureModal) ---
document.getElementById('closeMoonFeatureModal').addEventListener('click', () => {
    document.getElementById('moonFeatureModal').style.display = 'none';
    // Limpiar handlers para evitar fugas de memoria o interacciones no deseadas
    document.getElementById('moonFeatureCanvas').onmousemove = null;
    document.getElementById('moonFeatureCanvas').onclick = null;
});

window.addEventListener('click', (event) => {
    const modal = document.getElementById('moonFeatureModal');
    if (event.target === modal) {
        modal.style.display = 'none';
        document.getElementById('moonFeatureCanvas').onmousemove = null;
        document.getElementById('moonFeatureCanvas').onclick = null;
    }
});

// ==========================================================
// === MODAL DE ZOOM LUNAR A TAMAÑO COMPLETO (3er Modal) ====
// ==========================================================

const moonFeatureCanvas = document.getElementById("moonFeatureCanvas");
const moonZoomModal = document.getElementById("moonZoomModal");
const closeMoonZoomModal = document.getElementById("closeMoonZoomModal");
const moonFeatureInfo = document.getElementById("moonFeatureInfo");

// --- Cerrar modal (3er Modal) ---
closeMoonZoomModal.addEventListener("click", () => {
    moonZoomModal.classList.remove("show");
    document.body.style.overflow = "";
});

// Cerrar al hacer clic fuera (3er Modal)
moonZoomModal.addEventListener("click", (e) => {
    if (e.target === moonZoomModal) {
        moonZoomModal.classList.remove("show");
        document.body.style.overflow = "";
    }
});

// -------------------------------------------------------------
// --- FUNCIÓN DE CENTRADO (proyección ortográfica) -------------
// -------------------------------------------------------------
function centerZoomOn(lonDeg, latDeg) {
    const img = document.getElementById("zoomMoonImage");
    const marker = document.getElementById("zoomMarker");
    const viewer = document.getElementById("zoomViewer");

    const W = img.naturalWidth;
    const H = img.naturalHeight;
    const cx = W / 2;
    const cy = H / 2;
    const R = Math.min(W, H) / 2;

    const lon = lonDeg * Math.PI / 180;
    const lat = latDeg * Math.PI / 180;

    const x = Math.cos(lat) * Math.sin(lon);
    const y = Math.sin(lat);
    const z = Math.cos(lat) * Math.cos(lon);

    if (z < 0) {
        alert("⚠️ El punto está en la cara oculta de la Luna (no visible).");
        return;
    }

    const px = cx + R * x;
    const py = cy - R * y;

    const vw = viewer.clientWidth;
    const vh = viewer.clientHeight;

    img.style.width = W + "px";
    img.style.height = H + "px";
    img.style.left = (vw / 2 - px) + "px";
    img.style.top = (vh / 2 - py) + "px";

    marker.style.display = "block";
    marker.style.left = (vw / 2 - 10) + "px";
    marker.style.top = (vh / 2 - 10) + "px";
}

// --- Cálculo inicial si quieres autoactualizar ---
updateMoonModal();
