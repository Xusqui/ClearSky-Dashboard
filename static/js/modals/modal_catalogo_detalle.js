// modal_catalogo_detalle.js

// --- Mapeo de IDs del JSON a la CLAVE que espera la librería de efemérides (Astro) ---
const ASTRO_TARGET_MAP = {
    'mercurio': 'Mercury',
    'venus': 'Venus',
    'marte': 'Mars',
    'jupiter': 'Jupiter',
    'saturno': 'Saturn',
    'urano': 'Uranus',
    'neptuno': 'Neptune',
    'sol': 'Sun',
    'luna': 'Moon'
};

const solarSystemDetailModal = document.getElementById("solarSystemDetailModal");
const closeSolarSystemDetailModalButton = document.getElementById("closesolarSystemDetailModal");
const solarSystemDetailContent = document.getElementById('solarSystemDetailContent');

// -----------------------------------------------------
// 2.5 FUNCIONES DE CÁLCULO DE VISIBILIDAD (IMPLEMENTADO)
// -----------------------------------------------------

/**
 * Busca la próxima fecha y hora en que un cuerpo celeste alcanza su tránsito superior (elevación máxima).
 * Requiere la librería global 'Astro' (o la que uses, ej: Astronomy.js, Skyfield.js).
 *
 * @param {string} targetKey - La clave del cuerpo celeste (ej: 'Jupiter') según ASTRO_TARGET_MAP.
 * @returns {Promise<string>} - Una cadena de texto con la fecha y hora formateada.
 */
async function nextRiseTransitSet(body, startDate) {
    let latitude = parseFloat(LAT);
    let longitude = parseFloat(LON);
    let elevation = parseFloat(ELEV);
    let obs = new Astronomy.Observer(latitude, longitude, elevation);
    //Convertimos Date → Astronomy.Time
    const startAstro = Astronomy.MakeTime(startDate);
    //console.log("DEBUG: startAstro:", startAstro);
    let rise;
    let set;
    let transit;

    if (body === Astronomy.Body.Mercury || body === Astronomy.Body.Venus) {
        try {
            // El cálculo de tránsito en 'nextRiseTransitSet' con la librería astronomy-engine
            // requiere pasar el objeto Astronomy.Observer 'obs' para ser coherente con
            // SearchRiseSet. Si SearchTransit no acepta 'obs' en tu versión, podrías
            // tener que adaptar la llamada. Asumiendo la versión simple por ahora:
            const transitInfo = Astronomy.SearchTransit(body, startAstro);

            transit = transitInfo.peak;
            console.log("transit: ", transit);
        } catch (error) {
            console.warn(`Advertencia: Error al calcular tránsito para ${body || body}. Estableciendo como 'No disponible'.`, error);
            transit = "No disponible"; // Si el cálculo falla para Mercurio/Venus, se establece como "No disponible"
        }
    } else {
        // Para todos los demás cuerpos, se establece como "No disponible"
        transit = "No disponible";
    }

    try {
        // Al pasar el objeto 'body' (Astronomy.Body.X) directamente, podemos
        // usar la sobrecarga simple de SearchRiseSet/SearchTransit sin callbacks,
        // lo cual es mucho más estable.
        const rise = Astronomy.SearchRiseSet(body, obs, +1, startAstro, 365);
        const set = Astronomy.SearchRiseSet(body, obs, -1, startAstro, 365);
        return {
            rise: rise,
            set: set,
            transit: transit,
        };
    } catch (error) {
        // En caso de error interno (ej: cuerpo circumpolar o fallo de cálculo)
        console.error("Error de cálculo interno de efemérides para", body || body, ":", error);
        throw error; // Relanzamos el error para que el .catch de la promesa lo maneje
    }
}

/**
 * Abre el modal de detalle y renderiza toda la información de un cuerpo celeste.
 * (MODIFICADA para incluir la llamada asíncrona al cálculo de tránsito)
 * @param {Object} details - Objeto completo del cuerpo celeste (del JSON).
 */
