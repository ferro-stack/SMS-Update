
"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById("scholarMap");

    if (!mapElement) {
        return;
    }

    // Create the map
    const map = L.map("scholarMap").setView([10.3157, 123.8854], 8);

    // Add OpenStreetMap tiles
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);
});
