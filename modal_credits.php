<?php
// modal_credits.php
?>
<!--*************************************************************
*********************** MODAL AGRADECIMIENTOS *******************
****************************************************************-->
<!-- Modal oculto al inicio -->
<div id="credits" class="credits-modal">
    <div class="credits-modal-content">
        <!-- Botón de Cerrar (se mantiene el SVG original) -->
        <button class="close" aria-label="Cerrar" id="closeCreditsModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="credits-info-panel">
            <h1 class="credits-main-title">Agradecimientos y Créditos</h1>
            <a class="credits-card **credits-card-full**" href="https://github.com/Xusqui/ClearSky-Dashboard" target="_blank" rel="noopener noreferrer">
                <span class="credits-card-icon icon github"></span>
                <h3 class="credits-card-title">Idea Original, Desarrollo y Ensamblaje</h3>
                <p class="credits-card-value">Hecho por Xisco Fernández</p>
                <span class="credits-card-source">xusqui.com</span>
            </a>
            <h2 class="credits-section-title">Diseño, Datos y Tecnología</h2>
            <div class="credits-grid">
                <!-- 1. Weather Underground (Diseño/Estilo) -->
                <a class="credits-card" href="https://www.wunderground.com/" target="_blank" rel="noopener noreferrer">
                    <!-- Icono Wu-Logo -->
                    <span class="credits-card-icon wu-logo"></span>
                    <h3 class="credits-card-title">Diseño Infográfico</h3>
                    <p class="credits-card-value">La mayoría de iconos y gráficos son originales de **Weather Underground**</p>
                    <span class="credits-card-source">wunderground.com</span>
                </a>
                <!-- 2. CodePen (Fases Lunares) -->
                <a class="credits-card" href="https://codepen.io/xaelan/pen/bjqOvo" target="_blank" rel="noopener noreferrer">
                    <!-- Usamos un icono de noche despejada como representación lunar genérica -->
                    <span class="credits-card-icon icon clear-night"></span>
                    <h3 class="credits-card-title">Fases Lunares (CSS)</h3>
                    <p class="credits-card-value">Código de **Pure CSS Moon Phases**</p>
                    <span class="credits-card-source">codepen.io/xaelan</span>
                </a>
                <!-- 3. Cloudy Nights (Mapa Lunar) -->
                <a class="credits-card" href="https://www.cloudynights.com/forums/topic/908244-high-resolution-map-lunar-100/#comment-13218711" target="_blank" rel="noopener noreferrer">
                    <!-- Usamos un icono de información para referencia de mapa -->
                    <span class="credits-card-icon icon info"></span>
                    <h3 class="credits-card-title">Mapa Lunar 100</h3>
                    <p class="credits-card-value">Referencia: **High Resolution Lunar 100 Map**</p>
                    <span class="credits-card-source">cloudynights.com</span>
                </a>
                <!-- 4. Open-Meteo (Datos en Altura) -->
                <a class="credits-card" href="https://open-meteo.com" target="_blank" rel="noopener noreferrer">
                    <!-- Icono de Viento (para datos en altura) -->
                    <span class="credits-card-icon icon breezy"></span>
                    <h3 class="credits-card-title">Datos en Altura</h3>
                    <p class="credits-card-value">Información de **Open-Meteo** para el cálculo del Seeing.</p>
                    <span class="credits-card-source">open-meteo.com</span>
                </a>
                <!-- 5. Open-Meteo (Previsión) -->
                <a class="credits-card" href="https://open-meteo.com" target="_blank" rel="noopener noreferrer">
                    <!-- Icono de Nubes/Previsión -->
                    <span class="credits-card-icon icon cloudy"></span>
                    <h3 class="credits-card-title">Previsión Meteorológica</h3>
                    <p class="credits-card-value">Previsión horaria obtenida de **Open-Meteo**</p>
                    <span class="credits-card-source">open-meteo.com</span>
                </a>
                <!-- 6. SunCalc (Cálculos Astronómicos) -->
                <a class="credits-card" href="https://app.unpkg.com/suncalc@1.9.0" target="_blank" rel="noopener noreferrer">
                    <!-- Icono de Sol/Amanecer (para cálculos solares y lunares) -->
                    <span class="credits-card-icon icon sunrise"></span>
                    <h3 class="credits-card-title">Cálculos Astronómicos</h3>
                    <p class="credits-card-value">Librería **SunCalc** para horas solares y lunares.</p>
                    <span class="credits-card-source">unpkg.com/suncalc</span>
                </a>
                <!-- 7. OpenStreetMap -->
                <a class="credits-card" href="https://www.openstreetmap.org" target="_blank" rel="noopener noreferrer">
                    <!-- Icono de Ciudad/Ubicación (city) -->
                    <span class="credits-card-icon icon city"></span>
                    <h3 class="credits-card-title">Datos de Mapas y Ubicación</h3>
                    <p class="credits-card-value">Mapas base y datos geográficos proporcionados por **OpenStreetMap**</p>
                    <span class="credits-card-source">openstreetmap.org</span>
                </a>
                <!-- 8. Librería Orb.v2.js -->
                <a class="credits-card" href="https://github.com/lizard-isana/orb.js" target="_blank" rel="noopener noreferrer">
                    <!-- Icono de Ciudad/Ubicación (city) -->
                    <span class="credits-card-icon icon github"></span>
                    <h3 class="credits-card-title">Datos de localización de cuerpos celestes</h3>
                    <p class="credits-card-value">Cálculos de posición y visibilidad de cuerpos celestes realizados gracias a la biblioteca **ORB-V2.JS**</p>
                    <span class="credits-card-source">github.com/lizard-isana/orb.js</span>
                </a>
            </div>
            <!-- Nuevo Título de Sección -->
            <h2 class="credits-section-title">Asistencia en la Creación de Código</h2>
            <div class="credits-grid">
                <!-- 8. Agradecimiento a Gemini -->
                <a class="credits-card" href="https://google.com/gemini" target="_blank" rel="noopener noreferrer">
                    <!-- Icono de información o ajustes (settings) -->
                    <span class="credits-card-icon icon settings"></span>
                    <h3 class="credits-card-title">Asistencia de Gemini</h3>
                    <p class="credits-card-value">Generación, revisión y optimización de código PHP, JS, HTML y CSS.</p>
                    <span class="credits-card-source">google.com/gemini</span>
                </a>
                <!-- 9. Agradecimiento a ChatGPT -->
                <a class="credits-card" href="https://openai.com/chatgpt" target="_blank" rel="noopener noreferrer">
                    <!-- Icono de información o tiempo (time) -->
                    <span class="credits-card-icon icon time"></span>
                    <h3 class="credits-card-title">Asistencia de ChatGPT</h3>
                    <p class="credits-card-value">Generación, revisión y optimización de código PHP, JS, HTML y CSS.</p>
                    <span class="credits-card-source">openai.com/chatgpt</span>
                </a>
            </div>
        </div>
    </div>
</div>
