<?php
// modal_uv.php
?>
<!--*************************************************************
    ******** GRÁFICA DE RADIACIÓN SOLAR E INDICE UV**************
    *********************** M O D A L ***************************
    ************************************************************* -->
<!-- Modal oculto por defecto -->
<div id="uvSolarModal" class="modal">
    <div class="modal-content">
        <button class="close" id="closeUvSolarModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="date-range-picker">
            <div class="date-input">
                <label for="uv_startDate">Desde:</label>
                <input type="datetime-local" id="uv_startDate" name="uv_startDate">
            </div>
            <div class="date-input">
                <label for="uv_endDate">Hasta:</label>
                <input type="datetime-local" id="uv_endDate" name="uv_endDate">
            </div>
            <button id="uv_updateChartBtn" class="update-button">Actualizar Gráfico</button>
        </div>
        <h2>Índice UV y Radiación Solar</h2>
        <div id="uvChart" style="height: 250px; margin-bottom: 20px;"></div>
        <div id="solarChart" style="height: 250px;"></div>
    </div>
</div>
