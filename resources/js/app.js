import.meta.glob(['../images/**']);

/**
 * Generic share button handler for season/match share
 */
function handleNativeShareButtonClick(e) {
    const btn = e.currentTarget;
    const inputId = btn.getAttribute('data-share-input');
    const url = document.getElementById(inputId)?.value;
    const title = btn.getAttribute('data-share-title') || 'Delen';
    const text = btn.getAttribute('data-share-text') || '';
    if (!url) return;
    if (navigator.share) {
        navigator.share({title, text, url});
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-share-input]').forEach((btn) => {
        if (!navigator.share) {
            btn.remove();
        } else {
            btn.addEventListener('click', handleNativeShareButtonClick);
        }
    });
});

/**
 * Initialize screenshot carousels with touch/swipe support
 */
function initScreenshotCarousels() {
    const carousels = document.querySelectorAll('.screenshot-carousel');

    carousels.forEach((carousel) => {
        const slides = carousel.querySelectorAll('.carousel-slide');
        const dots = carousel.querySelectorAll('.carousel-dot');
        const slidesContainer = carousel.querySelector('.carousel-slides');
        let currentSlide = 0;
        let touchStartX = 0;
        let touchEndX = 0;

        // Function to show specific slide
        function showSlide(index) {
            // Ensure index is within bounds
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;

            currentSlide = index;

            // Update slides visibility
            slides.forEach((slide, i) => {
                if (i === currentSlide) {
                    slide.classList.remove('hidden');
                } else {
                    slide.classList.add('hidden');
                }
            });

            // Update dots
            dots.forEach((dot, i) => {
                const color = carousel.dataset.carousel;
                const activeColor =
                    {
                        step1: 'bg-blue-600',
                        step2: 'bg-green-600',
                        step3: 'bg-purple-600',
                        step4: 'bg-orange-600',
                    }[color] || 'bg-blue-600';

                const inactiveColor =
                    {
                        step1: 'bg-blue-300',
                        step2: 'bg-green-300',
                        step3: 'bg-purple-300',
                        step4: 'bg-orange-300',
                    }[color] || 'bg-blue-300';

                if (i === currentSlide) {
                    dot.classList.remove(inactiveColor);
                    dot.classList.add(activeColor);
                } else {
                    dot.classList.remove(activeColor);
                    dot.classList.add(inactiveColor);
                }
            });
        }

        // Dot click handlers
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
            });
        });

        // Touch/swipe handlers
        slidesContainer.addEventListener(
            'touchstart',
            (e) => {
                touchStartX = e.changedTouches[0].screenX;
            },
            {passive: true}
        );

        slidesContainer.addEventListener(
            'touchend',
            (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            },
            {passive: true}
        );

        function handleSwipe() {
            const swipeThreshold = 50; // minimum distance for swipe
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next slide
                    showSlide(currentSlide + 1);
                } else {
                    // Swipe right - previous slide
                    showSlide(currentSlide - 1);
                }
            }
        }
    });
}

//Nav toggle
document.addEventListener('DOMContentLoaded', function () {
    // Screenshot Carousel functionaliteit
    initScreenshotCarousels();

    const btn = document.getElementById('nav-toggle');
    const menu = document.getElementById('nav-menu');
    if (!btn) return;
    btn.addEventListener('click', function () {
        menu.classList.toggle('hidden');
    });

    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navMenu = document.getElementById('nav-menu');

    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function () {
            navMenu.classList.toggle('hidden');
        });
    }

    // Team dropdown toggle
    const teamDropdownBtn = document.getElementById('team-dropdown-btn');
    const teamDropdownMenu = document.getElementById('team-dropdown-menu');

    if (teamDropdownBtn && teamDropdownMenu) {
        teamDropdownBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            teamDropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function () {
            if (!teamDropdownMenu.classList.contains('hidden')) {
                teamDropdownMenu.classList.add('hidden');
            }
        });

        // Prevent closing when clicking inside dropdown
        teamDropdownMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // Generic copy to clipboard functionality
    document.addEventListener('click', function (e) {
        // Handle data-copy-to-clipboard (direct value in attribute)
        if (e.target.matches('[data-copy-to-clipboard]')) {
            const textToCopy = e.target.getAttribute('data-copy-to-clipboard');
            const message =
                e.target.getAttribute('data-copy-message') || 'Gekopieerd!';

            copyToClipboard(textToCopy, message);
        }

        // Handle data-copy-input (copy from input field by ID)
        if (e.target.matches('[data-copy-input]')) {
            const inputId = e.target.getAttribute('data-copy-input');
            const message =
                e.target.getAttribute('data-copy-message') ||
                'Link gekopieerd naar klembord!';
            const input = document.getElementById(inputId);

            if (input) {
                input.select();
                input.setSelectionRange(0, 99999); // For mobile devices
                copyToClipboard(input.value, message);
            }
        }
    });

    // Opponent autocomplete component initialisatie
    initOpponentAutocomplete();
});

