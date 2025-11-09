<?php
// widget_uv.php
?>
<!--    ****************************************************+
        **************** WIDGET DE ÍNDICE UV ****************
        ***************************************************** -->
<div class="widget" id="uvi_widget">
    <div class="title">Índice UV</div>
    <?php
        // Vamos a calcular cuántas barras se colorean
        // Creamos un array con 13 posiciones, todas inicialmente "empty"
        $filled = array_fill(1, 13, "empty");
        // valor que marca hasta dónde se llenan: $uv;
        $uv = 0; //Inicialmente índice = 0
        $nivel = $uv;
        // Recorremos el array
        for ($i = 1; $i <= 13; $i++) {
            if ($i <= $nivel) {
                $filled[$i] = "";
            } else {
                $filled[$i] = "empty";
            }
        }
        // Vamos calcular el valor de la radiación UV
        function uvIndexToCategory($uv)
        {
            if ($uv >= 0 && $uv <= 2) {
                return "Muy bajo";
            } elseif ($uv >= 3 && $uv <= 5) {
                return "Moderado";
            } elseif ($uv >= 6 && $uv <= 7) {
                return "Alto";
            } elseif ($uv >= 8 && $uv <= 10) {
                return "Muy alto";
            } elseif ($uv >= 11) {
                return "Extremo";
            } else {
                return "Valor inválido";
            }
        }
        $categoria = uvIndexToCategory($uv);
    ?>
    <uv-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="m" data-uv="<?= $uv ?>" data-main-value="<?= $uv ?>" aria-valuenow="<?= $uv ?>" data-secondary-value="<?= $uv ?>" class="widget-view loaded">
        <div class="graphic-container">
            <div class="pyramid-container" id="uv-widget-pyramid-container">
                <svg width="100%" height="100%" viewBox="0 0 162 136" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <title>UV</title>
                    <g id="UV-Index-Triangle" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <polygon class="<?= $filled[13] ?>" id="Fill-13" points="81.9537723 2.99975159 77.2979826 10.4602611 86.4956236 10.4362484" fill="var(--wu-purple)"></polygon>
                        <polygon class="<?= $filled[12] ?>" id="Fill-12" points="92.8108692 20.7694268 70.8323051 20.8356688 76.2650231 12.1248408 87.5102538 12.0925478" fill="var(--wu-purple)"></polygon>
                        <polygon class="<?= $filled[11] ?>" id="Fill-11" points="99.1192621 31.0946561 64.3589492 31.2022994 69.7916672 22.4914713 93.8186467 22.4177771" fill="var(--wu-purple)"></polygon>
                        <polygon class="<?= $filled[10] ?>" id="Fill-10" points="105.434921 41.428828 57.8945103 41.5778726 63.3272282 32.8670446 100.134305 32.751121" fill="var(--wu-red)"></polygon>
                        <polygon class="<?= $filled[9] ?>" id="Fill-9" points="111.751405 51.7620892 51.4218149 51.9450828 56.8627892 43.2334268 106.442533 43.0769299" fill="var(--wu-red)"></polygon>
                        <polygon class="<?= $filled[8] ?>" id="Fill-8" points="118.058972 62.0882293 44.9567154 62.3192484 50.3894333 53.6092484 112.758356 53.4105223" fill="var(--wu-red)"></polygon>
                        <polygon class="<?= $filled[7] ?>" id="Fill-7" points="124.367447 72.4134586 38.4834421 72.686707 43.9244164 63.975879 119.066832 63.7440318" fill="var(--wu-orange)"></polygon>
                        <polygon class="<?= $filled[6] ?>" id="Fill-6" points="130.17996 81.9276369 32.5388267 82.2331783 37.4513908 74.3512038 125.38216 74.0696752" fill="var(--wu-orange)"></polygon>
                        <polygon class="<?= $filled[5] ?>" id="Fill-5" points="136.495618 92.2528662 26.0661313 92.6006369 31.4988492 83.8889809 131.195003 83.5759873" fill="var(--wu-yellow)"></polygon>
                        <polygon class="<?= $filled[4] ?>" id="Fill-4" points="142.804011 102.58621 19.6010318 102.96793 25.0337497 94.2562739 137.503396 93.9093312" fill="var(--wu-yellow)"></polygon>
                        <polygon class="<?= $filled[3] ?>" id="Fill-3" points="149.111661 112.912268 13.1285841 113.342841 18.5613021 104.632013 143.819302 104.242013" fill="var(--wu-yellow)"></polygon>
                        <polygon class="<?= $filled[2] ?>" id="Fill-2" points="155.427732 123.23758 6.66373231 123.717834 12.0964503 115.007006 150.127117 114.560701" fill="var(--wu-green)"></polygon>
                        <polygon class="<?= $filled[1] ?>" id="Fill-1" points="5.62342462 125.373554 0.999834872 132.792662 161.264189 132.792662 156.435014 124.893299" fill="var(--wu-green)"></polygon>
                    </g>
                </svg>
            </div>
        </div>
        <div class="value-container">
            <div class="main-value value-unit" id="uv-widget-main-display"><?= $uv ?></div>
            <div class="secondary-value uppercase" id="uv-widget-secondary-display"><?= $categoria ?></div>
        </div>
    </uv-widget-view>
</div>