function openSolarSystemDetailModal(details) {
    if (!solarSystemDetailModal || !solarSystemDetailContent || !details) return;

    // Obtener la clave de la librería Astro para el cálculo de efemérides
    const rawId = details.id;
    const orbTargetKey = ASTRO_TARGET_MAP[rawId.toLowerCase()]; // Clave de texto (ej: 'Jupiter')
    const bodyObject = details.englishName; //orbTargetKey ? Astronomy.Body[orbTargetKey] : null; // Objeto del cuerpo (ej: Astronomy.Body.Jupiter)
    const date = new Date();

    let nextObservationTimeText = "Calculando..."; // Inicializa como 'Calculando'

    // Formateo de las variables necesarias para el detalle (Mantenido)
    const commonName = details.name || "Sin Nombre";
    const meanRadius = details.meanRadius
    ? (details.meanRadius / 1000).toLocaleString("es-ES", { maximumFractionDigits: 0 })
    : "N/A";
    const isPlanetText = details.isPlanet ? "Sí" : "No";
    const imageUrl = `./static/solar_system/images/${details.id}.jpg`;

    solarSystemDetailContent.innerHTML = `
        <div class="solar-system-detail-header">
            <div class="image-wrapper">
                <img src="${imageUrl}" alt="${commonName}" class="solar-system-detail-image" onerror="this.onerror=null;this.src='./static/solar_system/images/placeholder.jpg';">
            </div>

            <div class="header-info">
                <h2 class="solar-system-detail-title">${commonName} (${details.englishName || "N/A"})</h2>
                <p class="solar-system-detail-bodytype">Tipo de Cuerpo: ${details.bodyType || "N/A"}</p>

                <div class="solar-system-detail-section">
                    <h3>Características Físicas</h3>

                    <div class="data-row"><span class="label">Radio Medio:</span> <span class="value">${meanRadius} km</span></div>
                    <div class="data-row"><span class="label">Densidad:</span> <span class="value">${details.density ? details.density.toFixed(3) : "N/A"} g/cm³</span></div>
                    <div class="data-row"><span class="label">Gravedad Superficial:</span> <span class="value gravity">${details.gravity ? details.gravity.toFixed(2) : "N/A"} m/s²</span></div>
                    <div class="data-row"><span class="label">Temperatura Media:</span> <span class="value">${details.avgTemp || "N/A"}°K</span></div>
                    <div class="data-row"><span class="label">Masa:</span> <span class="value">${formatExponent(details.mass)} kg</span></div>

                </div>
            </div>
        </div>

        <div class="solar-system-detail-body">

            <div class="solar-system-detail-section">
                <h3>🔭 Próxima Visibilidad Telescópica</h3>
                <div class="data-row"><span class="label">Fecha Salida:</span> <span class="value observation-time" id="nextRiseTime">Calculando...</span></div>
                <div class="data-row"><span class="label">Fecha Puesta:</span> <span class="value observation-time" id="nextSetTime">Calculando...</span></div>
                <div class="data-row"><span class="label">Fecha de Tránsito:</span> <span class="value observation-time" id="nextTransitTime">Calculando...</span></div>
                <p style="font-size: 0.8em; color: #888; margin-top: 5px;">Calculado al alcanzar el punto más alto de elevación (Tránsito Superior), hora local.</p>
            </div>
            <hr style="border-top: 1px dotted rgba(255, 255, 255, 0.1);">
            <div class="solar-system-detail-section">
                <h3>Datos Orbitales</h3>
                <div class="data-row"><span class="label">Período Orbital Sidéreo:</span> <span class="value">${details.sideralOrbit ? details.sideralOrbit.toLocaleString("es-ES", { maximumFractionDigits: 2 }) : "N/A"} días</span></div>
                <div class="data-row"><span class="label">Período de Rotación Sidéreo:</span> <span class="value">${details.sideralRotation ? details.sideralRotation.toLocaleString("es-ES", { maximumFractionDigits: 2 }) : "N/A"} horas</span></div>
                <div class="data-row"><span class="label">Inclinación Axial:</span> <span class="value">${details.axialTilt ? details.axialTilt.toLocaleString("es-ES", { maximumFractionDigits: 2 }) : "N/A"}°</span></div>
                <div class="data-row"><span class="label">Semieje Mayor:</span> <span class="value">${details.semimajorAxis ? details.semimajorAxis.toLocaleString("es-ES", { maximumFractionDigits: 0 }) : "N/A"} km</span></div>
            </div>

            <div class="solar-system-detail-section">
                <h3>Lunas y Descubrimiento</h3>
                <div class="data-row"><span class="label">Es Planeta:</span> <span class="value">${isPlanetText}</span></div>
                <div class="data-row"><span class="label">Lunas:</span> <span class="value">${listMoons(details.moons)}</span></div>
                <div class="data-row"><span class="label">Descubierto por:</span> <span class="value">${details.discoveredBy || "Desconocido"}</span></div>
                <div class="data-row"><span class="label">Fecha de Descubrimiento:</span> <span class="value">${details.discoveryDate || "N/A"}</span></div>
            </div>
        </div>`;

    solarSystemDetailModal.style.display = "block"; // Mostrar el modal

    // --- CÁLCULO ASÍNCRONO DEL TRÁNSITO ---
    let startDate = new Date();
    if (bodyObject) {
        nextRiseTransitSet(bodyObject, startDate) // Se asume que ahora pasas bodyObject
            .then((result) => {
            const riseElement = document.getElementById("nextRiseTime");
            const setElement = document.getElementById("nextSetTime");
            const transitElement = document.getElementById("nextTransitTime");
            let txtRise = "";
            let txtSet = "";
            let txtTransit = "";

            if (result.rise) {
                txtRise = result.rise.toLocaleString("es-ES", { hour12: false});
            } else {
                txtRise = "No Disponible";
            }

            if (result.set) {
                txtSet = result.set.toLocaleString("es-ES", { hour12: false});
            } else {
                txtSet = "No Disponible";
            }

            if (result.transit) {
                txtTransit = result.transit.toLocaleString("es-ES", { hour12: false });
            } else {
                txtTransit = "No Disponible";
            }
            riseElement.innerHTML = txtRise;
            setElement.innerHTML = txtSet;
            transitElement.innerHTML = txtTransit;

        })

        // 🚨 BLOQUE CATCH MODIFICADO PARA DEPURACIÓN 🚨
        /*.catch((error) => {
            // Mostrar el error completo en la consola
            console.error("🛑 ERROR EN EL CÁLCULO DE EFEMÉRIDES. Consulta el objeto de error a continuación.", error);

            const timeElement = document.getElementById("nextRiseTime");
            if (timeElement) {
                timeElement.innerHTML = "Error al obtener efemérides (Ver Consola 👆).";
            }
        }); */
    } else {
        const riseElement = document.getElementById("nextRiseTime");
        if (riseElement) {
            riseElement.innerHTML = "N/A (Cuerpo no mapeado para efemérides)";
        }
    }
}
// -----------------------------------------------------
// 4. LISTENERS DE EVENTOS (Mantenido)
// -----------------------------------------------------

