import.meta.glob(["../images/**"]);

//Nav toggle
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("nav-toggle");
    const menu = document.getElementById("nav-menu");
    if (!btn) return;
    btn.addEventListener("click", function () {
        menu.classList.toggle("hidden");
    });
});

// Modal functionaliteit
window.openModal = function (name) {
    const modal = document.querySelector(`[data-modal="${name}"]`);
    if (!modal) return;
    modal.style.display = "block";
    document.body.classList.add("overflow-y-hidden");
    setTimeout(() => {
        const firstInput = modal.querySelector('input:not([type="hidden"])');
        if (firstInput) firstInput.focus();
    }, 100);
};

window.closeModal = function (name) {
    const modal = document.querySelector(`[data-modal="${name}"]`);
    if (!modal) return;
    modal.style.display = "none";
    document.body.classList.remove("overflow-y-hidden");
};

// Escape key om modals te sluiten
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        document.querySelectorAll("[data-modal]").forEach((modal) => {
            modal.style.display = "none";
        });
        document.body.classList.remove("overflow-y-hidden");
    }
});
