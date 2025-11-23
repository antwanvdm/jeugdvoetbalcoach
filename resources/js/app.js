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

    // Copy to clipboard functionality
    document.addEventListener("click", function (e) {
        if (e.target.matches("[data-copy-to-clipboard]")) {
            const textToCopy = e.target.getAttribute("data-copy-to-clipboard");
            const message =
                e.target.getAttribute("data-copy-message") || "Gekopieerd!";

            navigator.clipboard
                .writeText(textToCopy)
                .then(() => {
                    alert(message);
                })
                .catch((err) => {
                    console.error("Kopiëren mislukt:", err);
                });
        }
    });

    // Opponent autocomplete component initialisatie
    initOpponentAutocomplete();
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

// Autocomplete functie voor opponents
function initOpponentAutocomplete() {
    const inputs = document.querySelectorAll("[data-opponent-autocomplete]");
    if (!inputs.length) return;

    inputs.forEach((input) => {
        const hiddenTarget = document.getElementById(
            input.getAttribute("data-target-hidden")
        );
        if (!hiddenTarget) return;

        const wrapper = document.createElement("div");
        wrapper.className = "relative";
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const list = document.createElement("div");
        list.className =
            "absolute left-0 right-0 top-full z-30 bg-white border border-gray-200 rounded-md shadow mt-1 hidden";
        wrapper.appendChild(list);

        let abortController = null;
        let lastQuery = "";
        const debounceMs = 250;
        let timer = null;

        input.addEventListener("input", () => {
            const q = input.value.trim();
            if (q.length < 2) {
                list.classList.add("hidden");
                hiddenTarget.value = "";
                return;
            }
            if (q === lastQuery) return;
            lastQuery = q;
            clearTimeout(timer);
            timer = setTimeout(() => fetchOpponents(q), debounceMs);
        });

        input.addEventListener("focus", () => {
            if (list.children.length) list.classList.remove("hidden");
        });

        document.addEventListener("click", (e) => {
            if (!wrapper.contains(e.target)) {
                list.classList.add("hidden");
            }
        });

        function fetchOpponents(q) {
            if (abortController) abortController.abort();
            abortController = new AbortController();
            list.innerHTML =
                '<div class="p-2 text-sm text-gray-500">Zoeken...</div>';
            list.classList.remove("hidden");
            fetch(`/api/opponents?q=${encodeURIComponent(q)}`, {
                signal: abortController.signal,
                headers: { Accept: "application/json" },
            })
                .then((r) => r.json())
                .then((items) => {
                    if (!Array.isArray(items) || !items.length) {
                        list.innerHTML =
                            '<div class="p-2 text-sm text-gray-500">Geen resultaten</div>';
                        return;
                    }
                    list.innerHTML = "";
                    items.forEach((item) => {
                        const el = document.createElement("button");
                        el.type = "button";
                        el.className =
                            "w-full flex items-center gap-3 p-2 text-left hover:bg-blue-50 focus:bg-blue-100 text-sm";
                        el.innerHTML = `
                            ${
                                item.logo_url
                                    ? `<img src="${item.logo_url}" alt="" class="h-8 w-8 object-contain">`
                                    : `<div class="h-8 w-8 flex items-center justify-center bg-gray-100 rounded text-gray-400">?</div>`
                            }
                            <span class="flex-1">
                                <span class="font-medium">${escapeHtml(
                                    item.name
                                )}</span>
                                <span class="block text-xs text-gray-600">${escapeHtml(
                                    item.location || ""
                                )}</span>
                            </span>
                        `;
                        el.addEventListener("click", () => {
                            hiddenTarget.value = item.id;
                            input.value =
                                item.name + " (" + item.location + ")";
                            list.classList.add("hidden");
                            list.innerHTML = "";
                        });
                        list.appendChild(el);
                    });
                })
                .catch((e) => {
                    if (e.name === "AbortError") return;
                    list.innerHTML =
                        '<div class="p-2 text-sm text-red-600">Fout bij zoeken</div>';
                });
        }
    });
}

function escapeHtml(str) {
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
