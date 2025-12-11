<?php
// widget_temp_int.php
?>
                            <!--    ****************************************************+
                                    *************** WIDGET DE TEMPERATURA ***************
                                    ***************************************************** -->
                            <div class="widget" id="temp_widget">
                                <?php
                                    // Calcular la posición de la aguja de la temperatura
                                    $minTemp = -20;
                                    $maxTemp = 50;
                                    $minAngle = -145;
                                    $maxAngle = 145;
                                    $temp_angle = 0; // ángulo inicial
                                    // Limitamos a los extremos
                                    if ($temp_angle < $minAngle) {
                                        $temp_angle = $minAngle;
                                    }
                                    if ($temp_angle > $maxAngle) {
                                        $temp_angle = $maxAngle;
                                    }
                                ?>
                                <div class="title">Temperatura Exterior</div>
                                <temp-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="m" data-temp="<?= $temp ?>" data-temp-angle="<?= $temp_angle ?>" data-main-value="<?= $temp ?>" aria-valuenow="<?= $temp ?>" class="widget-view loaded">
                                    <div class="graphic-container">
                                        <div class="temp-gauge-container">
                                            <div class="temp-gauge-bg"></div>
                                            <div class="temp-gauge-inner"></div>
                                            <div class="temp-needle" id="temp-widget-needle" style="transform: translate(-50%, -100%) rotate(<?= $temp_angle ?>deg);"></div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit degrees" id="temp-widget-main-display"><?= $temp ?></div>
                                        <div class="tertiary-value uppercase Value-unit degrees" id="temp-widget-feel-display">Sensación: <?= $feels_like ?></div>
                                    </div>
                                </temp-widget-view>
                            </div>
