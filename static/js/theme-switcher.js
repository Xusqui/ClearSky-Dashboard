/* ======================================================
   THEME SWITCHER: Día 🌞 / Noche 🌙 / Auto 🌓
   ====================================================== */

document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".theme-buttons button");
  const savedTheme = localStorage.getItem("theme");

  // Aplica el tema guardado, si existe
  if (savedTheme === "light" || savedTheme === "dark") {
    document.documentElement.setAttribute("data-theme", savedTheme);
  } else {
    document.documentElement.removeAttribute("data-theme");
  }

  // Añade listeners a los botones
  buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const theme = btn.getAttribute("data-theme");

      if (theme === "light" || theme === "dark") {
        document.documentElement.setAttribute("data-theme", theme);
        localStorage.setItem("theme", theme);
      } else if (theme === "auto") {
        document.documentElement.removeAttribute("data-theme");
        localStorage.removeItem("theme");
      }

      // Marcar botón activo (opcional)
      buttons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
    });
  });
});
