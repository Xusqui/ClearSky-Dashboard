document.addEventListener("DOMContentLoaded", function () {
  const widgetSeeing = document.getElementById("seeing");
  const modal = document.getElementById("seeingModal");
  const closeBtn = document.getElementById("closeSeeingModal");


//Actualizar el modal de Seeing
function actualizarModal() {
  fetch('/weather/static/modules/get_seeing.php')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error("Error al cargar detalles del seeing:", data.message);
        return;
      }

      const d = data.detalles || {};

      // Actualizar variaciones de las últimas 8h
      document.getElementById("t8h").textContent = d.variacionTemp !== undefined ? `${parseFloat(d.variacionTemp).toFixed(2)}ºC (Últimas 8h)` : "-";
      document.getElementById("h8h").textContent = d.variacionHum !== undefined ? `${parseFloat(d.variacionHum).toFixed(1)}% (Últimas 8h)` : "-";
      document.getElementById("p8h").textContent = d.variacionPres !== undefined ? `${parseFloat(d.variacionPres).toFixed(2)} hPa` : "-";
      document.getElementById("wnow").textContent = d.vientoActual !== undefined ? `${parseFloat(d.vientoActual).toFixed(2)} Km/h` : "-";
      document.getElementById("gnow").textContent = d.rachaActual !== undefined ? `${parseFloat(d.rachaActual).toFixed(2)} Km/h` : "-";
      document.getElementById("rs").textContent = d.luminosidadActual !== undefined ? `${parseFloat(d.luminosidadActual).toFixed(2)} W/m²` : "-";

      // Datos en altura
      document.getElementById("t300").textContent = d.temp300 !== undefined ? `${parseFloat(d.temp300).toFixed(2)}ºC` : "-";
      document.getElementById("t500").textContent = d.temp500 !== undefined ? `${parseFloat(d.temp500).toFixed(2)}ºC` : "-";
      document.getElementById("w300").textContent = d.wind300 !== undefined ? `${parseFloat(d.wind300).toFixed(2)} Km/h` : "-";
      document.getElementById("w500").textContent = d.wind500 !== undefined ? `${parseFloat(d.wind500).toFixed(2)} Km/h` : "-";
      document.getElementById("shear").textContent = d.shear !== undefined ? `${parseFloat(d.shear).toFixed(2)} (turbulencia)` : "-";
      document.getElementById("deltaT").textContent = d.deltaT !== undefined ? `${parseFloat(d.deltaT).toFixed(2)} (estabilidad)` : "-";
      document.getElementById("clow").textContent = d.nubes_low !== undefined ? `${parseFloat(d.nubes_low).toFixed(2)}%` : "-";
      document.getElementById("cmid").textContent = d.nubes_mid !== undefined ? `${parseFloat(d.nubes_mid).toFixed(2)}%` : "-";
      document.getElementById("chigh").textContent = d.nubes_high !== undefined ? `${parseFloat(d.nubes_high).toFixed(2)}%` : "-";

      document.getElementById("seeingtext").textContent = data.seeing;
    })
    .catch(err => console.error("Error al obtener datos del modal:", err));
}


  // Abrir modal al hacer click en el widget
  widgetSeeing.addEventListener("click", () => {
    modal.style.display = "block";
    actualizarModal(); // cada vez que abras, actualiza datos
  });

  // Cerrar modal
  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Cerrar al hacer click fuera del contenido
  window.addEventListener("click", (event) => {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  });
});
