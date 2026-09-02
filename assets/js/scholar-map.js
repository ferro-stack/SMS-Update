"use strict";

let scholarMarkers = [];

document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById("scholarMap");
    if (!mapElement) return;

    // Initialize Leaflet Map centered around Leyte / Southern Leyte
    const map = L.map("scholarMap").setView([10.2500, 124.9000], 10);
    window.scholarMap = map;

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    loadStudentLocations(map);
    setupDepartmentFilter();
});

// Program & Department Color Palette
const departmentColors = {
    "Nursing": "#ea3388",
    "Information Technology": "#2ea263",
    "Accountancy": "#8426dc",
    "Business Administration": "#f59e0b",
    "Liberal Arts and Education": "#11a1da",
    "Food Preparation & Service Technology": "#08b2a4",
    "Other": "#64748b"
};

function normalizeDepartment(deptStr) {
    const d = (deptStr || "").toLowerCase();
    if (d.includes("nursing")) return "Nursing";
    if (d.includes("information technology") || d.includes("it") || d.includes("computer")) return "Information Technology";
    if (d.includes("accountancy") || d.includes("accounting")) return "Accountancy";
    if (d.includes("business")) return "Business Administration";
    if (d.includes("liberal") || d.includes("education")) return "Liberal Arts and Education";
    if (d.includes("food") || d.includes("service") || d.includes("fpst")) return "Food Preparation & Service Technology";
    return "Information Technology";
}

async function loadStudentLocations(map) {
    try {
        const apiBase = window.API_BASE || "api";
        const response = await fetch(`${apiBase}/scholar-map.php`);
        if (!response.ok) throw new Error("Failed to load student location data.");

        const result = await response.json();
        if (!result.success || !Array.isArray(result.data)) throw new Error("Invalid response format.");

        // Clear existing markers
        scholarMarkers.forEach(item => {
            if (map.hasLayer(item.marker)) map.removeLayer(item.marker);
        });
        scholarMarkers = [];

        result.data.forEach((student) => {
            if (student.latitude === null || student.longitude === null || isNaN(student.latitude) || isNaN(student.longitude)) {
                return;
            }

            const deptKey = normalizeDepartment(student.department);
            const color = departmentColors[deptKey] || "#2ea263";

            // Circle Marker for clean map representation
            const marker = L.circleMarker([student.latitude, student.longitude], {
                radius: 10,
                fillColor: color,
                color: "#ffffff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map);

            const isMaintained = Number(student.gwa) <= 1.50;
            const statusLabel = isMaintained ? "Active (≤ 1.50 GWA)" : "Removed (Below 1.50)";
            const statusColor = isMaintained ? "#16a34a" : "#dc2626";

            // Rich interactive Popup
            marker.bindPopup(`
                <div style="font-family: 'DM Sans', sans-serif; padding: 4px;">
                    <h3 style="margin: 0 0 6px 0; font-size: 15px; color: #134e2a;">${escapeHtml(student.name)}</h3>
                    <div style="font-size: 12.5px; color: #334155; line-height: 1.6;">
                        <div><strong>Student ID:</strong> <span style="font-family: monospace;">${escapeHtml(student.studentId)}</span></div>
                        <div><strong>Department:</strong> <span style="color: ${color}; font-weight:700;">${escapeHtml(deptKey)}</span></div>
                        <div><strong>Year Level:</strong> Year ${escapeHtml(String(student.yearLevel || 1))}</div>
                        <div><strong>Current GWA:</strong> <span style="font-family: monospace; font-weight:700;">${Number(student.gwa || 1.50).toFixed(2)}</span></div>
                        <div><strong>Status:</strong> <span style="color: ${statusColor}; font-weight: 700;">${escapeHtml(statusLabel)}</span></div>
                        <div style="margin-top: 4px;"><strong>Location:</strong> ${escapeHtml(student.address)}</div>
                    </div>
                </div>
            `);

            marker.bindTooltip(`<strong>${escapeHtml(student.name)}</strong> (${deptKey})`, {
                direction: "top",
                sticky: true
            });

            scholarMarkers.push({
                marker: marker,
                department: deptKey,
                data: student
            });
        });

        // Auto zoom to fit visible markers
        if (scholarMarkers.length > 0) {
            const bounds = L.latLngBounds(scholarMarkers.map(i => i.marker.getLatLng()));
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    } catch (err) {
        console.error("Map location loading error:", err);
    }
}

function setupDepartmentFilter() {
    const filterEl = document.getElementById("programFilter");
    if (!filterEl) return;

    filterEl.addEventListener("change", () => {
        const selectedDept = filterEl.value;
        const map = window.scholarMap;
        if (!map) return;

        const visibleLatLngs = [];

        scholarMarkers.forEach((item) => {
            const matches = (selectedDept === "all" || item.department === selectedDept);
            if (matches) {
                if (!map.hasLayer(item.marker)) {
                    item.marker.addTo(map);
                }
                visibleLatLngs.push(item.marker.getLatLng());
            } else {
                if (map.hasLayer(item.marker)) {
                    map.removeLayer(item.marker);
                }
            }
        });

        // Fit map bounds to currently filtered markers
        if (visibleLatLngs.length > 0) {
            const bounds = L.latLngBounds(visibleLatLngs);
            map.fitBounds(bounds, { padding: [60, 60], maxZoom: 14 });
        }
    });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}