// modal_credits.js (Modificado para cargarse sin type="module")

// ⚠️ Todo el código debe estar dentro de este evento.
document.addEventListener("DOMContentLoaded", function() {

    // Ahora, cuando este código se ejecuta, el DOM ya está listo.
    const modal = document.getElementById("credits");
    const openBtn = document.getElementById("link-credits");
    const closeBtn = document.getElementById("closeCreditsModal");

    // Verificar si los elementos existen antes de añadir el listener (por si acaso)
    if (!modal || !openBtn || !closeBtn) {
        console.error("Error: No se encontraron los elementos HTML del modal de créditos.");
        return;
    }

    // Abrir modal
    openBtn.addEventListener("click", () => {
        modal.style.display = "block";
    });

    // Cerrar con la X
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Cerrar haciendo clic fuera del contenido
    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

});
