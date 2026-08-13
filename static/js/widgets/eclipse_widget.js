// eclipse_widget.js
// Calcula el próximo eclipse solar visible desde la ubicación de la estación.
// 100% en cliente (sin backend, como moon.js): solo depende de `observer`,
// ya construido por conf_to_js.php a partir de lat/lon/elevación, y de
// astronomy.browser.js (Astronomy.SearchLocalSolarEclipse).

function eclipseKindLabel(kind) {
    // El dashboard solo distingue total/parcial; un eclipse anular no llega a
    // cubrir el disco solar por completo, así que se cuenta como parcial.
    return kind === 'total' ? 'Total' : 'Parcial';
}

function formatEclipseTime(astroTime) {
    if (!astroTime) return '--:--';
    return astroTime.date.toLocaleTimeString(navigator.language, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' });
}

function formatEclipseDate(astroTime) {
    return astroTime.date.toLocaleDateString(navigator.language, { day: 'numeric', month: 'short', year: 'numeric' });
}

function updateEclipseGraphic(eclipse) {
    const moonEl = document.getElementById('eclipse-moon');
    if (!moonEl) return;
    const sunCx = 50;
    const r = 26;
    // Separación de los centros: a mordisco 0 las circunferencias solo se
    // tocan por el borde; a mordisco 1 (total) quedan superpuestas del todo.
    // Se fuerza un mordisco mínimo visible aunque el oscurecimiento real sea
    // muy pequeño (eclipses parciales muy marginales), para que el icono
    // siempre se note como un eclipse y no como el Sol sin más.
    // No es una proyección astronómica exacta, solo una aproximación visual.
    const bite = Math.max(eclipse.obscuration, 0.12);
    const separation = 2 * r * (1 - bite);
    moonEl.setAttribute('cx', sunCx + separation);
    moonEl.classList.toggle('total', eclipse.kind === 'total');
}

function renderEclipseWidget(eclipse) {
    const dateEl = document.getElementById('eclipse-widget-date');
    const kindEl = document.getElementById('eclipse-widget-kind');
    const timesEl = document.getElementById('eclipse-widget-times');
    const altEl = document.getElementById('eclipse-widget-altitude');
    if (!dateEl || !kindEl || !timesEl || !altEl) return; // widget no presente en esta página

    dateEl.textContent = formatEclipseDate(eclipse.peak.time);

    const obscurationPercent = Math.round(eclipse.obscuration * 100);
    kindEl.textContent = `${eclipseKindLabel(eclipse.kind)} (${obscurationPercent}% oscurecido)`;

    timesEl.textContent = `${formatEclipseTime(eclipse.partial_begin.time)} – ${formatEclipseTime(eclipse.partial_end.time)}`;

    altEl.textContent = `Sol a ${eclipse.peak.altitude.toFixed(0)}° en el máximo`;

    updateEclipseGraphic(eclipse);
}

// SearchLocalSolarEclipse encuentra el primer eclipse con AL MENOS un extremo
// (inicio o fin) con el Sol sobre el horizonte, pero su máximo puede ocurrir
// igualmente con el Sol ya puesto (p.ej. un eclipse que empieza al atardecer).
// El widget solo quiere mostrar eclipses cuyo máximo sea realmente observable,
// así que se sigue buscando con NextLocalSolarEclipse hasta encontrar uno con
// altitud positiva en el pico.
const MAX_ECLIPSE_SEARCH_ATTEMPTS = 50;

function findNextVisibleLocalSolarEclipse(observer) {
    let eclipse = Astronomy.SearchLocalSolarEclipse(Astronomy.MakeTime(new Date()), observer);
    let attempts = 0;
    while (eclipse.peak.altitude <= 0 && attempts < MAX_ECLIPSE_SEARCH_ATTEMPTS) {
        eclipse = Astronomy.NextLocalSolarEclipse(eclipse.peak.time, observer);
        attempts++;
    }
    return eclipse.peak.altitude > 0 ? eclipse : null;
}

function updateEclipseWidget() {
    const dateEl = document.getElementById('eclipse-widget-date');
    const kindEl = document.getElementById('eclipse-widget-kind');
    if (!dateEl) return; // widget no presente en esta página

    if (typeof Astronomy === 'undefined' || typeof observer === 'undefined') {
        console.error('eclipse_widget.js: Astronomy/observer no disponibles; no se puede calcular el eclipse.');
        return;
    }

    try {
        const eclipse = findNextVisibleLocalSolarEclipse(observer);
        if (!eclipse) {
            if (kindEl) kindEl.textContent = 'Sin eclipses visibles próximamente';
            return;
        }
        renderEclipseWidget(eclipse);
    } catch (err) {
        console.error('eclipse_widget.js: error al calcular el próximo eclipse solar.', err);
    }
}

updateEclipseWidget();
// La fecha del próximo eclipse no cambia en escalas de minutos; se refresca
// solo de vez en cuando por si el dashboard queda abierto varias horas.
setInterval(updateEclipseWidget, 60 * 60 * 1000);