// Listener para el botón "Sistema Solar" (Carga y muestra las tarjetas)
if (catalogoSolarButton && dataContainer) {
    catalogoSolarButton.addEventListener("click", async () => {
        dataContainer.innerHTML = "<p>Cargando datos del Sistema Solar...</p>";

        const bodies = await fetchSolarSystemData();

        if (bodies && bodies.length > 0) {
            const cardsHtml = bodies.map(createSolarSystemCard).join("");
            dataContainer.innerHTML = cardsHtml;
        } else if (bodies && bodies.length === 0) {
            dataContainer.innerHTML = "<p>El catálogo del Sistema Solar está vacío.</p>";
        } else {
            dataContainer.innerHTML =
                "<p>🛑 Error al cargar los datos. Verifica la ruta del archivo o la conexión.</p>";
        }
    });
}

// Listener DELEGADO para el clic en CUALQUIER tarjeta del catálogo
if (dataContainer) {
    dataContainer.addEventListener("click", (event) => {
        const card = event.target.closest(".clickable-card");

        if (card) {
            const bodyId = card.getAttribute("data-id");
            const selectedBody = solarSystemData.find((body) => body.id === bodyId);

            if (selectedBody) {
                openSolarSystemDetailModal(selectedBody);
            }
        }
    });
}

if (closeSolarSystemDetailModalButton) {
    closeSolarSystemDetailModalButton.addEventListener("click", () => {
        if (solarSystemDetailModal) {
            solarSystemDetailModal.style.display = "none";
        }
    });
}

window.addEventListener("click", (event) => {
    if (event.target === solarSystemDetailModal) {
        solarSystemDetailModal.style.display = "none";
    }
});
