// This script handles the interactive parts like search suggestions and menus
document.addEventListener("DOMContentLoaded", () => {
    const searchInputs = document.querySelectorAll(".search-input-field");

    // I'm looping through both 'From' and 'To' inputs to add autocomplete to them
    searchInputs.forEach(input => {
        // Creating a div for the suggestions dropdown menu
        const dropdown = document.createElement("div");
        dropdown.className = "suggestions-dropdown";
        input.parentElement.appendChild(dropdown);

        // Every time someone types in the box, this code runs
        input.addEventListener("input", async (e) => {
            const query = e.target.value.trim();
            
            // If the search box is empty, just hide the list
            if (query.length < 1) {
                dropdown.style.display = "none";
                return;
            }

            try {
                // I'm getting the matching stop names from our PHP file
                const response = await fetch(`get_suggestions.php?q=${encodeURIComponent(query)}`);
                const suggestions = await response.json();

                // If we find any matches, show them in the dropdown
                if (suggestions.length > 0) {
                    dropdown.innerHTML = suggestions.map(s => `<div class="suggestion-item">${s}</div>`).join("");
                    dropdown.style.display = "block";

                    // When a user clicks a suggestion, it gets filled in the input box
                    dropdown.querySelectorAll(".suggestion-item").forEach(item => {
                        item.addEventListener("click", () => {
                            input.value = item.textContent;
                            dropdown.style.display = "none";
                        });
                    });
                } else {
                    // No matches found, so hide the list
                    dropdown.style.display = "none";
                }
            } catch (err) {
                console.error("Oops, there was an error with the suggestions:", err);
            }
        });

        // If the user clicks anywhere else, hide the suggestions list
        document.addEventListener("click", (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = "none";
            }
        });
    });
});

// This is for the mobile navigation menu toggle
function toggleMenu() {
    document.getElementById("navLinks").classList.toggle("is-active");
}

// This function shows or hides the list of bus stops in the search results
function toggleStops(btn) {
    const stopsList = btn.nextElementSibling;
    const isVisible = stopsList.classList.toggle("is-visible");
    btn.textContent = isVisible ? "Hide Stops" : `Show Stops`;
}
