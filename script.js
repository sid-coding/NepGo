/* ===============================================
   NAVIGATION MENU TOGGLE
=============================================== */
function toggleMenu() {
    const nav = document.getElementById("navLinks");
    nav.classList.toggle("is-active");
}

/* ===============================================
   HELPER: TOGGLE STOPS DISPLAY
=============================================== */
function toggleStops(btn) {
    const stopsList = btn.nextElementSibling;
    const isHidden = getComputedStyle(stopsList).display === "none";
    
    if (isHidden) {
        stopsList.classList.add("is-visible");
        btn.textContent = "Hide Stops";
    } else {
        stopsList.classList.remove("is-visible");
        btn.textContent = `Show Stops`;
    }
}
