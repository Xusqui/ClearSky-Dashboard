<?php
// modal_seeing.php
?>
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
            <h1 class="seeing-modal-title">🔭 Calidad Astronómica</h1>

            <h2 class="seeing-group-title">Estación Local</h2>
            <div class="bloque bloque-fixed-3">
                <div class="card">
                    <h3 class="seeing-card-title">🌡️ Temperatura</h3>
                    <p class="seeing-card-value"><span id="st_temp">-</span><span class="unit">ºC</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">💧 Humedad</h3>
                    <p class="seeing-card-value"><span id="st_hum">-</span><span class="unit">%</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌬️ Viento</h3>
                    <p class="seeing-card-value"><span id="st_wind">-</span><span class="unit">Km/h</span></p>
                </div>
            </div>

            <h2 class="seeing-group-title">Estado de la Luna</h2>
            <div class="bloque bloque-fixed-3">
                <div class="card">
                    <h3 class="seeing-card-title">📏 Altura</h3>
                    <p class="seeing-card-value"><span id="luna_alt">-</span><span class="unit">º</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">💡 Iluminación</h3>
                    <p class="seeing-card-value"><span id="luna_pct">-</span><span class="unit">%</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">✨ Brillo</h3>
                    <p class="seeing-card-value" id="luna_bright">-</p>
                    <span class="seeing-card-desc">Impacto</span>
                </div>
            </div>

            <h2 class="seeing-group-title">Viento en Altura</h2>
            <div class="bloque bloque-fixed-3">
                <div class="card">
                    <h3 class="seeing-card-title">💨 A 10m</h3>
                    <p class="seeing-card-value"><span id="v_10m">-</span><span class="unit">Km/h</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">💨 A 80m</h3>
                    <p class="seeing-card-value"><span id="v_80m">-</span><span class="unit">Km/h</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">💨 A 180m</h3>
                    <p class="seeing-card-value"><span id="v_180m">-</span><span class="unit">Km/h</span></p>
                </div>
            </div>

            <h2 class="seeing-group-title">Cobertura de Nubes</h2>
            <div class="bloque bloque-fixed-3">
                <div class="card">
                    <h3 class="seeing-card-title">☁️ Bajas</h3>
                    <p class="seeing-card-value"><span id="n_low">-</span><span class="unit">%</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌥️ Medias</h3>
                    <p class="seeing-card-value"><span id="n_mid">-</span><span class="unit">%</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌤️ Altas</h3>
                    <p class="seeing-card-value"><span id="n_high">-</span><span class="unit">%</span></p>
                </div>
            </div>

            <h2 class="seeing-group-title">Seeing (Arcsec)</h2>
            <div class="bloque">
                <div class="card">
                    <h3 class="seeing-card-title">🪐 Planetario</h3>
                    <p class="seeing-card-value"><span id="s_plan">-</span><span class="unit">arcsec</span></p>
                </div>
                <div class="card">
                    <h3 class="seeing-card-title">🌌 Cielo Prof.</h3>
                    <p class="seeing-card-value"><span id="s_deep">-</span><span class="unit">arcsec</span></p>
                </div>
            </div>

            <h2 class="seeing-group-title">Aptitud de la Noche</h2>
            <div class="bloque">
                <div class="card gauge-card">
                    <h3 class="seeing-card-title">👁️ Calidad Visual</h3>
                    <svg class="gauge" viewBox="0 0 200 110" data-type="visual">
                        <defs>
                            <linearGradient id="grad-visual" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%"   stop-color="var(--red)"/>
                                <stop offset="50%"  stop-color="var(--red80)"/>
                                <stop offset="100%" stop-color="var(--green)"/>
                            </linearGradient>
                        </defs>
                        <path d="M10 100 A90 90 0 0 1 190 100"
                              fill="none"
                              stroke="url(#grad-visual)"
                              stroke-width="14"
                              stroke-linecap="round"/>
                        <polygon class="needle"
                                 points="98,100 102,100 100,18"/>

                        <circle class="needle-hub"
                                cx="100" cy="100" r="5"/>

                        <text class="gauge-value" x="100" y="85">--%</text>
                    </svg>
                </div>

                <div class="card gauge-card">
                    <h3 class="seeing-card-title">📸 Astrofoto</h3>
                    <svg class="gauge" viewBox="0 0 200 110" data-type="astro">
                        <defs>
                            <linearGradient id="grad-astro" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%"   stop-color="var(--red)"/>
                                <stop offset="50%"  stop-color="var(--red80)"/>
                                <stop offset="100%" stop-color="var(--green)"/>
                            </linearGradient>
                        </defs>
                        <path d="M10 100 A90 90 0 0 1 190 100"
                              fill="none"
                              stroke="url(#grad-astro)"
                              stroke-width="14"
                              stroke-linecap="round"/>
                        <polygon class="needle"
                                 points="98,100 102,100 100,18"/>

                        <circle class="needle-hub"
                                cx="100" cy="100" r="5"/>

                        <text class="gauge-value" x="100" y="85">--%</text>
                    </svg>
                </div>
            </div>


            <div class="footer">
                <p class="seeing-attribution">
                    Datos procesados de Open-Meteo & Estación Local
                </p>
            </div>
        </div>
    </div>
</div>
