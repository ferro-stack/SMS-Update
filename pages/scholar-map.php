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
                <label for="programFilter">Filter by Program</label>

                <select id="programFilter">
                    <option value="all">All Programs</option>
                    <option value="Nursing">Nursing</option>
                    <option value="Information Technology">
                        Information Technology
                    </option>
                    <option value="Accountancy">Accountancy</option>
                    <option value="Business Administration">
                        Business Administration
                    </option>
                    <option value="Elementary Education">
                        Elementary Education
                    </option>
                    <option value="Food Preparation and Service Technology">
                        Food Preparation and Service Technology
                    </option>
                </select>
            </div>
        </div>

        <div id="scholarMap"></div>

        <div class="map-legend">
            <h4>Programs</h4>

            <div class="legend-item">
                <span class="legend-dot nursing"></span>
                <span>Nursing</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot information-technology"></span>
                <span>Information Technology</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot accountancy"></span>
                <span>Accountancy</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot business"></span>
                <span>Business Administration</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot education"></span>
                <span>Elementary Education</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot fpst"></span>
                <span>Food Preparation &amp; Service Technology</span>
            </div>

            <div class="legend-item">
                <span class="legend-dot other"></span>
                <span>Other</span>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>