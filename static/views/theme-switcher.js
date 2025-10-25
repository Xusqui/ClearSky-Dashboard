/* Archivo: theme-switcher.js */

const storageKey = 'themePreference';
const themeSwitcher = document.getElementById('theme-switcher');

/**
 * Aplica el tema al body.
 * @param {string} theme 'light', 'dark', o 'auto'
 */
function applyTheme(theme) {
    const body = document.body;

    // 1. Limpia las clases existentes de tema
    body.classList.remove('light-theme', 'dark-theme');

    if (theme === 'auto') {
        // 2. Determina si el sistema prefiere un tema oscuro
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (prefersDark) {
            body.classList.add('dark-theme');
        } else {
            body.classList.add('light-theme');
        }
    } else if (theme === 'dark') {
        body.classList.add('dark-theme');
    } else if (theme === 'light') {
        body.classList.add('light-theme');
    }

    // 3. Marca el botón activo
    if (themeSwitcher) {
        themeSwitcher.querySelectorAll('button').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-theme') === theme) {
                btn.classList.add('active');
            }
        });
    }

    // 4. Guarda la preferencia
    localStorage.setItem(storageKey, theme);
}

// Carga la preferencia guardada al inicio o usa 'auto' por defecto
const initialTheme = localStorage.getItem(storageKey) || 'auto';
applyTheme(initialTheme);

// Listener para los cambios de tema del sistema (solo relevante en 'auto')
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (localStorage.getItem(storageKey) === 'auto') {
        applyTheme('auto'); // Re-aplica el tema para reflejar el cambio del sistema
    }
});

// Listener para clics en los botones
if (themeSwitcher) {
    themeSwitcher.addEventListener('click', (event) => {
        const target = event.target.closest('button');
        if (target) {
            const newTheme = target.getAttribute('data-theme');
            applyTheme(newTheme);
        }
    });
}