// Helper function for copying to clipboard
function copyToClipboard(text, message = 'Gekopieerd!') {
    navigator.clipboard
        .writeText(text)
        .then(() => {
            alert(message);
        })
        .catch((err) => {
            console.error('Kopiëren mislukt:', err);
        });
}

// Modal functionaliteit
window.openModal = function (name) {
    const modal = document.querySelector(`[data-modal="${name}"]`);
    if (!modal) return;
    modal.style.display = 'block';
    document.body.classList.add('overflow-y-hidden');
    setTimeout(() => {
        const firstInput = modal.querySelector('input:not([type="hidden"])');
        if (firstInput) firstInput.focus();
    }, 100);
};

window.closeModal = function (name) {
    const modal = document.querySelector(`[data-modal="${name}"]`);
    if (!modal) return;
    modal.style.display = 'none';
    document.body.classList.remove('overflow-y-hidden');
};

// Escape key om modals te sluiten
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[data-modal]').forEach((modal) => {
            modal.style.display = 'none';
        });
        document.body.classList.remove('overflow-y-hidden');
    }
});

// Autocomplete functie voor opponents
function initOpponentAutocomplete() {
    const inputs = document.querySelectorAll('[data-opponent-autocomplete]');
    if (!inputs.length) return;

    inputs.forEach((input) => {
        const hiddenTarget = document.getElementById(
            input.getAttribute('data-target-hidden')
        );
        if (!hiddenTarget) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const list = document.createElement('div');
        list.className =
            'absolute left-0 right-0 top-full z-30 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow dark:shadow-gray-900 mt-1 hidden';
        wrapper.appendChild(list);

        let abortController = null;
        let lastQuery = '';
        const debounceMs = 250;
        let timer = null;

        input.addEventListener('input', () => {
            const q = input.value.trim();
            if (q.length < 2) {
                list.classList.add('hidden');
                hiddenTarget.value = '';
                return;
            }
            if (q === lastQuery) return;
            lastQuery = q;
            clearTimeout(timer);
            timer = setTimeout(() => fetchOpponents(q), debounceMs);
        });

        input.addEventListener('focus', () => {
            if (list.children.length) list.classList.remove('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                list.classList.add('hidden');
            }
        });

        function fetchOpponents(q) {
            if (abortController) abortController.abort();
            abortController = new AbortController();
            list.innerHTML =
                '<div class="p-2 text-sm text-gray-500 dark:text-gray-400">Zoeken...</div>';
            list.classList.remove('hidden');
            fetch(`/api/opponents?q=${encodeURIComponent(q)}`, {
                signal: abortController.signal,
                headers: {Accept: 'application/json'},
            })
                .then((r) => r.json())
                .then((items) => {
                    if (!Array.isArray(items) || !items.length) {
                        list.innerHTML =
                            '<div class="p-2 text-sm text-gray-500 dark:text-gray-400">Geen resultaten</div>';
                        return;
                    }
                    list.innerHTML = '';
                    items.forEach((item) => {
                        const el = document.createElement('button');
                        el.type = 'button';
                        el.className =
                            'w-full flex items-center gap-3 p-2 text-left hover:bg-blue-50 dark:hover:bg-blue-900 focus:bg-blue-100 dark:focus:bg-blue-800 text-sm text-gray-900 dark:text-gray-100';
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
                                <span class="block text-xs text-gray-600 dark:text-gray-400">${escapeHtml(
                            item.location || ''
                        )}</span>
                            </span>
                        `;
                        el.addEventListener('click', () => {
                            hiddenTarget.value = item.id;
                            input.value =
                                item.name + ' (' + item.location + ')';
                            list.classList.add('hidden');
                            list.innerHTML = '';
                        });
                        list.appendChild(el);
                    });
                })
                .catch((e) => {
                    if (e.name === 'AbortError') return;
                    list.innerHTML =
                        '<div class="p-2 text-sm text-red-600 dark:text-red-400">Fout bij zoeken</div>';
                });
        }
    });
}

function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Dynamic Player Rows Management
document.addEventListener('DOMContentLoaded', function () {
    const playersContainer = document.getElementById('players-rows');
    const addPlayerBtn = document.getElementById('add-player-btn');

    if (!playersContainer || !addPlayerBtn) return;

    let rowIndex = 1; // Start from 1 since we have row 0 already

    // Function to update remove button visibility
    function updateRemoveButtons() {
        const rows = playersContainer.querySelectorAll('.player-row');
        const removeBtns =
            playersContainer.querySelectorAll('.remove-player-btn');

        // Show remove buttons only if there's more than 1 row
        removeBtns.forEach((btn) => {
            if (rows.length > 1) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });
    }

    // Function to create a new player row
    function createPlayerRow(index) {
        const row = document.createElement('div');
        row.className =
            'player-row mb-3 p-3 border rounded bg-gray-50 dark:bg-gray-900 dark:border-gray-700';
        row.setAttribute('data-row-index', index);

        // Get positions from the first select element
        const firstSelect = playersContainer.querySelector(
            'select[name$="[position_id]"]'
        );
        const positionsHTML = firstSelect ? firstSelect.innerHTML : '';

        row.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-[2fr_1.5fr_1fr_auto] gap-3 items-start">
                <div>
                    <label class="block text-sm font-medium mb-1 sm:hidden dark:text-gray-200">Naam</label>
                    <input type="text" name="players[${index}][name]"
                           class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                           placeholder="Naam van de speler" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 sm:hidden dark:text-gray-200">Positie</label>
                    <select name="players[${index}][position_id]"
                            class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                        ${positionsHTML}
                    </select>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-start h-full">
                    <label class="block text-sm font-medium mb-1 sm:hidden dark:text-gray-200">Sterkere speler</label>
                    <input type="hidden" name="players[${index}][weight]" value="1">
                    <input type="checkbox" name="players[${index}][weight]" value="2" class="h-5 w-5">
                </div>
                <div class="flex items-start sm:items-center sm:justify-center">
                    <button type="button" class="remove-player-btn w-10 h-10 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 rounded transition cursor-pointer" title="Verwijder speler">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        `;

        return row;
    }

    // Add player button click handler
    addPlayerBtn.addEventListener('click', function () {
        const newRow = createPlayerRow(rowIndex);
        playersContainer.appendChild(newRow);
        rowIndex++;
        updateRemoveButtons();

        // Focus on the name input of the new row
        const nameInput = newRow.querySelector('input[name$="[name]"]');
        if (nameInput) {
            nameInput.focus();
        }
    });

    // Remove player button click handler (event delegation)
    playersContainer.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.remove-player-btn');
        if (removeBtn) {
            const row = removeBtn.closest('.player-row');
            const rows = playersContainer.querySelectorAll('.player-row');

            // Only remove if more than 1 row exists
            if (rows.length > 1 && row) {
                row.remove();
                updateRemoveButtons();
            }
        }
    });

    // Initial state
    updateRemoveButtons();
});

// Dynamic Goal Rows Management (Football Match Goals)
document.addEventListener('DOMContentLoaded', function () {
    const goalsContainer = document.getElementById('goals-container');
    const addGoalBtn = document.getElementById('add-goal-btn');

    if (!goalsContainer || !addGoalBtn) return;

    let goalIndex = goalsContainer.querySelectorAll('.goal-row').length;

    // Add goal button click handler
    addGoalBtn.addEventListener('click', function () {
        const template = document.getElementById('goal-row-template');
        if (!template) return;

        const templateContent = template.innerHTML.replace(
            /__INDEX__/g,
            goalIndex
        );
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = templateContent;
        const newRow = tempDiv.firstElementChild;

        goalsContainer.appendChild(newRow);
        goalIndex++;
    });

    // Remove goal button click handler (event delegation)
    goalsContainer.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.remove-goal-btn');
        if (removeBtn) {
            const row = removeBtn.closest('.goal-row');
            if (row) {
                // Check if this is an existing goal (has an ID)
                const idInput = row.querySelector('input[name$=\'[id]\']');
                if (idInput && idInput.value) {
                    // Mark for deletion instead of removing
                    const deleteFlag = row.querySelector('.delete-flag');
                    if (deleteFlag) {
                        deleteFlag.value = '1';
                        row.style.display = 'none';
                    }
                } else {
                    // New row, just remove it
                    row.remove();
                }
            }
        }
    });
});

//Season page year preview
document.addEventListener('DOMContentLoaded', function () {
    // Year preview
    const yearInput = document.getElementById('season-year');
    const yearPreview = document.getElementById('season-year-preview');
    if (yearInput && yearPreview) {
        function updateYearPreview() {
            const jaar = parseInt(yearInput.value);
            if (!isNaN(jaar) && jaar >= 2000 && jaar <= 2100) {
                yearPreview.textContent = jaar + '-' + (jaar + 1);
            } else {
                yearPreview.textContent = '';
            }
        }

        yearInput.addEventListener('input', updateYearPreview);
        updateYearPreview();
    }

    // Fase validation
    window.validatePhaseInput = function (input) {
        const errorDiv = document.getElementById('phase-error');
        const value = parseInt(input.value);
        if (isNaN(value) || value < 1 || value > 4) {
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    };
});

// Regenerate lineup section toggle
document.addEventListener('DOMContentLoaded', function () {
    const regenerateSection = document.querySelector('.regenerate-section');
    if (regenerateSection) {
        const button = regenerateSection.querySelector('button');
        const arrow = button.querySelector('span');
        const formContent = regenerateSection.querySelector(
            '.regenerate-form-content'
        );
        const cancelButton = formContent.querySelector('button[type="button"]');

        // Toggle form visibility
        button.addEventListener('click', function () {
            formContent.classList.toggle('hidden');
            arrow.classList.toggle('rotate-90');
        });

        // Cancel button closes the form
        if (cancelButton) {
            cancelButton.addEventListener('click', function () {
                formContent.classList.add('hidden');
                arrow.classList.remove('rotate-90');
            });
        }
    }
});
