import.meta.glob([
    '../images/**',
]);

//Nav toggle
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('nav-toggle');
    const menu = document.getElementById('nav-menu');
    if (!btn) return;
    btn.addEventListener('click', function () {
        menu.classList.toggle('hidden');
    });
});
