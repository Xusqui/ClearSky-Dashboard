// theme-switcher.js
/* ======================================================
    THEME SWITCHER: Auto 🌓 / Día 🌞 / Noche 🌙
    ====================================================== */

document.addEventListener("DOMContentLoaded", () => {
    // Referencias a los elementos
    const toggleButton = document.getElementById("theme-toggle");
    const iconElement = document.getElementById("theme-icon");
    const docRoot = document.documentElement;

    // Definición del ciclo de temas y sus propiedades
    const themes = ["auto", "light", "dark"]; // Orden del ciclo
    const themeDetails = {
        "auto": {
            iconSrc: "./static/images/icons/auto.svg",
            altText: "Modo Automático",
            action: () => {
                docRoot.removeAttribute("data-theme");
                localStorage.removeItem("theme");
            }
        },
        "light": {
            iconSrc: "./static/images/icons/day.svg",
            altText: "Modo Día",
            action: () => {
                docRoot.setAttribute("data-theme", "light");
                localStorage.setItem("theme", "light");
            }
        },
        "dark": {
            iconSrc: "./static/images/icons/night.svg",
            altText: "Modo Noche",
            action: () => {
                docRoot.setAttribute("data-theme", "dark");
                localStorage.setItem("theme", "dark");
            }
        }
    };

    /**
     * Aplica el tema dado (al DOM, icono y localStorage).
     * @param {string} theme El tema a aplicar ("auto", "light", "dark").
     */
    const applyTheme = (theme) => {
        const details = themeDetails[theme];
        if (!details) return; // Evitar errores si el tema no es válido

        // 1. Aplica el tema (configura data-theme y localStorage)
        details.action();

        // 2. Actualiza el icono y el texto alternativo
        iconElement.src = details.iconSrc;
        iconElement.alt = details.altText;

        // 3. Actualiza el atributo data-theme del botón para el siguiente estado
        toggleButton.setAttribute("data-theme", theme);
        toggleButton.title = `Alternar a ${themes[(themes.indexOf(theme) + 1) % themes.length]} mode`;
    };

    /**
     * Determina el siguiente tema en el ciclo.
     * @param {string} currentTheme El tema actual.
     * @returns {string} El siguiente tema en el ciclo.
     */
    const getNextTheme = (currentTheme) => {
        const currentIndex = themes.indexOf(currentTheme);
        // Calcula el índice del siguiente tema (cíclico)
        const nextIndex = (currentIndex + 1) % themes.length;
        return themes[nextIndex];
    };

    // --- Inicialización ---

    const savedTheme = localStorage.getItem("theme");
    let initialTheme = "auto";

    // Si hay un tema guardado, úsalo para la inicialización
    if (savedTheme === "light" || savedTheme === "dark") {
        initialTheme = savedTheme;
    }

    // Aplica el tema inicial (guardado o "auto")
    applyTheme(initialTheme);

    // --- Listener del botón ---

    toggleButton.addEventListener("click", () => {
        // El tema actual es el que está *establecido*, no el que está en el atributo del botón
        const currentTheme = toggleButton.getAttribute("data-theme");

        // Obtiene el siguiente tema en el ciclo
        const nextTheme = getNextTheme(currentTheme);

        // Aplica el siguiente tema
        applyTheme(nextTheme);
    });
});
