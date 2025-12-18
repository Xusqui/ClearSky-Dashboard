<?php
// modal_dso.php
?>
<div id="messierCatalogModal" class="modal">
    <div class="modal-content-catalog">
        <header class="modal-header">
            <h2 id="catalogo_nombre">Catálogo Completo</h2>
            <span class="close-button" onclick="closeCatalogModal()">&times;</span>
        </header>
        <div id="messierGrid" class="messier-grid">
            <p id="loadingMessage">Cargando datos del universo...</p>
        </div>
        <footer class="modal-footer">
            <p>Datos proporcionados por <a href="https://astro.xusqui.com" target="_blank" class="link-color">astro.xusqui.com</a></p>
        </footer>
    </div>
</div>
