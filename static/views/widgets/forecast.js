// ==========================
// 🔧 CONFIGURACIÓN
// ==========================
const OPENMETEO_URL = `https://api.open-meteo.com/v1/forecast?latitude=${LAT}&longitude=${LON}&hourly=temperature_2m,temperature_500hPa,temperature_300hPa,wind_speed_2m,wind_speed_500hPa,wind_speed_300hPa,relative_humidity_2m,pressure_msl,cloud_cover_low,cloud_cover_mid,cloud_cover_high,weathercode&timezone=auto&forecast_hours=7`;

// ==========================
// 🌞 FUNCIÓN AUXILIAR DÍA/NOCHE
// ==========================
function esNoche(fecha, lat, lon) {
    const times = SunCalc.getTimes(fecha, lat, lon);
    return fecha < times.sunrise || fecha > times.sunset;
}

// ========================== 
// 🎨 ICONOS CSS 
// ========================== 
function iconoWeatherCodeCSS(code, esNocheFlag) { 
    // Si es de noche, sustituimos los iconos diurnos por los nocturnos equivalentes
    if (esNocheFlag) {
        if ([0].includes(code)) return "var(--icon-clear-night)";
        if ([1].includes(code)) return "var(--icon-fair-mostly-clear-night)";
        if ([2].includes(code)) return "var(--icon-partly-cloudy-night)";
        if ([3].includes(code)) return "var(--icon-mostly-cloudy-night)";
    }

    // Iconos diurnos por defecto
    if ([0].includes(code)) return "var(--icon-sunny-day)"; 
    if ([1].includes(code)) return "var(--icon-fair-mostly-sunny-day)";
    if ([2].includes(code)) return "var(--icon-partly-cloudy-day)"; 
    if ([3].includes(code)) return "var(--icon-mostly-cloudy-day)"; 
    if ([45,48].includes(code)) return "var(--icon-foggy)"; 
    if ([51,53,55,61,63,80,81].includes(code)) return "var(--icon-rain)"; 
    if ([65,82].includes(code)) return "var(--icon-heavy-rain)"; 
    if ([71,73,75].includes(code)) return "var(--icon-snow)"; 
    if ([95,99].includes(code)) return "var(--icon-thunderstorms)"; 
    return "var(--icon-not-available)"; 
}

// ==========================
// 📝 TRADUCCIÓN WeatherCode
// ==========================
function traducirWeatherCode(code) {
    const condiciones = {
        0: "Despejado",1: "Mayormente despejado",2: "Parcialmente nublado",3: "Nublado",
        45: "Niebla",48: "Niebla con escarcha",
        51: "Llovizna ligera",53: "Llovizna moderada",55: "Llovizna intensa",
        61: "Lluvia ligera",63: "Lluvia moderada",65: "Lluvia intensa",
        71: "Nieve ligera",73: "Nieve moderada",75: "Nieve intensa",
        80: "Chubascos ligeros",81: "Chubascos moderados",82: "Chubascos intensos",
        95: "Tormenta",99: "Tormenta fuerte"
    };
    return condiciones[code] || "Desconocido";
}

// ==========================
// 🌠 FUNCIÓN DE CÁLCULO DEL SEEING POR HORA
// ==========================
function calcularSeeingHora(h) {
    const vientoActual  = h.viento2m;
    const rachaActual   = h.viento2m;
    const shear  = Math.abs(h.wind300 - h.wind500);
    const deltaT = Math.abs(h.temp500 - h.temp300);
    let puntos_base = 0;
    puntos_base += 5; puntos_base += 5; puntos_base += 5;
    puntos_base += (vientoActual < 10) ? 5 : ((vientoActual < 20) ? 3 : 1);
    puntos_base += (rachaActual < 15) ? 5 : ((rachaActual < 25) ? 3 : 1);
    puntos_base += (h.wind300 < 40) ? 5 : ((h.wind300 < 80) ? 3 : 1);
    puntos_base += (shear < 20) ? 5 : ((shear < 40) ? 3 : 1);
    puntos_base += (deltaT < 15) ? 5 : ((deltaT < 30) ? 3 : 1);
    const low  = h.nubesBajas || 0;
    const mid  = h.nubesMedias || 0;
    const high = h.nubesAltas || 0;
    let factor_nubes = 1 - ((low*0.5 + mid*0.7 + high*1.0)/100);
    factor_nubes = Math.max(0, Math.min(1, factor_nubes));
    const puntos_final = puntos_base * factor_nubes;
    let seeing, point;
    if (puntos_final < 5) { seeing="Nulo"; point=0; }
    else if (puntos_final < 15) { seeing="Pobre"; point=0.5; }
    else if (puntos_final < 25) { seeing="Regular"; point=1; }
    else if (puntos_final < 32) { seeing="Bueno"; point=2; }
    else if (puntos_final < 40) { seeing="Muy bueno"; point=2.5; }
    else { seeing="Excelente"; point=3; }
    return { seeing, point, puntos_final: Math.round(puntos_final*10)/10 };
}

// ==========================
// 🌤️ FUNCIÓN PRINCIPAL DE PREVISIÓN
// ==========================
async function actualizarForecast() {
    try {
        const res = await fetch(OPENMETEO_URL);
        const data = await res.json();

        if (!data.hourly || !data.hourly.time) {
            console.error("Estructura de datos inesperada:", data);
            return;
        }

        const totalHoras = data.hourly.time.length;
        const inicio = Math.max(totalHoras - 6, 0);
        const forecastHTML = [];

        for (let i = inicio; i < totalHoras; i++) {
            const fechaHora = new Date(data.hourly.time[i]);
            const hora = fechaHora.toLocaleTimeString("es-ES", {hour:"2-digit",minute:"2-digit"});
            const h = {
                temp2m: data.hourly.temperature_2m[i],
                humedad: data.hourly.relative_humidity_2m[i],
                presion: data.hourly.pressure_msl[i] || 1013,
                viento2m: data.hourly.wind_speed_2m[i],
                temp500: data.hourly.temperature_500hPa[i],
                temp300: data.hourly.temperature_300hPa[i],
                wind500: data.hourly.wind_speed_500hPa[i],
                wind300: data.hourly.wind_speed_300hPa[i],
                nubesBajas: data.hourly.cloud_cover_low[i],
                nubesMedias: data.hourly.cloud_cover_mid[i],
                nubesAltas: data.hourly.cloud_cover_high[i],
                weathercode: data.hourly.weathercode[i],
                hora
            };

            // 🌙 Detectar si esta hora es nocturna
            const noche = esNoche(fechaHora, LAT, LON);

            const seeingHora = calcularSeeingHora(h);
            const icon = iconoWeatherCodeCSS(h.weathercode, noche);
            const condicion = traducirWeatherCode(h.weathercode);

            forecastHTML.push(`
            <div class="forecast-card">
                <div class="forecast-hour">${h.hora}</div>
                <div class="forecast-icon" style="background-image: ${icon};"></div>
                <div class="forecast-temp">${h.temp2m}°C</div>
                <div class="forecast-desc">${condicion}</div>
                <div class="forecast-seeing">
                    <strong>Seeing:</strong> ${seeingHora.seeing} (${seeingHora.puntos_final})
                </div>
            </div>`);
        }

        document.getElementById("forecast").innerHTML = forecastHTML.join("");

    } catch(err) {
        console.error("Error cargando datos:", err);
    }
}

// ==========================
// 🕒 ACTUALIZACIÓN PERIÓDICA
// ==========================
actualizarForecast();
setInterval(actualizarForecast, 3600000);
