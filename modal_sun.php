<?php
// modal_sun.php
?>
<!--*************************************************************
*************************** MODAL SOLAR *************************
****************************************************************-->
<!-- Modal oculto al inicio -->
<div id="solarModal" class="modal">
    <div class="modal-content">
        <button class="close" aria-label="Cerrar" id="closeSolarModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="infografia">
            <h1 class="seeing-modal-title">☀️ Datos Solares de Hoy</h1>
            <h2 class="seeing-group-title">Amanecer, Cénit y Ocaso</h2>
            <div class="bloque bloque-fixed-3">
                <div class="card">
                    <h3 class="seeing-card-title">🌅 Amanecer</h3>
                    <p class="seeing-card-value" id="sunriseTime">-</p>
                    <span class="seeing-card-desc">Hora local</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌞 Cénit solar</h3>
                    <p class="seeing-card-value" id="solarNoonTime">-</p>
                    <span class="seeing-card-desc">Hora local</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌇 Puesta de Sol</h3>
                    <p class="seeing-card-value" id="sunsetTime">-</p>
                    <span class="seeing-card-desc">Hora local</span>
                </div>
            </div>
            <h2 class="seeing-group-title">Crepúsculo Matutino</h2>
            <div class="bloque bloque-fixed-3">
                <div class="card">
                    <h3 class="seeing-card-title">🌑 Astronómico</h3>
                    <p class="seeing-card-value" id="astronomicalDawn">-</p>
                    <span class="seeing-card-desc">Inicio del crepúsculo astronómico</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌌 Náutico</h3>
                    <p class="seeing-card-value" id="nauticalDawn">-</p>
                    <span class="seeing-card-desc">Inicio del crepúsculo náutico</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌆 Civil</h3>
                    <p class="seeing-card-value" id="civilDawn">-</p>
                    <span class="seeing-card-desc">Inicio del crepúsculo civil</span>
                </div>
            </div>
            <h2 class="seeing-group-title">Crepúsculo Vespertino</h2>
            <div class="bloque bloque-fixed-3">
                <div class="card">
                    <h3 class="seeing-card-title">🌑 Astronómico</h3>
                    <p class="seeing-card-value" id="astronomicalDusk">-</p>
                    <span class="seeing-card-desc">Inicio del crepúsculo astronómico</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌌 Náutico</h3>
                    <p class="seeing-card-value" id="nauticalDusk">-</p>
                    <span class="seeing-card-desc">Inicio del crepúsculo náutico</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌆 Civil</h3>
                    <p class="seeing-card-value" id="civilDusk">-</p>
                    <span class="seeing-card-desc">Inicio del crepúsculo civil</span>
                </div>
            </div>
            <h2 class="seeing-group-title">Otros Datos</h2>
            <div class="bloque bloque-fixed-4">
                <div class="card">
                    <h3 class="seeing-card-title">🧭 Azimut Amanecer</h3>
                    <p class="seeing-card-value" id="sunriseAzimuth">-</p>
                    <span class="seeing-card-desc">desde el Norte</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🧭 Azimut Ocaso</h3>
                    <p class="seeing-card-value" id="sunsetAzimuth">-</p>
                    <span class="seeing-card-desc">desde el Norte</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">📈 Elevación solar máxima</h3>
                    <p class="seeing-card-value" id="maxElevation">-</p>
                    <span class="seeing-card-desc">sobre el horizonte</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🕓 Duración del día</h3>
                    <p class="seeing-card-value" id="dayLength">-</p>
                    <span class="seeing-card-desc">Horas totales de luz</span>
                </div>
            </div>
        </div>
    </div>
</div>
