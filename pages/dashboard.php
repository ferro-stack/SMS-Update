<?php
$current_page = 'dashboard';
$page_title = "Dashboard";
$page_css = "dashboard.css";
$page_js = "dashboard.js";
include __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../config/db_helper.php';

try {
    $pdo = getDB();
    $total_applicants = (int)$pdo->query("SELECT COUNT(*) FROM applicants")->fetchColumn();
    $under_evaluation = (int)$pdo->query("SELECT COUNT(*) FROM applicants WHERE status IN ('review', 'interview', 'pending')")->fetchColumn();
    $active_scholars = (int)$pdo->query("SELECT COUNT(*) FROM applicants WHERE status = 'approved'")->fetchColumn();
    $renewal_due = (int)$pdo->query("SELECT COUNT(*) FROM renewal_retention WHERE status = 'at-risk' OR status = 'eligible'")->fetchColumn();
} catch (Exception $e) {
    $total_applicants = 142;
    $under_evaluation = 38;
    $active_scholars = 89;
    $renewal_due = 15;
}
?>

<div class="main-content">
    <!-- Stat Cards -->
    <div class="cards">
        <div class="card green">
            <div class="card-header-icon">
                <i data-lucide="user-check"></i>
            </div>
            <div>
                <span class="card-text">Total Applicants</span>
                <h1><?= number_format($total_applicants) ?></h1>
            </div>
        </div>

        <div class="card emerald">
            <div class="card-header-icon">
                <i data-lucide="clock"></i>
            </div>
            <div>
                <span class="card-text">Under Evaluation</span>
                <h1><?= number_format($under_evaluation) ?></h1>
            </div>
        </div>

        <div class="card teal">
            <div class="card-header-icon">
                <i data-lucide="graduation-cap"></i>
            </div>
            <div>
                <span class="card-text">Active Scholars</span>
                <h1><?= number_format($active_scholars) ?></h1>
            </div>
        </div>

        <div class="card amber">
            <div class="card-header-icon">
                <i data-lucide="refresh-cw"></i>
            </div>
            <div>
                <span class="card-text">Renewal Due</span>
                <h1><?= number_format($renewal_due) ?></h1>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-wrapper">
        <div class="chart-block">
            <div class="chart-title">Scholarship Distribution</div>
            <canvas id="scholarshipChart" role="img" aria-label="Bar chart of scholarship distribution by type."></canvas>
        </div>

        <div class="chart-block">
            <div class="chart-title">Monthly Applications</div>
            <canvas id="monthlyChart" role="img" aria-label="Bar chart of monthly applications."></canvas>
        </div>
    </div>

    <!-- Notifications -->
    <div class="notification-container">
        <h3>Recent Activity & Alerts</h3>
        <div class="notification-item">
            <i data-lucide="calendar-check"></i>
            <div>
                <h4>Renewal Deadline Approaching</h4>
                <p>15 active scholars require evaluation prior to next semester.</p>
            </div>
        </div>
        <div class="notification-item">
            <i data-lucide="user-plus"></i>
            <div>
                <h4>New Application Received</h4>
                <p>Juan Dela Cruz applied for Academic Excellence Scholarship.</p>
            </div>
        </div>
        <div class="notification-item">
            <i data-lucide="check-check"></i>
            <div>
                <h4>Evaluation Completed</h4>
                <p>Committee completed batch review for 12 endorsement applicants.</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>