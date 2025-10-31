document.addEventListener("DOMContentLoaded", function () {
    const pwsInfo = document.getElementById("PWS_info");
    const dialog = document.getElementById("pws-info-dialog");
    const closeBtn = document.getElementById("pws-info-dialog-close");

    // --- Variables para guardar el estado del mapa ---
    let pwsMap = null;      // La instancia del mapa
    let pwsMarker = null;   // La instancia del marcador
    let mapCoordinates = null; // Las coordenadas [lon, lat]

    // --- Media Query para detectar el modo oscuro ---
    // Lo creamos una sola vez aquí
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    /**
     * Función simple para obtener la URL del estilo correcto.
     * @param {boolean} isDarkMode - true si el modo oscuro está activo.
     * @returns {string} - La URL del estilo del mapa.
     */
    function getMapStyle(isDarkMode) {
        return isDarkMode
            ? 'https://tiles.openfreemap.org/styles/positron' // Noche (Oscuro)
        : 'https://tiles.openfreemap.org/styles/liberty';  // Día (Claro)
    }

    // --- Abrir modal al hacer click en el widget ---
    pwsInfo.addEventListener("click", function () {
        // 1. Hacemos visible el modal
        dialog.style.display = "block";

        // 2. Comprobamos si el mapa YA ha sido inicializado
        if (!pwsMap) {

            // Leemos las coordenadas del HTML y las guardamos
            const lat = parseFloat(dialog.getAttribute('data-lat'));
            const lon = parseFloat(dialog.getAttribute('data-lon'));
            mapCoordinates = [lon, lat]; // Guardamos [lon, lat]

            // Obtenemos el estilo actual al momento de crear
            const currentStyle = getMapStyle(mediaQuery.matches);

            // 3. Inicializamos el mapa
            pwsMap = new maplibregl.Map({
                style: currentStyle,
                center: mapCoordinates,
                zoom: 14,
                container: 'pws-map-container',
                interactive: true
            });

            // 4. Creamos y GUARDAMOS el marcador
            pwsMarker = new maplibregl.Marker({ color: '#2a7fff' })
                .setLngLat(mapCoordinates)
                .addTo(pwsMap);

            pwsMap.on('load', function() {
                pwsMap.resize();
            });
        }
    });

    // --- ¡AQUÍ ESTÁ LA MAGIA! ---
    // Escuchamos los cambios en la Media Query (cambio de tema claro/oscuro)
    mediaQuery.addEventListener('change', (event) => {
        // Si el mapa no se ha creado todavía (el modal nunca se abrió),
        // no hacemos nada.
        if (!pwsMap) {
            return;
        }

        // event.matches es 'true' si el nuevo estado es oscuro
        const newStyle = getMapStyle(event.matches);

        // 1. Aplicamos el nuevo estilo al mapa existente
        pwsMap.setStyle(newStyle);

        // 2. ¡IMPORTANTE! setStyle() borra los marcadores.
        //    Tenemos que esperar a que el nuevo estilo cargue
        //    y volver a añadir el marcador que guardamos.
        pwsMap.on('style.load', () => {
            if (pwsMarker) {
                pwsMarker.addTo(pwsMap);
            }
        });
    });

    // --- Lógica de cierre (sin cambios) ---
    closeBtn.addEventListener("click", function () {
        dialog.style.display = "none";
    });

    window.addEventListener("click", function (event) {
        if (event.target === dialog) {
            dialog.style.display = "none";
        }
    });
});
