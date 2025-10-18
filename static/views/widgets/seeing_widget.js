document.addEventListener("DOMContentLoaded", function () {
  function actualizarSeeing() {
    fetch('/weather/static/modules/get_seeing.php')
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          console.error("Error Home Assistant:", data.message);
          return;
        }

        const intensity = parseInt(data.IEAL); // extraer valor IEAL
        const texto = data.seeing;
        const multiplicador = data.estrellas;
        if (isNaN(intensity)) {
          console.warn("Valor IEAL inválido:", data.IEAL);
          return;
        }

        const svg = document.querySelector('#seeing svg');
        const starLayer = svg.querySelector('#stars');
        if (!svg || !starLayer) return;

        starLayer.innerHTML = '';
        const count = Math.min(Math.max(intensity, 1), 30) * multiplicador; // escala: 10 a 300 estrellas

        const colors = ['orange', 'yellow'];

        for (let i = 0; i < count; i++) {
          const x = Math.random() * 1190;
          const y = Math.random() * 1706;
          const r = 15;
          const duration = (Math.random() * 4 + 4).toFixed(2);
          const color = colors[Math.floor(Math.random() * colors.length)];

          const star = document.createElementNS("http://www.w3.org/2000/svg", "circle");
          star.setAttribute("cx", x);
          star.setAttribute("cy", y);
          star.setAttribute("r", r);
          star.setAttribute("fill", color);
          star.setAttribute("class", "star");
          star.style.animationDuration = `${duration}s`;

          starLayer.appendChild(star);
        }
        document.getElementById("seeing-description").textContent =
          "Vis: " + texto + " (" + intensity + ")";
      })
      .catch(err => console.error('Error al obtener datos astronómicos:', err));
  }

  // Llamada inicial
  actualizarSeeing();

  // Repetir cada 60 segundos (60000 ms)
  setInterval(actualizarSeeing, 60000);
});
