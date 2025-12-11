<?php
//modal_hum_ext.php
?>
<!--*************************************************************
******************* GRÁFICA DE HUMEDAD **********************
*********************** M O D A L **************************
************************************************************ -->
<!-- Modal oculto al inicio -->
<div id="humModal" class="modal">
    <div class="modal-content">
        <button class="close" id="closeHumModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <div class="date-range-picker">
            <div class="date-input">
                <label for="hum_startDate">Desde:</label>
                <input type="datetime-local" id="hum_startDate" name="hum_startDate">
            </div>
            <div class="date-input">
                <label for="hum_endDate">Hasta:</label>
                <input type="datetime-local" id="hum_endDate" name="hum_endDate">
            </div>
            <button id="hum_updateChartBtn" class="update-button">Actualizar Gráfico</button>
        </div>
        <h2>Evolución de la Humedad Exterior</h2>
        <div id="humChart" style="height:400px;"></div>
    </div>
</div>
