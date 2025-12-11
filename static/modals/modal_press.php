<?php
//modal_press.php
?>
<!--*************************************************************
******************* GRÁFICA DE PRESIÓN *********************
*********************** M O D A L ***************************
************************************************************* -->
<!-- Modal oculto por defecto -->
<div id="pressureModal" class="modal">
    <div class="modal-content">
        <button class="close" id="closePressureModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="date-range-picker">
            <div class="date-input">
                <label for="pressure_startDate">Desde:</label>
                <input type="datetime-local" id="pressure_startDate" name="pressure_startDate">
            </div>
            <div class="date-input">
                <label for="pressure_endDate">Hasta:</label>
                <input type="datetime-local" id="pressure_endDate" name="pressure_endDate">
            </div>
            <button id="pressure_updateChartBtn" class="update-button">Actualizar Gráfico</button>
        </div>
        <h2>Evolución de la Presión Relativa</h2>
        <div id="pressureChart" style="height:400px;"></div>
    </div>
</div>
