<?php
// modal_rain.php
?>
<!--*************************************************************
********************* DATOS DE LLUVIA ***********************
*********************** M O D A L ***************************
************************************************************* -->
<!-- Modal oculto por defecto -->
<div id="rain-modal" class="modal">
    <div class="modal-content">
        <button class="close" id="closeRainModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <h2>Desglose de Precipitación</h2>

        <div class="rain-stats-grid">
            <div class="stat-card"><span class="stat-label">Estado</span><span class="stat-value" id="rain-status">...</span></div>
            <div class="stat-card"><span class="stat-label">Ratio</span><span class="stat-value" id="rain-rate">...</span></div>
            <div class="stat-card"><span class="stat-label">Evento</span><span class="stat-value" id="rain-evento">...</span></div>
            <div class="stat-card"><span class="stat-label">Hoy</span><span class="stat-value" id="rain-today">...</span></div>
            <div class="stat-card"><span class="stat-label">Última Hora</span><span class="stat-value" id="rain-hour">...</span></div>
            <div class="stat-card"><span class="stat-label">Última Semana</span><span class="stat-value" id="rain-week">...</span></div>
            <div class="stat-card"><span class="stat-label">Este Mes</span><span class="stat-value" id="rain-month">...</span></div>
            <div class="stat-card"><span class="stat-label">Este Año</span><span class="stat-value" id="rain-year">...</span></div>
            <div class="stat-card"><span class="stat-label">Total</span><span class="stat-value" id="rain-total">...</span></div>
        </div>

        <hr style="margin: 30px 0;">

        <h3 style="margin-bottom: 20px;">Acumulado Mensual (Lluvia por Mes)</h3>
        <div class="date-range-picker">
            <div class="date-input">
                <label for="rain_startMonth">Mes Inicio:</label>
                <input type="month" id="rain_startMonth" name="rain_startMonth">
            </div>
            <div class="date-input">
                <label for="rain_endMonth">Mes Fin:</label>
                <input type="month" id="rain_endMonth" name="rain_endMonth">
            </div>
            <button id="rain_updateMonthChartBtn" class="update-button">Actualizar Mensual</button>
        </div>
        <div id="rain-month-chart" style="height:300px; margin-top:20px;"></div>

        <hr style="margin: 40px 0;">

        <h3 style="margin-bottom: 20px;">Intensidad y Eventos (Serie de Tiempo)</h3>
        <div class="date-range-picker">
            <div class="date-input">
                <label for="rain_startDatetime">Inicio (Fecha/Hora):</label>
                <input type="datetime-local" id="rain_startDatetime" name="rain_startDatetime">
            </div>
            <div class="date-input">
                <label for="rain_endDatetime">Fin (Fecha/Hora):</label>
                <input type="datetime-local" id="rain_endDatetime" name="rain_endDatetime">
            </div>
            <button id="rain_updateDetailChartBtn" class="update-button">Actualizar Detalle</button>
        </div>
        <div id="rain-detail-chart" style="height:300px; margin-top:20px;"></div>

    </div>
</div>
