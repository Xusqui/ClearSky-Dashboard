document.addEventListener("DOMContentLoaded", function () {
    const pwsInfo = document.getElementById("PWS_info");
    const dialog = document.getElementById("pws-info-dialog");
    const closeBtn = document.getElementById("pws-info-dialog-close");

    // Abrir modal al hacer click en el widget
    pwsInfo.addEventListener("click", function () {
        dialog.style.display = "block";
    });

    // Cerrar modal al hacer click en la X
    closeBtn.addEventListener("click", function () {
        dialog.style.display = "none";
    });

    // Cerrar modal al hacer click fuera del contenido
    window.addEventListener("click", function (event) {
        if (event.target === dialog) {
            dialog.style.display = "none";
        }
    });
});
