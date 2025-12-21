/* modal_seeing.js */
document.addEventListener("DOMContentLoaded", function () {
    const widgetSeeing = document.getElementById("seeing");
    const modal = document.getElementById("seeingModal");
    const closeBtn = document.getElementById("closeSeeingModal");

    // Función auxiliar para formatear valores
    const setVal = (id, value, decimals = 1) => {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = (value !== undefined && value !== null)
                ? parseFloat(value).toFixed(decimals)
            : "-";
        }
    };

    function setGauge(svg, value) {
        const needle = svg.querySelector(".needle");
        const hub    = svg.querySelector(".needle-hub");
        const label  = svg.querySelector(".gauge-value");

        const clamped = Math.max(0, Math.min(100, value));
        const angle = -90 + (clamped * 180 / 100);

        const color = valueToColor(clamped);

        needle.style.transform = `rotate(${angle}deg)`;
        needle.style.fill = color;
        hub.style.fill = color;

        label.textContent = `${clamped}%`;
    }

    function valueToColor(value) {
        const v = Math.max(0, Math.min(100, value)) / 100;

        const r = Math.round(255 * (1 - v));
        const g = Math.round(180 * v);
        const b = 0;

        return `rgb(${r}, ${g}, ${b})`;
    }

    function actualizarModal() {
        fetch('./static/modules/widgets/get_astronomy_quality.php')
            .then(response => response.json())
            .then(data => {
            if (data.error) {
                console.error("Error al cargar calidad astronómica:", data.message);
                return;
            }

            // --- Estación ---
            setVal("st_temp", data.estacion?.temperatura);
            setVal("st_hum", data.estacion?.humedad, 0);
            setVal("st_wind", data.estacion?.viento);

            // --- Luna ---
            setVal("luna_alt", data.luna?.altura);
            setVal("luna_pct", data.luna?.iluminacion_pct, 0);
            setVal("luna_bright", data.luna?.impacto_brillo, 0);

            // --- Viento Altura ---
            setVal("v_10m", data.viento_altura?.["10m"]);
            setVal("v_80m", data.viento_altura?.["80m"]);
            setVal("v_180m", data.viento_altura?.["180m"]);

            // --- Nubes ---
            setVal("n_low", data.nubes?.bajas, 0);
            setVal("n_mid", data.nubes?.medias, 0);
            setVal("n_high", data.nubes?.altas, 0);

            // --- Seeing ---
            setVal("s_plan", data.seeing?.planetario?.arcsec, 2);
            setVal("s_deep", data.seeing?.cielo_profundo?.arcsec, 2);

            // --- Calidades Finales (Tarjetas) ---
            //setVal("c_visual", data.calidad?.visual, 0);
            //setVal("c_astrofoto", data.calidad?.astrofoto, 0);
            // --- Gauges ---
            document.querySelectorAll(".gauge").forEach(gauge => {
                if (gauge.dataset.type === "visual") {
                    setGauge(gauge, data.calidad?.visual ?? 0);
                }
                if (gauge.dataset.type === "astro") {
                    setGauge(gauge, data.calidad?.astrofoto ?? 0);
                }
            });


        })
            .catch(err => console.error("Error al obtener datos del modal:", err));
    }

    // Abrir modal
    widgetSeeing.addEventListener("click", () => {
        modal.style.display = "block";
        actualizarModal();
    });

    // Cerrar modal
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Cerrar al hacer click fuera
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});
