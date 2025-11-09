/* date-time.js */
var myVar = setInterval(function() {
    myTimer();
}, 1000);

function myTimer() {
    var d = new Date();

    // Obtener partes de la fecha
    let horas = String(d.getHours()).padStart(2, '0');
    let minutos = String(d.getMinutes()).padStart(2, '0');
    let dia = String(d.getDate()).padStart(2);

    // Mes abreviado en español
    let meses = ["ene", "feb", "mar", "abr", "may", "jun",
                 "jul", "ago", "sep", "oct", "nov", "dic"];
    let mes = meses[d.getMonth()];

    let anio = d.getFullYear();

    // Construir el formato
    let fechaHora = `${horas}:${minutos} del ${dia} de ${mes} de ${anio}.`;

    // Escribir en el elemento
    document.getElementById("pws-status-time-long").innerHTML = fechaHora;
}
