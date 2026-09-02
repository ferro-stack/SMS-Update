<?php
require_once __DIR__ . '/../config/config.php';

$current_page = 'scholar-map';
$page_title = "Scholar Map";
$page_css = "scholar-map.css";
$page_js = "scholar-map.js";

include __DIR__ . '/../includes/header.php';
?>

<div class="page">
    <div class="map-card">

        <div class="map-controls">
            <div class="map-filter">
                <label for="programFilter" style="font-weight:700; color:#134e2a;">Filter Students by Department</label>
                <select id="programFilter" class="filter-select">
                    <option value="all">All Departments</option>
                    <option value="Nursing">Nursing</option>
                    <option value="Information Technology">Information Technology</option>
                    <option value="Accountancy">Accountancy</option>
                    <option value="Business Administration">Business Administration</option>
                    <option value="Liberal Arts and Education">Liberal Arts and Education</option>
                    <option value="Food Preparation & Service Technology">Food Preparation & Service Technology</option>
                </select>
            </div>
        </div>

        <div id="scholarMap" style="height: 550px; width: 100%; border-radius: 12px;"></div>

        <div class="map-legend">
            <h4>Departments</h4>

            <div class="legend-item">
                <span class="legend-dot" style="background:#ea3388;"></span>
                <span>Nursing</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot" style="background:#2ea263;"></span>
                <span>Information Technology</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot" style="background:#8426dc;"></span>
                <span>Accountancy</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot" style="background:#f59e0b;"></span>
                <span>Business Administration</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot" style="background:#11a1da;"></span>
                <span>Liberal Arts and Education</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot" style="background:#08b2a4;"></span>
                <span>Food Preparation &amp; Service Technology</span>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>