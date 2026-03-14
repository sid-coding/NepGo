/* ===============================================
   AUTOCOMPLETE SUGGESTIONS
=============================================== */
document.addEventListener("DOMContentLoaded", () => {
    const searchInputs = document.querySelectorAll(".search-input-field");

    searchInputs.forEach(input => {
        const dropdown = document.createElement("div");
        dropdown.className = "suggestions-dropdown";
        input.parentElement.appendChild(dropdown);

        input.addEventListener("input", async (e) => {
            const query = e.target.value.trim();
            if (query.length < 1) {
                dropdown.style.display = "none";
                return;
            }

            try {
                const response = await fetch(`get_suggestions.php?q=${encodeURIComponent(query)}`);
                const suggestions = await response.json();

                if (suggestions.length > 0) {
                    dropdown.innerHTML = suggestions.map(s => `<div class="suggestion-item">${s}</div>`).join("");
                    dropdown.style.display = "block";

                    dropdown.querySelectorAll(".suggestion-item").forEach(item => {
                        item.addEventListener("click", () => {
                            input.value = item.textContent;
                            dropdown.style.display = "none";
                        });
                    });
                } else {
                    dropdown.style.display = "none";
                }
            } catch (err) {
                console.error("Suggestions error:", err);
            }
        });

        document.addEventListener("click", (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = "none";
            }
        });
    });
});

/* ===============================================
   UI HELPERS
=============================================== */
function toggleMenu() {
    document.getElementById("navLinks").classList.toggle("is-active");
}

function toggleStops(btn) {
    const stopsList = btn.nextElementSibling;
    const isVisible = stopsList.classList.toggle("is-visible");
    btn.textContent = isVisible ? "Hide Stops" : `Show Stops`;
}
