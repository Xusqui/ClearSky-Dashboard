// moon_modal.js

// --- Importar el Catálogo oficial Lunar 100 desde el nuevo archivo ---
import { LUNAR_100_FEATURES } from './lunar_data.js';

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
    const phase = ill.phase;      // 0..1
    const fraction = ill.fraction;
    const angle = ill.angle;      // rad

    const isWaxing = angle < 0;
    const terminatorName = isWaxing ? "Amanecer lunar" : "Anochecer lunar";

    // Longitud subsolar (Ls)
    const subsolarDeg = 360 * phase - 180;

    // Terminador del amanecer = subsolar + 90°
    let terminatorDeg360 = (360 * phase - 90) % 360;
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

// --- Función para mostrar datos en el modal --

function updateMoonModal(){
    const date = new Date();
    const moonData = computeMoonData(date);

    // Obtener el valor de la longitud del terminador y redondearlo a 2 decimales
    const terminatorLong = moonData.terminatorVisible90.toFixed(2);
    // Obtener el valor original (numérico) para la comprobación del signo
    const rawTerminatorLong = moonData.terminatorVisible90;

    let formattedTerminator;

    if (rawTerminatorLong < 0) {
        // Es negativo: Oeste lunar (W)
        // Usamos Math.abs para obtener el valor absoluto (sin el signo negativo)
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
    const tolerance = 5;
    const featuresNearTerminator = LUNAR_100_FEATURES.filter(f => {
        let delta = Math.abs(f.long - moonData.terminatorVisible90);
        return delta <= tolerance;
    });

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

        // 4. UBICACIÓN (Coordenadas pequeñas, justo debajo del nombre)
        const locationInfo = document.createElement('small');
        // AÑADIMOS CLASE ESPECÍFICA
        locationInfo.classList.add('feature-coords-info');

        // Eliminamos todos los estilos inline (como display, opacity, margins)
        locationInfo.textContent = `[${formattedLat}, ${formattedLong}]`;

        // Ajusta el margen inferior para la separación, si es necesaria.
        //locationInfo.style.marginBottom = '10px';
        //locationInfo.style.opacity = '0.5';
        locationInfo.textContent = `[${formattedLat}, ${formattedLong}]`;

        // 5. NÚMERO DE CATÁLOGO (#Nº en grande)
        const value = document.createElement('p');
        value.classList.add('seeing-card-value');
        // Añadimos el '#' al texto del número
        value.textContent = `#${f.l100}`;

        // 6. COMENTARIO (Descripción)
        const desc = document.createElement('span');
        desc.classList.add('seeing-card-desc');
        desc.textContent = f.description;


        // 7. ENSAMBLAR LA TARJETA
        card.appendChild(title);
        card.appendChild(locationInfo); // Posición 2: Coordenadas
        card.appendChild(value);        // Posición 3: #Nº (Grande)
        card.appendChild(desc);         // Posición 4: Comentario
        container.appendChild(card);
    });
}

// Escribir el pie de página.
const moonFooter = document.getElementById('moon-footer');

// Obtener la fecha y hora actuales. La hora de referencia (CET) se basa en la hora actual del servidor.
const now = new Date();
const formattedDate = now.toLocaleDateString('es-ES', {
    day: '2-digit', month: 'long', year: 'numeric'
});
const formattedTime = now.toLocaleTimeString('es-ES', {
    hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
});

moonFooter.innerHTML = `
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

// --- Abrir modal al hacer click en la luna ---
document.querySelector('.moon-card').addEventListener('click', () => {
    document.getElementById('moonModal').style.display = 'block';
    updateMoonModal();
});

// --- Cerrar modal ---
document.getElementById('closeMoonModal').addEventListener('click', () => {
    document.getElementById('moonModal').style.display = 'none';
});

// --- Cerrar modal al hacer clic fuera del contenido ---
window.addEventListener('click', (event) => {
    const moonModal = document.getElementById('moonModal');

    // Comprueba si el objetivo del clic es el modal en sí mismo (el fondo oscuro)
    // y no un elemento dentro del 'modal-content'.
    if (event.target === moonModal) {
        moonModal.style.display = 'none';
    }
});
// --- Cálculo inicial si quieres autoactualizar ---
updateMoonModal();
