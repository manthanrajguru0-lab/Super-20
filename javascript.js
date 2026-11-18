
function applyTheme(theme) {
    document.body.classList.toggle(
        "dark-mode",
        theme === "dark"
    );

    localStorage.setItem("super20_theme", theme);

    const button = document.getElementById("themeToggle");

    if (button) {
        button.textContent =
            theme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
    }
}

function toggleTheme() {
    const isDark = document.body.classList.contains("dark-mode");

    applyTheme(isDark ? "light" : "dark");
}

document.addEventListener("DOMContentLoaded", function () {
    const savedTheme = localStorage.getItem("super20_theme") || "light";
    applyTheme(savedTheme);
});
