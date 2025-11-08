const modal = document.getElementById("agradecimientos");
const openBtn = document.getElementById("link-greetings");
const closeBtn = document.getElementById("closeAgradecimientosModal");

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
