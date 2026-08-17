<?php
$page_title = "Scholarships";
$page_css = "scholarships.css";
$page_js = "scholarships.js";

include __DIR__ . '/../includes/header.php';
?>

<div class="page">
    <div class="table-header-toolbar">
        <div class="toolbar">
            <div class="search-wrap">
                <input type="text" placeholder="Search scholarship...">
                <i data-lucide="search"></i>
            </div>

            <div class="select-wrap">
                <select id="filterType">
                    <option>All Scholarship Types</option>
                </select>
                <i data-lucide="chevron-down"></i>
            </div>

            <div class="select-wrap">
                <select id="filterStatus">
                    <option>All Status</option>
                </select>
                <i data-lucide="chevron-down"></i>
            </div>
        </div>

        <button class="btn-primary" id="addScholarshipBtn">
            <i data-lucide="plus"></i>
            Add Scholarship
        </button>
    </div>

    <div class="table-card">
        <div class="table-wrap">
            <table class="scholarships-table">
                <thead>
                    <tr>
                        <th>Scholarship Name</th>
                        <th>Type</th>
                        <th>Slots</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Dynamic -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add / Edit Scholarship Modal -->
<div class="custom-modal-overlay" id="schFormOverlay">
    <div class="custom-modal-card">
        <div class="custom-modal-header">
            <div>
                <h3 id="schFormTitle">Add Scholarship</h3>
                <p>Configure scholarship parameters and requirements.</p>
            </div>
            <button type="button" class="custom-modal-close" id="schFormCloseBtn"><i data-lucide="x"></i></button>
        </div>
        <form id="schForm">
            <input type="hidden" id="schId" name="id">
            <div class="custom-modal-body" style="display:flex; flex-direction:column; gap:14px;">
                <div class="field">
                    <label style="font-size:13px; font-weight:600; color:#374151;">Scholarship Name <span style="color:red;">*</span></label>
                    <input type="text" id="schName" name="name" placeholder="e.g. Academic Excellence Award" required style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; outline:none;">
                </div>
                <div class="field">
                    <label style="font-size:13px; font-weight:600; color:#374151;">Code <span style="color:red;">*</span></label>
                    <input type="text" id="schCode" name="code" placeholder="e.g. AEA" required style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; outline:none;">
                </div>
                <div class="field">
                    <label style="font-size:13px; font-weight:600; color:#374151;">Type <span style="color:red;">*</span></label>
                    <select id="schType" name="type" required style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; outline:none;">
                        <option>Academic Merit</option>
                        <option>Financial Need-Based</option>
                        <option>Athletic</option>
                        <option>Community Service</option>
                    </select>
                </div>
                <div class="field">
                    <label style="font-size:13px; font-weight:600; color:#374151;">GWA Requirement</label>
                    <input type="number" step="0.01" id="schGwa" name="gwa_requirement" placeholder="e.g. 1.75" style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; outline:none;">
                </div>
                <div class="field">
                    <label style="font-size:13px; font-weight:600; color:#374151;">Total Slots</label>
                    <input type="number" id="schSlots" name="slots" placeholder="50" style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; outline:none;">
                </div>
                <div class="field">
                    <label style="font-size:13px; font-weight:600; color:#374151;">Coverage / Benefits</label>
                    <input type="text" id="schCoverage" name="coverage" placeholder="100% Tuition & Allowances" style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; outline:none;">
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn-secondary" id="schFormCancelBtn">Cancel</button>
                <button type="submit" class="btn-primary">Save Program</button>
            </div>
        </form>
    </div>
</div>

<!-- View Scholarship Details Modal -->
<div class="custom-modal-overlay" id="schViewOverlay">
    <div class="custom-modal-card sm">
        <div class="custom-modal-header">
            <div>
                <h3>Scholarship Overview</h3>
                <p>Program requirements and capacity details.</p>
            </div>
            <button type="button" class="custom-modal-close" id="schViewCloseBtn"><i data-lucide="x"></i></button>
        </div>
        <div class="custom-modal-body" id="schViewBody"></div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="custom-modal-overlay" id="schDeleteOverlay">
    <div class="custom-modal-card sm">
        <div class="custom-modal-header">
            <div>
                <h3>Delete Scholarship</h3>
                <p>Confirm program removal.</p>
            </div>
            <button type="button" class="custom-modal-close" id="schDeleteCloseBtn"><i data-lucide="x"></i></button>
        </div>
        <div class="custom-modal-body">
            <p style="font-size:14px; color:#4b5563;">Are you sure you want to delete <strong id="schDeleteTarget"></strong>?</p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn-secondary" id="schDeleteCancelBtn">Cancel</button>
            <button type="button" class="btn-danger" id="schDeleteConfirmBtn">Delete Program</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>