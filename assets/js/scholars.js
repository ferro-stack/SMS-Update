"use strict";
let loadedScholarsList = [];
let editingScholarId = null;
let deletingScholarId = null;

function getScholarEl(id) {
    return document.getElementById(id);
}

async function loadScholarsData() {
    try {
        const deptFilter = getScholarEl("filterDepartment");
        const yearFilter = getScholarEl("filterYear");
        const statusFilter = getScholarEl("filterStatus");
        const searchInput = getScholarEl("searchScholarInput");

        const deptVal = deptFilter ? deptFilter.value : "all";
        const yearVal = yearFilter ? yearFilter.value : "all";
        const statusVal = statusFilter ? statusFilter.value : "above";
        const searchVal = searchInput ? searchInput.value.trim() : "";

        const apiFunc = window.apiListScholars;
        if (typeof apiFunc === "function") {
            loadedScholarsList = await apiFunc(
                deptVal === "all" ? "" : deptVal,
                yearVal === "all" ? "" : yearVal,
                statusVal,
                searchVal
            );
        } else {
            const params = new URLSearchParams();
            if (deptVal !== "all") params.append("department", deptVal);
            if (yearVal !== "all") params.append("year_level", yearVal);
            if (statusVal) params.append("status", statusVal);
            if (searchVal) params.append("search", searchVal);

            const apiBase = window.API_BASE || "api";
            const res = await fetch(`${apiBase}/list_scholars.php?${params.toString()}`);
            const json = await res.json();
            loadedScholarsList = json.data || [];
        }

        renderScholarsTable();
    } catch (err) {
        console.error("Error loading scholars data:", err);
    }
}

function renderScholarsTable() {
    const tbody = getScholarEl("scholarsTableBody");
    const emptyState = getScholarEl("scholarsEmptyState");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!loadedScholarsList || loadedScholarsList.length === 0) {
        if (emptyState) emptyState.style.display = "block";
        return;
    }

    if (emptyState) emptyState.style.display = "none";

    loadedScholarsList.forEach((s) => {
        const tr = document.createElement("tr");
        const isMaintaining = Number(s.gwa) <= 1.50;
        const statusText = s.status || (isMaintaining ? "Active" : "Removed");
        const badgeClass = isMaintaining && statusText.toLowerCase() === "active" ? "badge-maintained" : "badge-removed";
        const gwaClass = isMaintaining ? "gwa-pass" : "gwa-fail";

        tr.innerHTML = `
      <td><strong class="font-mono">${s.student_id}</strong></td>
      <td><strong>${s.name}</strong></td>
      <td><span class="dept-tag">${s.department}</span></td>
      <td><span class="badge-year">Year ${s.year_level}</span></td>
      <td><span class="gwa-pill ${gwaClass}">${Number(s.gwa).toFixed(2)}</span></td>
      <td><span class="font-mono">${s.school_year || '2025-2026'}</span></td>
      <td>
        <span class="${badgeClass}">
          ${statusText} ${isMaintaining ? '(<= 1.50)' : '(Below 1.50)'}
        </span>
      </td>
      <td class="actions-cell">
        <button type="button" class="btn-icon-action edit" title="Edit Scholar" onclick="editScholarEntry(event, ${s.id})">
          <i data-lucide="pencil"></i>
        </button>
        <button type="button" class="btn-icon-action delete" title="Delete Scholar" onclick="confirmDeleteScholarEntry(event, ${s.id})">
          <i data-lucide="trash-2"></i>
        </button>
      </td>
    `;

        tbody.appendChild(tr);
    });

    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }
}

function openScholarModal(isEdit = false) {
    const overlay = getScholarEl("scholarModalOverlay");
    const modalTitle = getScholarEl("scholarModalTitle");
    if (!isEdit) {
        editingScholarId = null;
        if (modalTitle) modalTitle.textContent = "Add New Scholar";
        const form = getScholarEl("scholarForm");
        if (form) form.reset();
    } else {
        if (modalTitle) modalTitle.textContent = "Edit Scholar Record";
    }
    if (overlay) overlay.classList.add("open");
}

function closeScholarModal() {
    const overlay = getScholarEl("scholarModalOverlay");
    if (overlay) overlay.classList.remove("open");
    editingScholarId = null;
}

window.editScholarEntry = async function (event, id) {
    if (event && typeof event.stopPropagation === "function") event.stopPropagation();
    const scholar = loadedScholarsList.find((item) => Number(item.id) === Number(id));
    if (!scholar) return;

    editingScholarId = Number(id);

    const fStudentId = getScholarEl("modalStudentId");
    const fName = getScholarEl("modalName");
    const fDepartment = getScholarEl("modalDepartment");
    const fYearLevel = getScholarEl("modalYearLevel");
    const fGwa = getScholarEl("modalGwa");
    const fSchoolYear = getScholarEl("modalSchoolYear");
    const fRemarks = getScholarEl("modalRemarks");

    if (fStudentId) fStudentId.value = scholar.student_id || "";
    if (fName) fName.value = scholar.name || "";
    if (fDepartment) fDepartment.value = scholar.department || "Information Technology";
    if (fYearLevel) fYearLevel.value = String(scholar.year_level || 1);
    if (fGwa) fGwa.value = String(scholar.gwa || 1.50);
    if (fSchoolYear) fSchoolYear.value = scholar.school_year || "2025-2026";
    if (fRemarks) fRemarks.value = scholar.remarks || "";

    openScholarModal(true);
};

