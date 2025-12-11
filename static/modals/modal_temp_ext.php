<?php
// modal_temp_ext.php
?>
<!--************************************************************
***************** GRÁFICA DE TEMPERATURA *******************
*********************** M O D A L **************************
************************************************************ -->
<!-- Modal oculto al inicio -->
<div id="tempModal" class="modal">
    <div class="modal-content">
        <button class="close" id="closeModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <div class="date-range-picker">
            <div class="date-input">
                <label for="temp_startDate">Desde:</label>
                <input type="datetime-local" id="temp_startDate" name="temp_startDate">
            </div>
            <div class="date-input">
                <label for="temp_endDate">Hasta:</label>
                <input type="datetime-local" id="temp_endDate" name="temp_endDate">
            </div>
            <button id="temp_updateChartBtn" class="update-button">Actualizar Gráfico</button>
        </div>
        <h2>Evolución de la Temperatura Exterior</h2>
        <div id="tempChart" style="height:400px;"></div>
    </div>
</div>
