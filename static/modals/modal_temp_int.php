<?php
//modal_temp_int.php
?>
<!--************************************************************
    ************ GRÁFICA DE TEMPERATURA INTERIOR ***************
    *********************** M O D A L **************************
    ************************************************************ -->
<!-- Modal oculto al inicio -->
<div id="tempIntModal" class="modal">
    <div class="modal-content">
        <button class="close" id="closeTempIntModal" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <div class="date-range-picker">
            <div class="date-input">
                <label for="startDate">Desde:</label>
                <input type="datetime-local" id="startDate" name="startDate">
            </div>
            <div class="date-input">
                <label for="endDate">Hasta:</label>
                <input type="datetime-local" id="endDate" name="endDate">
            </div>
            <button id="updateChartBtn" class="update-button">Actualizar Gráfico</button>
        </div>
        <h2>Evolución de la Temperatura Interior</h2>
        <div id="tempIntChart" style="height:400px;"></div>
    </div>
</div>
