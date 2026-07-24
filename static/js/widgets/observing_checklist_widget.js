/* /static/js/widgets/observing_checklist_widget.js */
// Resume en una tarjeta lo que hoy exige mirar 5-6 widgets distintos:
// seeing, viento, nubosidad, humedad y fase lunar, cada uno como una
// insignia circular con color de estado (verde/ámbar/rojo), más un veredicto
// 👍/👎 agregado arriba.
// Reutiliza los endpoints que ya existen (get_seeing.php, get_humidity_data.php)
// en vez de duplicar sus cálculos, y calcula la fase lunar en cliente con
// SunCalc (ya cargado globalmente, misma fuente que usa moon.js).
function updateObservingChecklist() {
    const listEl = document.getElementById('checklist-list');
    const verdictEl = document.getElementById('checklist-verdict');
    if (!listEl) return;

    function setItem(key, statusClass, text) {
        const item = listEl.querySelector(`[data-key="${key}"]`);
        if (!item) return;
        const circle = item.querySelector('.checklist-circle');
        const valueEl = item.querySelector('.checklist-value');
        if (circle) {
            circle.classList.remove('status-ok', 'status-warn', 'status-bad');
            circle.classList.add(statusClass);
        }
        if (valueEl) valueEl.textContent = text;
    }

    function setVerdict(statuses) {
        if (!verdictEl) return;
        // Binario: si algún indicador está en rojo, veredicto negativo;
        // si no hay ninguno en rojo (aunque haya ámbares), veredicto positivo.
        const hasBad = statuses.includes('status-bad');
        verdictEl.classList.remove('status-ok', 'status-bad');
        if (hasBad) {
            verdictEl.textContent = '👎';
            verdictEl.classList.add('status-bad');
        } else {
            verdictEl.textContent = '👍';
            verdictEl.classList.add('status-ok');
        }
    }

    // Umbrales tomados del propio cálculo de seeing (get_seeing.php), no
    // inventados: wind300<40/<80, shear<20/<40, deltaT<15/<30 usan el mismo
    // patrón de 3 franjas; aquí se aplica igual a viento en superficie y
    // nubosidad ponderada (cloud_index, 0-100).
    function classifySeeing(estrellas) {
        if (estrellas >= 2) return 'status-ok';
        if (estrellas >= 1) return 'status-warn';
        return 'status-bad';
    }

    function classifyWind(kmh) {
        if (kmh < 10) return 'status-ok';
        if (kmh < 20) return 'status-warn';
        return 'status-bad';
    }

    function classifyClouds(cloudIndex) {
        if (cloudIndex < 10) return 'status-ok';
        if (cloudIndex < 40) return 'status-warn';
        return 'status-bad';
    }

    function classifyHumidity(state) {
        // 'comfortable' -> sin riesgo; 'dry'/'humid' -> aviso (la humedad alta
        // implica riesgo de condensación en la óptica, pero no descarta observar).
        return state === 'comfortable' ? 'status-ok' : 'status-warn';
    }

    function classifyMoon(fraction) {
        // fraction: 0 = luna nueva, 1 = luna llena (SunCalc). Más iluminación
        // = más brillo de fondo de cielo = peor para cielo profundo.
        if (fraction < 0.25) return 'status-ok';
        if (fraction < 0.6) return 'status-warn';
        return 'status-bad';
    }

    // Fase lunar: se calcula en el cliente, sin esperar a los fetch, pero se
    // incluye en el veredicto agregado igual que el resto de indicadores.
    let moonStatus = null;
    if (typeof SunCalc !== 'undefined') {
        const moon = SunCalc.getMoonIllumination(new Date());
        moonStatus = classifyMoon(moon.fraction);
        setItem('moon', moonStatus, `${Math.round(moon.fraction * 100)}%`);
    }

    Promise.all([
        fetch('./static/modules/widgets/get_seeing.php').then(r => r.json()),
        fetch('./static/modules/widgets/get_humidity_data.php').then(r => r.json())
    ]).then(([seeingData, humidityData]) => {
        const statuses = [];
        if (moonStatus) statuses.push(moonStatus);

        if (!seeingData.error) {
            const seeingStatus = classifySeeing(seeingData.estrellas);
            setItem('seeing', seeingStatus, seeingData.seeing);
            statuses.push(seeingStatus);

            const wind = seeingData.detalles ? seeingData.detalles.vientoActual : null;
            if (typeof wind === 'number') {
                const windStatus = classifyWind(wind);
                setItem('wind', windStatus, `${wind.toFixed(0)} km/h`);
                statuses.push(windStatus);
            }

            const cloudIndex = seeingData.detalles ? seeingData.detalles.cloud_index : null;
            if (typeof cloudIndex === 'number') {
                const cloudStatus = classifyClouds(cloudIndex);
                setItem('clouds', cloudStatus, `${cloudIndex.toFixed(0)}%`);
                statuses.push(cloudStatus);
            }
        } else {
            console.error('Checklist: error en get_seeing.php', seeingData.message);
        }

        if (!humidityData.error) {
            const humidityStatus = classifyHumidity(humidityData.state);
            setItem('humidity', humidityStatus, `${humidityData.humidity}%`);
            statuses.push(humidityStatus);
        } else {
            console.error('Checklist: error en get_humidity_data.php', humidityData.message);
        }

        setVerdict(statuses);
    }).catch(err => console.error('Error al actualizar el checklist de observación:', err));
}

// Primera actualización inmediata
updateObservingChecklist();
