<?php
// modal_wind.php
?>

<!--*************************************************************
****************** GRÁFICAS DEl VIENTO *********************
*********************** M O D A L **************************
************************************************************ -->
<!-- Modal oculto al inicio -->
<div id="windModal" class="modal">
    <div class="modal-content">
        <button class="close" id="closeWindModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="date-range-picker">
            <div class="date-input">
                <label for="wind_startDate">Desde:</label>
                <input type="datetime-local" id="wind_startDate" name="wind_startDate">
            </div>
            <div class="date-input">
                <label for="wind_endDate">Hasta:</label>
                <input type="datetime-local" id="wind_endDate" name="wind_endDate">
            </div>
            <button id="wind_updateChartBtn" class="update-button">Actualizar Gráfico</button>
        </div>
        <h2>Viento</h2>
        <div id="windSpeedChart" style="height: 250px; margin-bottom: 20px;"></div>
        <div id="windDirectionChart" style="height: 250px;"></div>
    </div>
</div>