window.confirmDeleteScholarEntry = function (event, id) {
    if (event && typeof event.stopPropagation === "function") event.stopPropagation();
    const scholar = loadedScholarsList.find((item) => Number(item.id) === Number(id));
    if (!scholar) return;

    deletingScholarId = Number(id);
    const targetName = getScholarEl("deleteScholarTargetName");
    const overlay = getScholarEl("deleteScholarConfirmOverlay");

    if (targetName) targetName.textContent = scholar.name;
    if (overlay) overlay.classList.add("open");
};

function closeDeleteScholarModal() {
    const overlay = getScholarEl("deleteScholarConfirmOverlay");
    if (overlay) overlay.classList.remove("open");
    deletingScholarId = null;
}

function initScholarsPage() {
    const addBtn = getScholarEl("addScholarBtn");
    if (addBtn) addBtn.addEventListener("click", () => openScholarModal(false));

    const closeBtn = getScholarEl("scholarModalCloseBtn");
    if (closeBtn) closeBtn.addEventListener("click", closeScholarModal);

    const cancelBtn = getScholarEl("scholarModalCancelBtn");
    if (cancelBtn) cancelBtn.addEventListener("click", closeScholarModal);

    const overlay = getScholarEl("scholarModalOverlay");
    if (overlay) {
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) closeScholarModal();
        });
    }

    const deleteOverlay = getScholarEl("deleteScholarConfirmOverlay");
    if (deleteOverlay) {
        deleteOverlay.addEventListener("click", (e) => {
            if (e.target === deleteOverlay) closeDeleteScholarModal();
        });
    }

    const deleteCloseBtn = getScholarEl("deleteScholarCloseBtn");
    if (deleteCloseBtn) deleteCloseBtn.addEventListener("click", closeDeleteScholarModal);

    const deleteCancelBtn = getScholarEl("deleteScholarCancelBtn");
    if (deleteCancelBtn) deleteCancelBtn.addEventListener("click", closeDeleteScholarModal);

    const deleteConfirmBtn = getScholarEl("deleteScholarConfirmBtn");
    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener("click", async () => {
            if (!deletingScholarId) return;
            try {
                const apiFunc = window.apiDeleteScholar;
                if (typeof apiFunc === "function") {
                    await apiFunc(deletingScholarId);
                } else {
                    const apiBase = window.API_BASE || "api";
                    const formData = new FormData();
                    formData.append("id", String(deletingScholarId));
                    await fetch(`${apiBase}/delete_scholar.php`, { method: "POST", body: formData });
                }
                closeDeleteScholarModal();
                await loadScholarsData();
            } catch (err) {
                closeDeleteScholarModal();
                alert("Failed to delete scholar entry.");
            }
        });
    }

    const scholarForm = getScholarEl("scholarForm");
    if (scholarForm) {
        scholarForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const fStudentId = getScholarEl("modalStudentId");
            const fName = getScholarEl("modalName");
            const fDepartment = getScholarEl("modalDepartment");
            const fYearLevel = getScholarEl("modalYearLevel");
            const fGwa = getScholarEl("modalGwa");
            const fSchoolYear = getScholarEl("modalSchoolYear");
            const fRemarks = getScholarEl("modalRemarks");

            const data = {
                id: editingScholarId,
                student_id: fStudentId ? fStudentId.value.trim() : "",
                name: fName ? fName.value.trim() : "",
                department: fDepartment ? fDepartment.value : "Information Technology",
                year_level: fYearLevel ? Number(fYearLevel.value) : 1,
                gwa: fGwa ? Number(fGwa.value) : 1.50,
                school_year: fSchoolYear ? fSchoolYear.value.trim() : "2025-2026",
                remarks: fRemarks ? fRemarks.value.trim() : "",
            };

            try {
                const apiFunc = window.apiSaveScholar;
                if (typeof apiFunc === "function") {
                    await apiFunc(data);
                } else {
                    const apiBase = window.API_BASE || "api";
                    const formData = new FormData();
                    Object.entries(data).forEach(([k, v]) => formData.append(k, String(v ?? "")));
                    await fetch(`${apiBase}/save_scholar.php`, { method: "POST", body: formData });
                }
                closeScholarModal();
                await loadScholarsData();
            } catch (err) {
                alert(err.message || "Failed to save scholar.");
            }
        });
    }

    const filterDept = getScholarEl("filterDepartment");
    if (filterDept) filterDept.addEventListener("change", loadScholarsData);

    const filterYear = getScholarEl("filterYear");
    if (filterYear) filterYear.addEventListener("change", loadScholarsData);

    const filterStatus = getScholarEl("filterStatus");
    if (filterStatus) filterStatus.addEventListener("change", loadScholarsData);

    const searchInput = getScholarEl("searchScholarInput");
    if (searchInput) searchInput.addEventListener("input", loadScholarsData);

    loadScholarsData();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initScholarsPage);
} else {
    initScholarsPage();
}
