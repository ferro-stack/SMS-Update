"use strict";
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("tableBody");
    const searchInput = document.querySelector(".search-wrap input");
    const filterType = document.getElementById("filterType");
    const filterStatus = document.getElementById("filterStatus");
    const exportBtn = document.querySelector(".btn-export");
    const addRecordBtn = document.getElementById("addRecordBtn");
    const recFormOverlay = document.getElementById("recFormOverlay");
    const recFormTitle = document.getElementById("recFormTitle");
    const recFormCloseBtn = document.getElementById("recFormCloseBtn");
    const recFormCancelBtn = document.getElementById("recFormCancelBtn");
    const recForm = document.getElementById("recForm");
    const recId = document.getElementById("recId");
    const recStudentId = document.getElementById("recStudentId");
    const recName = document.getElementById("recName");
    const recType = document.getElementById("recType");
    const recStatus = document.getElementById("recStatus");
    const recSemester = document.getElementById("recSemester");
    const recSy = document.getElementById("recSy");
    const recRemarks = document.getElementById("recRemarks");
    const recViewOverlay = document.getElementById("recViewOverlay");
    const recViewCloseBtn = document.getElementById("recViewCloseBtn");
    const recViewCloseBtn2 = document.getElementById("recViewCloseBtn2");
    const recViewBody = document.getElementById("recViewBody");
    const recDeleteOverlay = document.getElementById("recDeleteOverlay");
    const recDeleteCloseBtn = document.getElementById("recDeleteCloseBtn");
    const recDeleteCancelBtn = document.getElementById("recDeleteCancelBtn");
    const recDeleteConfirmBtn = document.getElementById("recDeleteConfirmBtn");
    const recDeleteTarget = document.getElementById("recDeleteTarget");
    let recordsData = [];
    let deletingRecordId = null;
    async function loadRecords() {
        try {
            const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
            const res = await fetch(`${apiPath}/list_records.php`);
            const json = await res.json();
            if (json.success) {
                recordsData = json.data;
                populateFilterTypes();
                renderRecords();
            }
        }
        catch (e) {
            console.error("Failed to load records:", e);
        }
    }
    function populateFilterTypes() {
        if (!filterType)
            return;
        const types = Array.from(new Set(recordsData.map((r) => r.scholarshipType))).filter(Boolean);
        filterType.innerHTML = '<option value="all">All Scholarship Types</option>';
        types.forEach(t => {
            const opt = document.createElement("option");
            opt.value = t;
            opt.textContent = t;
            filterType.appendChild(opt);
        });
    }
    function renderRecords() {
        if (!tableBody)
            return;
        const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const typeVal = filterType ? filterType.value.toLowerCase() : "all";
        const statusVal = filterStatus ? filterStatus.value.toLowerCase() : "all";
        const filtered = recordsData.filter((r) => {
            const matchQuery = r.name.toLowerCase().includes(query) || r.studentId.toLowerCase().includes(query);
            const matchType = typeVal === "all" || r.scholarshipType.toLowerCase() === typeVal;
            const matchStatus = statusVal === "all" || statusVal === "all status" || r.status.toLowerCase() === statusVal;
            return matchQuery && matchType && matchStatus;
        });
        tableBody.innerHTML = "";
        if (filtered.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 24px; color: #6b7280;">No records found.</td></tr>`;
            return;
        }
        filtered.forEach((r) => {
            const tr = document.createElement("tr");
            const badgeClass = r.status === "approved" ? "badge-approved" : (r.status === "rejected" ? "badge-rejected" : "badge-pending");
            tr.innerHTML = `
        <td><strong class="font-mono">${r.studentId}</strong></td>
        <td>${r.name}</td>
        <td>${r.scholarshipType}</td>
        <td><span class="status-badge ${badgeClass}">${r.status}</span></td>
        <td>${r.semester}</td>
        <td><span class="font-mono">${r.sy}</span></td>
        <td><span class="font-mono">${r.dateEvaluated}</span></td>
        <td class="actions-cell">
          <button type="button" class="btn-icon-action edit" title="Edit Record" onclick="editRecord(event, ${r.id})">
            <i data-lucide="pencil"></i>
          </button>
          <button type="button" class="btn-icon-action delete" title="Delete Record" onclick="confirmDeleteRecord(event, ${r.id})">
            <i data-lucide="trash-2"></i>
          </button>
        </td>
      `;
            tr.addEventListener("click", () => openViewModal(r));
            tableBody.appendChild(tr);
        });
        if (typeof lucide !== "undefined")
            lucide.createIcons();
    }
    function openViewModal(r) {
        if (!recViewOverlay || !recViewBody)
            return;
        const statusClass = r.status === 'approved' ? 'badge-approved' : 'badge-rejected';
        recViewBody.innerHTML = `
      <div class="view-detail-grid">
        <div class="detail-item">
          <span class="detail-label">Student ID</span>
          <span class="detail-value mono font-mono">${r.studentId}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Outcome</span>
          <span class="status-badge ${statusClass}">${r.status}</span>
        </div>
        <div class="detail-item full-width">
          <span class="detail-label">Student Name</span>
          <span class="detail-value highlight">${r.name}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Scholarship Type</span>
          <span class="detail-value">${r.scholarshipType}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Term & SY</span>
          <span class="detail-value">${r.semester} (<span class="font-mono">${r.sy}</span>)</span>
        </div>
        <div class="detail-item full-width">
          <span class="detail-label">Evaluation Date</span>
          <span class="detail-value font-mono">${r.dateEvaluated}</span>
        </div>
        <div class="detail-item full-width">
          <span class="detail-label">Remarks</span>
          <span class="detail-value remarks">${r.remarks || 'No remarks recorded.'}</span>
        </div>
      </div>
    `;
        recViewOverlay.classList.add("open");
    }
    function closeViewModal() {
        if (recViewOverlay)
            recViewOverlay.classList.remove("open");
    }
    function openFormModal(editItem = null) {
        if (!recFormOverlay)
            return;
        if (editItem) {
            if (recFormTitle)
                recFormTitle.textContent = "Edit Evaluation Record";
            if (recId)
                recId.value = String(editItem.id);
            if (recStudentId)
                recStudentId.value = editItem.studentId;
            if (recName)
                recName.value = editItem.name;
            if (recType)
                recType.value = editItem.scholarshipType;
            if (recStatus)
                recStatus.value = editItem.status;
            if (recSemester)
                recSemester.value = editItem.semester;
            if (recSy)
                recSy.value = editItem.sy;
            if (recRemarks)
                recRemarks.value = editItem.remarks || '';
        }
        else {
            if (recFormTitle)
                recFormTitle.textContent = "Add Evaluation Record";
            if (recForm)
                recForm.reset();
            if (recId)
                recId.value = "";
        }
        recFormOverlay.classList.add("open");
    }
    function closeFormModal() {
        if (recFormOverlay)
            recFormOverlay.classList.remove("open");
        if (recForm)
            recForm.reset();
    }
    window.editRecord = function (event, id) {
        event.stopPropagation();
        const item = recordsData.find((r) => r.id === id);
        if (item)
            openFormModal(item);
    };
    window.confirmDeleteRecord = function (event, id) {
        event.stopPropagation();
        const item = recordsData.find((r) => r.id === id);
        if (!item)
            return;
        deletingRecordId = id;
        if (recDeleteTarget)
            recDeleteTarget.textContent = item.name;
        if (recDeleteOverlay)
            recDeleteOverlay.classList.add("open");
    };
    function closeDeleteModal() {
        if (recDeleteOverlay)
            recDeleteOverlay.classList.remove("open");
        deletingRecordId = null;
    }
    if (recForm) {
        recForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(recForm);
            try {
                const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
                const res = await fetch(`${apiPath}/list_records.php`, { method: "POST", body: formData });
                const json = await res.json();
                if (json.success) {
                    closeFormModal();
                    loadRecords();
                }
                else {
                    alert(json.message || "Failed to save record.");
                }
            }
            catch (err) {
                alert("Server error.");
            }
        });
    }
    if (recDeleteConfirmBtn) {
        recDeleteConfirmBtn.addEventListener("click", async () => {
            if (!deletingRecordId)
                return;
            try {
                const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
                const res = await fetch(`${apiPath}/delete_record.php?id=${deletingRecordId}`, { method: "POST" });
                const json = await res.json();
                if (json.success) {
                    closeDeleteModal();
                    loadRecords();
                }
                else {
                    alert(json.message || "Failed to delete record.");
                }
            }
            catch (e) {
                alert("Server error.");
            }
        });
    }
    if (addRecordBtn)
        addRecordBtn.addEventListener("click", () => openFormModal());
    if (recFormCloseBtn)
        recFormCloseBtn.addEventListener("click", closeFormModal);
    if (recFormCancelBtn)
        recFormCancelBtn.addEventListener("click", closeFormModal);
    if (recViewCloseBtn)
        recViewCloseBtn.addEventListener("click", closeViewModal);
    if (recViewCloseBtn2)
        recViewCloseBtn2.addEventListener("click", closeViewModal);
    if (recDeleteCloseBtn)
        recDeleteCloseBtn.addEventListener("click", closeDeleteModal);
    if (recDeleteCancelBtn)
        recDeleteCancelBtn.addEventListener("click", closeDeleteModal);
    if (searchInput)
        searchInput.addEventListener("input", renderRecords);
    if (filterType)
        filterType.addEventListener("change", renderRecords);
    if (filterStatus)
        filterStatus.addEventListener("change", renderRecords);
    if (exportBtn) {
        exportBtn.addEventListener("click", () => {
            alert("Exporting scholarship records to CSV...");
        });
    }
    loadRecords();
});
