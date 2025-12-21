/* seeing_widget.js */

function visQualityToText(value) {
  if (value <= 5) return "Nulo";
  if (value <= 15) return "Prácticamente imposible";
  if (value <= 30) return "Muy pobre";
  if (value <= 45) return "Pobre";
  if (value <= 60) return "Aceptable";
  if (value <= 75) return "Buena";
  if (value <= 90) return "Muy buena";
  if (value <= 97) return "Excelente";
  return "Inmejorable";
}

function actualizarSeeing() {
  fetch('./static/modules/widgets/get_astronomy_quality.php')
    .then(r => r.json())
    .then(data => {

    if (!data.seeing || !data.seeing.planetario) {
      console.warn("Datos de seeing incompletos");
      return;
    }

    const seeingArcsec = data.seeing.planetario.arcsec;
    const visQuality = data.calidad.visual;
    const visQualityText = "";

    const svg = document.querySelector('#seeing svg');
    const starLayer = svg?.querySelector('#stars');
    if (!svg || !starLayer) return;

    starLayer.innerHTML = '';

    // Escala razonable: 0–200 estrellas
    const starCount = Math.round(
      Math.min(Math.max(visQuality, 0), 100) * 2
    );

    for (let i = 0; i < starCount; i++) {
      const star = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "circle"
      );

      star.setAttribute("cx", Math.random() * 1190);
      star.setAttribute("cy", Math.random() * 1706);

      // Peor seeing → estrellas más grandes
      const radius = Math.min(Math.max(seeingArcsec, 0.6), 3.5) * 3;
      star.setAttribute("r", radius);

      star.setAttribute("fill", "white");
      star.setAttribute("class", "star");

      starLayer.appendChild(star);
    }

    const visText = visQualityToText(visQuality);

    document.getElementById("seeing-description").textContent =
      `Vis: ${visText} (${visQuality}%)`;
  })
    .catch(e => console.error("Error viendo seeing:", e));
}

actualizarSeeing();
