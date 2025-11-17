import.meta.glob(["../images/**"]);

//Nav toggle
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("nav-toggle");
    const menu = document.getElementById("nav-menu");
    if (!btn) return;
    btn.addEventListener("click", function () {
        menu.classList.toggle("hidden");
    });

    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById("mobile-menu-btn");
    const navMenu = document.getElementById("nav-menu");

    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener("click", function () {
            navMenu.classList.toggle("hidden");
        });
    }

    // Team dropdown toggle
    const teamDropdownBtn = document.getElementById("team-dropdown-btn");
    const teamDropdownMenu = document.getElementById("team-dropdown-menu");

    if (teamDropdownBtn && teamDropdownMenu) {
        teamDropdownBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            teamDropdownMenu.classList.toggle("hidden");
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function () {
            if (!teamDropdownMenu.classList.contains("hidden")) {
                teamDropdownMenu.classList.add("hidden");
            }
        });

        // Prevent closing when clicking inside dropdown
        teamDropdownMenu.addEventListener("click", function (e) {
            e.stopPropagation();
        });
    }
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
