"use strict";
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("tableBody");
    const searchInput = document.querySelector(".search-wrap input");
    const filterType = document.getElementById("filterType");
    const filterStatus = document.getElementById("filterStatus");
    const addBtn = document.getElementById("addScholarshipBtn");
    const schFormOverlay = document.getElementById("schFormOverlay");
    const schFormTitle = document.getElementById("schFormTitle");
    const schFormCloseBtn = document.getElementById("schFormCloseBtn");
    const schFormCancelBtn = document.getElementById("schFormCancelBtn");
    const schForm = document.getElementById("schForm");
    const schId = document.getElementById("schId");
    const schName = document.getElementById("schName");
    const schCode = document.getElementById("schCode");
    const schType = document.getElementById("schType");
    const schGwa = document.getElementById("schGwa");
    const schSlots = document.getElementById("schSlots");
    const schCoverage = document.getElementById("schCoverage");
    const schViewOverlay = document.getElementById("schViewOverlay");
    const schViewCloseBtn = document.getElementById("schViewCloseBtn");
    const schViewCloseBtn2 = document.getElementById("schViewCloseBtn2");
    const schViewBody = document.getElementById("schViewBody");
    const schDeleteOverlay = document.getElementById("schDeleteOverlay");
    const schDeleteCloseBtn = document.getElementById("schDeleteCloseBtn");
    const schDeleteCancelBtn = document.getElementById("schDeleteCancelBtn");
    const schDeleteConfirmBtn = document.getElementById("schDeleteConfirmBtn");
    const schDeleteTarget = document.getElementById("schDeleteTarget");
    let scholarships = [];
    let deletingSchId = null;
    async function loadScholarships() {
        try {
            const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
            const res = await fetch(`${apiPath}/list_scholarships.php`);
            const json = await res.json();
            if (json.success) {
                scholarships = json.data;
                populateFilterTypes();
                renderScholarships();
            }
        }
        catch (e) {
            console.error("Failed to load scholarships:", e);
        }
    }
    function populateFilterTypes() {
        if (!filterType)
            return;
        const types = Array.from(new Set(scholarships.map((s) => s.type))).filter(Boolean);
        filterType.innerHTML = '<option value="all">All Scholarship Types</option>';
        types.forEach(t => {
            const opt = document.createElement("option");
            opt.value = t;
            opt.textContent = t;
            filterType.appendChild(opt);
        });
    }
    function renderScholarships() {
        if (!tableBody)
            return;
        const query = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const typeVal = filterType ? filterType.value.toLowerCase() : "all";
        const statusVal = filterStatus ? filterStatus.value.toLowerCase() : "all";
        const filtered = scholarships.filter((s) => {
            const matchQuery = s.name.toLowerCase().includes(query) || s.code.toLowerCase().includes(query);
            const matchType = typeVal === "all" || s.type.toLowerCase() === typeVal;
            const matchStatus = statusVal === "all" || statusVal === "all status" || s.status.toLowerCase() === statusVal;
            return matchQuery && matchType && matchStatus;
        });
        tableBody.innerHTML = "";
        if (filtered.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 24px; color: #6b7280;">No scholarships found.</td></tr>`;
            return;
        }
        filtered.forEach((s) => {
            const tr = document.createElement("tr");
            const badgeClass = s.status === "active" ? "badge-active" : "badge-inactive";
            tr.innerHTML = `
        <td>
          <strong>${s.name} (${s.code})</strong>
          <div style="font-size:12px; color:#6b7280;">${s.coverage || s.description || 'Standard Benefit Coverage'}</div>
        </td>
        <td>${s.type}</td>
        <td><span class="font-mono">${s.slotsAvailable} / ${s.slots}</span></td>
        <td><span class="status-badge ${badgeClass}">${s.status}</span></td>
        <td class="actions-cell">
          <button type="button" class="btn-icon-action edit" title="Edit Program" onclick="editScholarship(event, ${s.id})">
            <i data-lucide="pencil"></i>
          </button>
          <button type="button" class="btn-icon-action delete" title="Delete Program" onclick="confirmDeleteScholarship(event, ${s.id})">
            <i data-lucide="trash-2"></i>
          </button>
        </td>
      `;
            tr.addEventListener("click", () => openViewModal(s));
            tableBody.appendChild(tr);
        });
        if (typeof lucide !== "undefined")
            lucide.createIcons();
    }
    function openViewModal(s) {
        if (!schViewOverlay || !schViewBody)
            return;
        const statusClass = s.status === 'active' ? 'badge-active' : 'badge-inactive';
        schViewBody.innerHTML = `
      <div class="view-detail-grid">
        <div class="detail-item full-width">
          <span class="detail-label">Program Name</span>
          <span class="detail-value highlight">${s.name} (${s.code})</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Category / Type</span>
          <span class="detail-value">${s.type}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Status</span>
          <span class="status-badge ${statusClass}">${s.status}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">GWA Requirement</span>
          <span class="detail-value mono font-mono"><= ${s.gwaRequirement || '1.75'}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Total Slots Capacity</span>
          <span class="detail-value font-mono">${s.slots} (${s.slotsAvailable} available)</span>
        </div>
        <div class="detail-item full-width">
          <span class="detail-label">Benefit Coverage & Overview</span>
          <span class="detail-value">${s.coverage || s.description || 'Full Tuition Coverage'}</span>
        </div>
      </div>
    `;
        schViewOverlay.classList.add("open");
    }
    function closeViewModal() {
        if (schViewOverlay)
            schViewOverlay.classList.remove("open");
    }
    function openFormModal(editItem = null) {
        if (!schFormOverlay)
            return;
        if (editItem) {
            if (schFormTitle)
                schFormTitle.textContent = "Edit Scholarship Program";
            if (schId)
                schId.value = String(editItem.id);
            if (schName)
                schName.value = editItem.name;
            if (schCode)
                schCode.value = editItem.code;
            if (schType)
                schType.value = editItem.type;
            if (schGwa)
                schGwa.value = String(editItem.gwaRequirement);
            if (schSlots)
                schSlots.value = String(editItem.slots);
            if (schCoverage)
                schCoverage.value = editItem.coverage || '';
        }
        else {
            if (schFormTitle)
                schFormTitle.textContent = "Add Scholarship Program";
            if (schForm)
                schForm.reset();
            if (schId)
                schId.value = "";
        }
        schFormOverlay.classList.add("open");
    }
    function closeFormModal() {
        if (schFormOverlay)
            schFormOverlay.classList.remove("open");
        if (schForm)
            schForm.reset();
    }
    window.editScholarship = function (event, id) {
        event.stopPropagation();
        const item = scholarships.find((s) => s.id === id);
        if (item)
            openFormModal(item);
    };
    window.confirmDeleteScholarship = function (event, id) {
        event.stopPropagation();
        const item = scholarships.find((s) => s.id === id);
        if (!item)
            return;
        deletingSchId = id;
        if (schDeleteTarget)
            schDeleteTarget.textContent = item.name;
        if (schDeleteOverlay)
            schDeleteOverlay.classList.add("open");
    };
    function closeDeleteModal() {
        if (schDeleteOverlay)
            schDeleteOverlay.classList.remove("open");
        deletingSchId = null;
    }
    if (schForm) {
        schForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(schForm);
            try {
                const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
                const res = await fetch(`${apiPath}/list_scholarships.php`, { method: "POST", body: formData });
                const json = await res.json();
                if (json.success) {
                    closeFormModal();
                    loadScholarships();
                }
                else {
                    alert(json.message || "Failed to save scholarship.");
                }
            }
            catch (err) {
                alert("Server error.");
            }
        });
    }
    if (schDeleteConfirmBtn) {
        schDeleteConfirmBtn.addEventListener("click", async () => {
            if (!deletingSchId)
                return;
            try {
                const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
                const res = await fetch(`${apiPath}/delete_scholarship.php?id=${deletingSchId}`, { method: "POST" });
                const json = await res.json();
                if (json.success) {
                    closeDeleteModal();
                    loadScholarships();
                }
                else {
                    alert(json.message || "Failed to delete.");
                }
            }
            catch (e) {
                alert("Server error.");
            }
        });
    }
    if (addBtn)
        addBtn.addEventListener("click", () => openFormModal());
    if (schFormCloseBtn)
        schFormCloseBtn.addEventListener("click", closeFormModal);
    if (schFormCancelBtn)
        schFormCancelBtn.addEventListener("click", closeFormModal);
    if (schViewCloseBtn)
        schViewCloseBtn.addEventListener("click", closeViewModal);
    if (schViewCloseBtn2)
        schViewCloseBtn2.addEventListener("click", closeViewModal);
    if (schDeleteCloseBtn)
        schDeleteCloseBtn.addEventListener("click", closeDeleteModal);
    if (schDeleteCancelBtn)
        schDeleteCancelBtn.addEventListener("click", closeDeleteModal);
    if (searchInput)
        searchInput.addEventListener("input", renderScholarships);
    if (filterType)
        filterType.addEventListener("change", renderScholarships);
    if (filterStatus)
        filterStatus.addEventListener("change", renderScholarships);
    loadScholarships();
});
