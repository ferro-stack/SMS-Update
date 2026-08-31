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
            <div id="scholarMap">
            </div>
        </div>
     </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>