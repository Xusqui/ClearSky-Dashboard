<?php
// modal_seeing.php
?>
<!--************************************************************
    ******************** DATOS DEL SEEING **********************
    *********************** M O D A L **************************
    ************************************************************ -->
<div id="seeingModal" class="modal">
    <div class="modal-content">
        <button class="close" aria-label="Cerrar" id="closeSeeingModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="infografia">
            <h1 class="seeing-modal-title">🌠 Datos del Seeing Astronómico</h1>
            <h2 class="seeing-group-title">Datos de Superficie</h2>
            <div class="bloque bloque-fixed-3"> <div class="card">
                <h3 class="seeing-card-title">🌡️ Variación térmica</h3>
                <p class="seeing-card-value" id="t8h">-</p>
                <span class="seeing-card-desc">ºC (Últimas 8h)</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">💧 Variación de humedad</h3>
                    <p class="seeing-card-value" id="h8h">-</p>
                    <span class="seeing-card-desc">% (Últimas 8h)</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌬️ Viento actual</h3>
                    <p class="seeing-card-value" id="wnow">-</p>
                    <span class="seeing-card-desc">Km/h</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌬️ Racha de viento</h3>
                    <p class="seeing-card-value" id="gnow">-</p>
                    <span class="seeing-card-desc">Km/h</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">📉 Variación de presión</h3>
                    <p class="seeing-card-value" id="p8h">-</p>
                    <span class="seeing-card-desc">hPa (Últimas 8h)</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">☀️ Radiación solar</h3>
                    <p class="seeing-card-value" id="rs">-</p>
                    <span class="seeing-card-desc">W/m²</span>
                </div>
            </div>
            <h2 class="seeing-group-title">Datos en Altura</h2>
            <div class="bloque bloque-fixed-3"> <div class="card">
                <h3 class="seeing-card-title">🌀 Temp. a 500 hPa</h3>
                <p class="seeing-card-value" id="t500">-</p>
                <span class="seeing-card-desc">ºC</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌀 Temp. a 300 hPa</h3>
                    <p class="seeing-card-value" id="t300">-</p>
                    <span class="seeing-card-desc">ºC</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">📊 DeltaT</h3>
                    <p class="seeing-card-value" id="deltaT">-</p>
                    <span class="seeing-card-desc">(Estabilidad)</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">💨 Viento a 500 hPa</h3>
                    <p class="seeing-card-value" id="w500">-</p>
                    <span class="seeing-card-desc">Km/h</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">💨 Viento a 300 hPa</h3>
                    <p class="seeing-card-value" id="w300">-</p>
                    <span class="seeing-card-desc">Km/h</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌪️ Shear vertical</h3>
                    <p class="seeing-card-value" id="shear">-</p>
                    <span class="seeing-card-desc">(Turbulencia)</span>
                </div>
            </div>
            <h2 class="seeing-group-title">Cobertura de Nubes</h2>
            <div class="bloque"> <div class="card">
                <h3 class="seeing-card-title">☁️ Nubes bajas</h3>
                <p class="seeing-card-value" id="clow">-</p>
                <span class="seeing-card-desc">% Cobertura</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌥️ Nubes medias</h3>
                    <p class="seeing-card-value" id="cmid">-</p>
                    <span class="seeing-card-desc">% Cobertura</span>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌤️ Nubes altas</h3>
                    <p class="seeing-card-value" id="chigh">-</p>
                    <span class="seeing-card-desc">% Cobertura</span>
                </div>
            </div>
            <div class="footer">
                <p class="seeing-result">
                    👁️ Seeing: <strong><span id="seeingtext">-</span></strong>
                </p>
                <p class="seeing-attribution">
                    Datos en altura y nubes de <a href="https://open-meteo.com/" target="_blank" rel="noopener noreferrer">Open-Meteo</a>
                </p>
            </div>
        </div>
    </div>
</div>
