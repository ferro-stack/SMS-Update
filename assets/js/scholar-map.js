"use strict";

let scholarMarkers = [];

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById("scholarMap");

    if (!mapElement) {
        return;
    }

    // Create the map
    const map = L.map("scholarMap");

    window.scholarMap = map;

    map.setView([10.3157, 123.8854], 8);

    // Add OpenStreetMap tiles
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    // Load approved scholars
    loadApprovedScholars(map);

    // Setup program filter
    setupProgramFilter();
});


// Determine the program/department
function getDepartment(program) {
    const value = (program || "").toLowerCase();

    if (
        value.includes("nursing") ||
        value.includes("bsn")
    ) {
        return "Nursing";
    }

    if (
        value.includes("information technology") ||
        value.includes("bsit")
    ) {
        return "Information Technology";
    }

    if (
        value.includes("accountancy") ||
        value.includes("bsa")
    ) {
        return "Accountancy";
    }

    if (
        value.includes("business administration") ||
        value.includes("bsba")
    ) {
        return "Business Administration";
    }

    if (
        value.includes("liberal arts") ||
        value.includes("education") ||
        value.includes("laed")
    ) {
        return "Liberal Arts and Education";
    }

    if (
        value.includes("food preparation") ||
        value.includes("service technology") ||
        value.includes("fpst")
    ) {
        return "Food Preparation and Service Technology";
    }

    return "Other";
}


// Program colors
const programColors = {
    "Nursing": "#ea3388",
    "Information Technology": "#eb2525",
    "Accountancy": "#8426dc",
    "Business Administration": "#16f962",
    "Liberal Arts and Education": "#11a1da",
    "Food Preparation and Service Technology": "#08b2a4",
    "Other": "#64748b"
};


// Load approved scholars
async function loadApprovedScholars(map) {
    try {
        const response = await fetch(
            window.API_BASE + "/scholar-map.php"
        );

        if (!response.ok) {
            throw new Error("Failed to load scholar locations.");
        }

        const result = await response.json();

        console.log("Scholar map data:", result);

        if (!result.success) {
            throw new Error(
                result.message || "Failed to load data."
            );
        }

        result.data.forEach((scholar) => {

            // Skip scholars without coordinates
            if (
                scholar.latitude === null ||
                scholar.longitude === null
            ) {
                return;
            }

            // Determine department
            const department = getDepartment(scholar.program);

            // Create colored marker
            const marker = L.circleMarker(
                [
                    scholar.latitude,
                    scholar.longitude
                ],
                {
                    radius: 9,
                    fillColor: programColors[department],
                    color: "#ffffff",
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }
            ).addTo(map);

            // Store marker for filtering
            scholarMarkers.push({
                marker: marker,
                department: department
            });

            // Hover information
            marker.bindTooltip(`
                <div class="scholar-tooltip">
                    <strong>${escapeHtml(scholar.name)}</strong>

                    <div>
                        <strong>Student ID:</strong>
                        ${escapeHtml(scholar.studentId)}
                    </div>

                    <div>
                        <strong>Department:</strong>
                        ${escapeHtml(department)}
                    </div>

                    <div>
                        <strong>Program:</strong>
                        ${escapeHtml(scholar.program)}
                    </div>

                    <div>
                        <strong>Scholarship:</strong>
                        ${escapeHtml(scholar.scholarshipType)}
                    </div>

                    <div>
                        <strong>Address:</strong>
                        ${escapeHtml(scholar.address)}
                    </div>
                </div>
            `, {
                direction: "top",
                sticky: true,
                opacity: 1
            });
        });

        // Automatically fit the map to ALL approved scholars
        if (scholarMarkers.length > 0) {
            const bounds = L.latLngBounds(
                scholarMarkers.map(item => item.marker.getLatLng())
            );

            map.fitBounds(bounds, {
                padding: [50, 50]
            });
        }

    } catch (error) {
        console.error("Scholar map error:", error);
    }
}


// Program filter
function setupProgramFilter() {
    const programFilter = document.getElementById("programFilter");

    if (!programFilter) {
        return;
    }

    programFilter.addEventListener("change", () => {

        const selectedProgram = programFilter.value;

        scholarMarkers.forEach((item) => {

            if (
                selectedProgram === "all" ||
                item.department === selectedProgram
            ) {
                if (!window.scholarMap.hasLayer(item.marker)) {
                    item.marker.addTo(window.scholarMap);
                }
            } else {
                if (window.scholarMap.hasLayer(item.marker)) {
                    window.scholarMap.removeLayer(item.marker);
                }
            }
        });

        // Adjust map to currently visible markers
        const visibleMarkers = scholarMarkers
            .filter(item => window.scholarMap.hasLayer(item.marker))
            .map(item => item.marker.getLatLng());

        if (visibleMarkers.length > 0) {
            const bounds = L.latLngBounds(visibleMarkers);

            window.scholarMap.fitBounds(bounds, {
                padding: [50, 50]
            });
        }
    });
}


// Prevent HTML injection
function escapeHtml(value) {
    const div = document.createElement("div");

    div.textContent = value ?? "";

    return div.innerHTML;
}