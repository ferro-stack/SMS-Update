"use strict";
document.addEventListener("DOMContentLoaded", () => {
    const ledgerBody = document.getElementById("ledgerBody");
    const countEligible = document.getElementById("countEligible");
    const countAtRisk = document.getElementById("countAtRisk");
    const countTerminated = document.getElementById("countTerminated");
    const countTotal = document.getElementById("countTotal");
    const searchBox = document.getElementById("searchBox");
    const statusFilter = document.getElementById("statusFilter");
    const modalOverlay = document.getElementById("modalOverlay");
    const modalClose = document.getElementById("modalClose");
    const modalName = document.getElementById("modalName");
    const modalId = document.getElementById("modalId");
    const modalCriteria = document.getElementById("modalCriteria");
    const modalRemarksText = document.getElementById("modalRemarksText");
    const modalSeal = document.getElementById("modalSeal");
    const renewBtn = document.getElementById("renewBtn");
    const flagBtn = document.getElementById("flagBtn");
    const renDeleteOverlay = document.getElementById("renDeleteOverlay");
    const renDeleteCloseBtn = document.getElementById("renDeleteCloseBtn");
    const renDeleteCancelBtn = document.getElementById("renDeleteCancelBtn");
    const renDeleteConfirmBtn = document.getElementById("renDeleteConfirmBtn");
    const renDeleteTarget = document.getElementById("renDeleteTarget");
    let ledgerData = [];
    let selectedRecord = null;
    let deletingRenId = null;
    async function loadLedger() {
        try {
            const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
            const res = await fetch(`${apiPath}/list_renewal.php`);
            const json = await res.json();
            if (json.success) {
                ledgerData = json.data;
                if (json.summary) {
                    if (countEligible)
                        countEligible.textContent = String(json.summary.eligible);
                    if (countAtRisk)
                        countAtRisk.textContent = String(json.summary.at_risk);
                    if (countTerminated)
                        countTerminated.textContent = String(json.summary.terminated);
                    if (countTotal)
                        countTotal.textContent = String(json.summary.total);
                }
                renderLedger();
            }
        }
        catch (e) {
            console.error("Failed to load renewal ledger:", e);
        }
    }
    function renderLedger() {
        if (!ledgerBody)
            return;
        const query = searchBox ? searchBox.value.toLowerCase().trim() : "";
        const statusVal = statusFilter ? statusFilter.value.toLowerCase() : "";
        const filtered = ledgerData.filter((r) => {
            const matchQuery = r.name.toLowerCase().includes(query) || r.studentId.toLowerCase().includes(query);
            const matchStatus = !statusVal || r.status.toLowerCase() === statusVal;
            return matchQuery && matchStatus;
        });
        ledgerBody.innerHTML = "";
        if (filtered.length === 0) {
            ledgerBody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:#6b7280;">No scholars found.</td></tr>`;
            return;
        }
        filtered.forEach((r) => {
            const tr = document.createElement("tr");
            const statusBadge = r.status === "eligible" ? "badge-eligible" : (r.status === "at-risk" ? "badge-at-risk" : "badge-terminated");
            tr.innerHTML = `
        <td><strong class="font-mono">${r.studentId}</strong></td>
        <td>${r.name}</td>
        <td><span class="font-mono">${r.gwa.toFixed(2)}</span></td>
        <td>${r.failingGrades > 0 ? `<span class="font-mono" style="color:red;">${r.failingGrades} Failing</span>` : "Passed All"}</td>
        <td>${r.enrolled ? "Enrolled" : "Not Enrolled"}</td>
        <td><span class="status-badge ${statusBadge}">${r.status}</span></td>
        <td class="actions-cell">
          <button type="button" class="btn-icon-action edit" title="Edit / Review Scholar" onclick="editRenewal(event, ${r.id})">
            <i data-lucide="pencil"></i>
          </button>
          <button type="button" class="btn-icon-action delete" title="Delete Scholar Entry" onclick="confirmDeleteRenewal(event, ${r.id})">
            <i data-lucide="trash-2"></i>
          </button>
        </td>
      `;
            tr.addEventListener("click", () => window.openEvalModal ? window.openEvalModal(r.id) : null);
            ledgerBody.appendChild(tr);
        });
        if (typeof lucide !== "undefined")
            lucide.createIcons();
    }
    window.openEvalModal = function (id) {
        selectedRecord = ledgerData.find((r) => r.id === id);
        if (!selectedRecord || !modalOverlay)
            return;
        if (modalName)
            modalName.textContent = selectedRecord.name;
        if (modalId)
            modalId.textContent = `Student ID: ${selectedRecord.studentId}`;
        if (modalSeal) {
            modalSeal.textContent = selectedRecord.status.toUpperCase();
            const badgeCls = selectedRecord.status === 'eligible' ? 'badge-approved' : (selectedRecord.status === 'at-risk' ? 'badge-pending' : 'badge-rejected');
            modalSeal.className = `status-badge ${badgeCls}`;
        }
        if (modalRemarksText)
            modalRemarksText.textContent = selectedRecord.remarks || "No remarks logged.";
        if (modalCriteria) {
            modalCriteria.innerHTML = `
        <div class="view-detail-grid" style="margin-bottom:12px;">
          <div class="detail-item full-width">
            <span class="detail-label">Scholarship Type</span>
            <span class="detail-value highlight">${selectedRecord.scholarshipType}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Current GWA</span>
            <span class="detail-value mono font-mono">${selectedRecord.gwa.toFixed(2)}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Failing Grades</span>
            <span class="detail-value font-mono ${selectedRecord.failingGrades > 0 ? 'metric fail' : 'metric pass'}">${selectedRecord.failingGrades}</span>
          </div>
          <div class="detail-item full-width">
            <span class="detail-label">Enrollment Status</span>
            <span class="detail-value">${selectedRecord.enrolled ? "Validated Enrollment" : "Unconfirmed Enrollment"}</span>
          </div>
        </div>
      `;
        }
        modalOverlay.classList.add("open");
    };
    function closeModal() {
        if (modalOverlay)
            modalOverlay.classList.remove("open");
        selectedRecord = null;
    }
    window.editRenewal = function (event, id) {
        event.stopPropagation();
        if (window.openEvalModal)
            window.openEvalModal(id);
    };
    window.confirmDeleteRenewal = function (event, id) {
        event.stopPropagation();
        const item = ledgerData.find((r) => r.id === id);
        if (!item)
            return;
        deletingRenId = id;
        if (renDeleteTarget)
            renDeleteTarget.textContent = item.name;
        if (renDeleteOverlay)
            renDeleteOverlay.classList.add("open");
    };
    function closeDeleteModal() {
        if (renDeleteOverlay)
            renDeleteOverlay.classList.remove("open");
        deletingRenId = null;
    }
    async function updateStatus(action) {
        if (!selectedRecord)
            return;
        const remarks = prompt(`Enter remarks for ${action.toUpperCase()}:`, selectedRecord.remarks || "");
        const formData = new FormData();
        formData.append("id", String(selectedRecord.id));
        formData.append("action", action);
        formData.append("remarks", remarks || "");
        try {
            const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
            const res = await fetch(`${apiPath}/list_renewal.php`, { method: "POST", body: formData });
            const json = await res.json();
            if (json.success) {
                closeModal();
                loadLedger();
            }
        }
        catch (e) {
            alert("Failed to update status.");
        }
    }
    if (renDeleteConfirmBtn) {
        renDeleteConfirmBtn.addEventListener("click", async () => {
            if (!deletingRenId)
                return;
            try {
                const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
                const res = await fetch(`${apiPath}/delete_renewal.php?id=${deletingRenId}`, { method: "POST" });
                const json = await res.json();
                if (json.success) {
                    closeDeleteModal();
                    loadLedger();
                }
                else {
                    alert(json.message || "Failed to delete item.");
                }
            }
            catch (e) {
                alert("Server error.");
            }
        });
    }
    if (modalClose)
        modalClose.addEventListener("click", closeModal);
    if (renewBtn)
        renewBtn.addEventListener("click", () => updateStatus("renew"));
    if (flagBtn)
        flagBtn.addEventListener("click", () => updateStatus("flag"));
    if (renDeleteCloseBtn)
        renDeleteCloseBtn.addEventListener("click", closeDeleteModal);
    if (renDeleteCancelBtn)
        renDeleteCancelBtn.addEventListener("click", closeDeleteModal);
    if (searchBox)
        searchBox.addEventListener("input", renderLedger);
    if (statusFilter)
        statusFilter.addEventListener("change", renderLedger);
    loadLedger();
});
