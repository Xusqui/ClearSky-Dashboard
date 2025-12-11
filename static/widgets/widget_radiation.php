<?php
// widget_radiation.php
?>
                            <!--    ****************************************************+
                                    ************* WIDGET DE RADIACIÓN SOLAR *************
                                    ***************************************************** -->
                            <div class="widget" id="solar_widget">
                                <div class="title">Radiación Solar</div>
                                <solar-radiation-widget-view data-pws-id="<?= $observatorio ?>" data-status="connected" data-unit="m" data-solar-radiation="<?= $solar ?>" data-main-value="<?= $solar ?>" aria-valuenow="<?= $solar ?>" data-secondary-value="<?= $solar ?>" class="widget-view loaded">
                                    <div class="graphic-container">
                                        <div class="circle-container">
                                            <div class="inner-circle" id="solar-radiation-widget-inner-circle" style="width: <?= $percentage ?>%;"></div>
                                            <div class="circle-ring ring-1 show" id="solar-radiation-widget-ring" style="width: <?= $percentage ?>%;"></div>
                                        </div>
                                    </div>
                                    <div class="value-container">
                                        <div class="main-value value-unit radiation" id="solar-radiation-widget-main-display"><?= $solar ?></div>
                                    </div>
                                </solar-radiation-widget-view>
                            </div>
