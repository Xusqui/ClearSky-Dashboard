/* /static/js/widgets/observing_checklist_widget.js */
// Resume en una tarjeta lo que hoy exige mirar 5-6 widgets distintos:
// seeing, viento, nubosidad, humedad y fase lunar, cada uno como una
// insignia circular con color de estado (verde/ámbar/rojo), más un veredicto
// 👍/👎 agregado arriba. Al pasar el ratón (o hacer foco/tap) sobre cada
// insignia se muestra una tarjeta explicando por qué está en ese estado.
// Reutiliza los endpoints que ya existen (get_seeing.php, get_humidity_data.php)
// en vez de duplicar sus cálculos, y calcula la fase lunar en cliente con
// SunCalc (ya cargado globalmente, misma fuente que usa moon.js).
function updateObservingChecklist() {
    const listEl = document.getElementById('checklist-list');
    const verdictEl = document.getElementById('checklist-verdict');
    if (!listEl) return;

    function setItem(key, statusClass, text, reason) {
        const item = listEl.querySelector(`[data-key="${key}"]`);
        if (!item) return;
        const circle = item.querySelector('.checklist-circle');
        const valueEl = item.querySelector('.checklist-value');
        const tooltipEl = item.querySelector('.checklist-tooltip');
        if (circle) {
            circle.classList.remove('status-ok', 'status-warn', 'status-bad');
            circle.classList.add(statusClass);
        }
        if (valueEl) valueEl.textContent = text;
        if (tooltipEl) {
            tooltipEl.classList.remove('status-ok', 'status-warn', 'status-bad');
            tooltipEl.classList.add(statusClass);
            tooltipEl.textContent = reason || '';
        }
    }

    function setVerdict(items) {
        if (!verdictEl) return;
        const wrap = verdictEl.closest('.checklist-verdict-wrap');
        const tooltipEl = wrap ? wrap.querySelector('.checklist-tooltip') : null;
        // Binario: si algún indicador está en rojo, veredicto negativo;
        // si no hay ninguno en rojo (aunque haya ámbares), veredicto positivo.
        const bad = items.filter(i => i.status === 'status-bad').map(i => i.label);
        const warn = items.filter(i => i.status === 'status-warn').map(i => i.label);

        verdictEl.classList.remove('status-ok', 'status-bad');
        let reason;
        let reasonStatus;
        if (bad.length) {
            verdictEl.textContent = '👎';
            verdictEl.classList.add('status-bad');
            reasonStatus = 'status-bad';
            reason = `En contra: ${bad.join(', ')}.`;
            if (warn.length) reason += ` También en aviso: ${warn.join(', ')}.`;
        } else {
            verdictEl.textContent = '👍';
            verdictEl.classList.add('status-ok');
            if (warn.length) {
                reasonStatus = 'status-warn';
                reason = `Condiciones favorables, con aviso en: ${warn.join(', ')}.`;
            } else {
                reasonStatus = 'status-ok';
                reason = 'Todas las condiciones son favorables para observar.';
            }
        }
        if (tooltipEl) {
            tooltipEl.classList.remove('status-ok', 'status-warn', 'status-bad');
            tooltipEl.classList.add(reasonStatus);
            tooltipEl.textContent = reason;
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

    // Textos explicativos para la tarjeta que aparece al pasar el ratón sobre
    // cada insignia. Se construyen a partir de los mismos valores/umbrales
    // usados para clasificar el estado, para que el texto nunca contradiga
    // al color mostrado.
    function reasonSeeing(seeingData, status) {
        const d = seeingData.detalles || {};
        const partes = [];
        if (typeof d.vientoActual === 'number') partes.push(`viento ${d.vientoActual.toFixed(0)} km/h`);
        if (typeof d.wind300 === 'number') partes.push(`viento en altura ${d.wind300.toFixed(0)} km/h`);
        if (typeof d.shear === 'number') partes.push(`cizalladura ${d.shear.toFixed(0)} km/h`);
        if (typeof d.deltaT === 'number') partes.push(`ΔT ${d.deltaT.toFixed(1)}°C`);
        if (typeof d.cloud_index === 'number') partes.push(`nubosidad ${d.cloud_index.toFixed(0)}%`);
        const detalle = partes.length ? ` (${partes.join(', ')})` : '';
        if (status === 'status-ok') return `Seeing ${seeingData.seeing.toLowerCase()}${detalle}: buena estabilidad atmosférica y cielo despejado.`;
        if (status === 'status-warn') return `Seeing ${seeingData.seeing.toLowerCase()}${detalle}: alguno de los factores en altura o la nubosidad no es óptimo.`;
        return `Seeing ${seeingData.seeing.toLowerCase()}${detalle}: viento en altura, cizalladura, ΔT o nubosidad muy desfavorables.`;
    }

    function reasonWind(kmh, status) {
        if (status === 'status-ok') return `Viento en calma (${kmh.toFixed(0)} km/h): buena estabilidad para el equipo, sin vibraciones esperables.`;
        if (status === 'status-warn') return `Viento moderado (${kmh.toFixed(0)} km/h): puede introducir pequeñas vibraciones, sobre todo con focales largas.`;
        return `Viento fuerte (${kmh.toFixed(0)} km/h): alto riesgo de vibraciones, mala colimación y dificultad para guiar.`;
    }

    function reasonClouds(cloudIndex, status) {
        if (status === 'status-ok') return `Cielo prácticamente despejado (nubosidad ponderada ${cloudIndex.toFixed(0)}%).`;
        if (status === 'status-warn') return `Nubosidad parcial (${cloudIndex.toFixed(0)}% ponderado): puede tapar zonas del cielo de forma intermitente.`;
        return `Nubosidad alta (${cloudIndex.toFixed(0)}% ponderado): cielo mayoritariamente cubierto, observación muy limitada.`;
    }

    function reasonHumidity(humidityData, status) {
        const hum = humidityData.humidity;
        const dew = humidityData.dew;
        if (status === 'status-ok') return `Humedad confortable (${hum}%, punto de rocío ${dew}°C): bajo riesgo de condensación en la óptica.`;
        if (humidityData.state === 'dry') return `Aire seco (${hum}%, punto de rocío ${dew}°C): sin riesgo de condensación, aunque puede favorecer polvo y estática.`;
        return `Humedad alta (${hum}%, punto de rocío ${dew}°C): riesgo de condensación (rocío) en el tubo, la lente y los oculares.`;
    }

    function reasonMoon(fraction, status) {
        const pct = Math.round(fraction * 100);
        if (status === 'status-ok') return `Luna con poca iluminación (${pct}%): mínima interferencia en objetos de cielo profundo.`;
        if (status === 'status-warn') return `Luna con iluminación moderada (${pct}%): aclara el fondo de cielo y reduce el contraste en objetos débiles.`;
        return `Luna muy iluminada (${pct}%): fondo de cielo brillante, poco recomendable para cielo profundo (mejor para Luna/planetas).`;
    }

    // Fase lunar: se calcula en el cliente, sin esperar a los fetch, pero se
    // incluye en el veredicto agregado igual que el resto de indicadores.
    let moonItem = null;
    if (typeof SunCalc !== 'undefined') {
        const moon = SunCalc.getMoonIllumination(new Date());
        const moonStatus = classifyMoon(moon.fraction);
        setItem('moon', moonStatus, `${Math.round(moon.fraction * 100)}%`, reasonMoon(moon.fraction, moonStatus));
        moonItem = { key: 'moon', label: 'Luna', status: moonStatus };
    }

    Promise.all([
        fetch('./static/modules/widgets/get_seeing.php').then(r => r.json()),
        fetch('./static/modules/widgets/get_humidity_data.php').then(r => r.json())
    ]).then(([seeingData, humidityData]) => {
        const items = [];
        if (moonItem) items.push(moonItem);

        if (!seeingData.error) {
            const seeingStatus = classifySeeing(seeingData.estrellas);
            setItem('seeing', seeingStatus, seeingData.seeing, reasonSeeing(seeingData, seeingStatus));
            items.push({ key: 'seeing', label: 'Seeing', status: seeingStatus });

            const wind = seeingData.detalles ? seeingData.detalles.vientoActual : null;
            if (typeof wind === 'number') {
                const windStatus = classifyWind(wind);
                setItem('wind', windStatus, `${wind.toFixed(0)} km/h`, reasonWind(wind, windStatus));
                items.push({ key: 'wind', label: 'Viento', status: windStatus });
            }

            const cloudIndex = seeingData.detalles ? seeingData.detalles.cloud_index : null;
            if (typeof cloudIndex === 'number') {
                const cloudStatus = classifyClouds(cloudIndex);
                setItem('clouds', cloudStatus, `${cloudIndex.toFixed(0)}%`, reasonClouds(cloudIndex, cloudStatus));
                items.push({ key: 'clouds', label: 'Nubosidad', status: cloudStatus });
            }
        } else {
            console.error('Checklist: error en get_seeing.php', seeingData.message);
        }

        if (!humidityData.error) {
            const humidityStatus = classifyHumidity(humidityData.state);
            setItem('humidity', humidityStatus, `${humidityData.humidity}%`, reasonHumidity(humidityData, humidityStatus));
            items.push({ key: 'humidity', label: 'Humedad', status: humidityStatus });
        } else {
            console.error('Checklist: error en get_humidity_data.php', humidityData.message);
        }

        setVerdict(items);
    }).catch(err => console.error('Error al actualizar el checklist de observación:', err));
}

// Soporte táctil: como no hay :hover en móviles, un tap abre la tarjeta y
// otro tap fuera la cierra (además de :focus-within vía tabindex="0").
(function setupChecklistTooltipTaps() {
    document.addEventListener('click', (e) => {
        const openTarget = e.target.closest('.checklist-badge, .checklist-verdict-wrap');
        document.querySelectorAll('.checklist-badge.tooltip-open, .checklist-verdict-wrap.tooltip-open').forEach(el => {
            if (el !== openTarget) el.classList.remove('tooltip-open');
        });
        if (openTarget) openTarget.classList.toggle('tooltip-open');
    });
})();

// Primera actualización inmediata
updateObservingChecklist();
