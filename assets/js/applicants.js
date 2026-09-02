"use strict";
const STEPS = [
    { key: "personal", label: "Personal" },
    { key: "academic", label: "Academic" },
    { key: "scholarship", label: "Scholarship" },
    { key: "documents", label: "Documents" },
];
function getEl(id) {
    return document.getElementById(id);
}
let currentIndex = 0;
let furthestIndex = 0;
const formData = {};
let loadedApplicants = [];
let editingApplicantId = null;
let deletingApplicantId = null;
const checkIcon = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`;
const ICONS = {
    personal: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>`,
    academic: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.5 2.5 3 6 3s6-1.5 6-3v-5"/></svg>`,
    scholarship: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5"/></svg>`,
    documents: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>`,
};
function formatPhoneNumber(val) {
    if (!val)
        return "";
    const isPlus = val.trim().startsWith("+");
    let digits = val.replace(/\D/g, "");
    if (isPlus || digits.startsWith("63")) {
        if (digits.startsWith("63"))
            digits = digits.slice(2);
        digits = digits.slice(0, 10);
        const p1 = digits.slice(0, 3);
        const p2 = digits.slice(3, 6);
        const p3 = digits.slice(6, 10);
        return `+63${p1 ? ' ' + p1 : ''}${p2 ? ' ' + p2 : ''}${p3 ? ' ' + p3 : ''}`;
    }
    digits = digits.slice(0, 11);
    const p1 = digits.slice(0, 4);
    const p2 = digits.slice(4, 7);
    const p3 = digits.slice(7, 11);
    return `${p1}${p2 ? ' ' + p2 : ''}${p3 ? ' ' + p3 : ''}`;
}
async function loadTableData() {
    try {
        const filterStatus = getEl("filterStatus");
        const statusVal = filterStatus ? filterStatus.value : 'all';
        loadedApplicants = await window.apiListApplicants(statusVal === 'all' ? '' : statusVal);
        renderTable();
    }
    catch (e) {
        console.error("Error loading applicants:", e);
    }
}
function renderTable() {
    const tableBody = getEl("tableBody");
    const emptyState = getEl("emptyState");
    const searchInput = getEl("searchInput");
    const filterType = getEl("filterType");
    const filterStatus = getEl("filterStatus");
    if (!tableBody)
        return;
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const typeVal = filterType ? filterType.value : 'all';
    const statusVal = filterStatus ? filterStatus.value : 'all';
    const filtered = loadedApplicants.filter(app => {
        const nameMatch = (app.name || '').toLowerCase().includes(query) || (app.studentId || '').toLowerCase().includes(query);
        const typeMatch = typeVal === 'all' || (app.scholarshipType || '').toLowerCase() === typeVal.toLowerCase();
        const statusMatch = statusVal === 'all' || (app.status || '').toLowerCase() === statusVal.toLowerCase();
        return nameMatch && typeMatch && statusMatch;
    });
    tableBody.innerHTML = '';
    if (filtered.length === 0) {
        if (emptyState) {
            emptyState.style.display = 'block';
            emptyState.classList.add('show');
        }
        return;
    }
    if (emptyState) {
        emptyState.style.display = 'none';
        emptyState.classList.remove('show');
    }
    filtered.forEach(app => {
        const tr = document.createElement('tr');
        const statusLower = (app.status || '').toLowerCase();
        const statusBadgeClass = statusLower === 'approved' ? 'badge-approved' : (statusLower === 'rejected' ? 'badge-rejected' : 'badge-pending');
        const formattedStatus = app.status ? app.status.charAt(0).toUpperCase() + app.status.slice(1) : 'Pending';
        tr.innerHTML = `
      <td><strong class="font-mono">${app.studentId || '-'}</strong></td>
      <td>${app.name}</td>
      <td>${app.scholarshipType}</td>
      <td><span class="status-badge ${statusBadgeClass}">${formattedStatus}</span></td>
      <td><span class="font-mono">${(app.createdAt || '').split(' ')[0] || '2026-08-10'}</span></td>
      <td class="actions-cell">
        <button type="button" class="btn-icon-action edit" title="Edit Applicant" onclick="editApplicant(event, ${app.id})">
          <i data-lucide="pencil"></i>
        </button>
        <button type="button" class="btn-icon-action delete" title="Delete Applicant" onclick="confirmDeleteApplicant(event, ${app.id})">
          <i data-lucide="trash-2"></i>
        </button>
      </td>
    `;
        tr.addEventListener('click', (e) => {
            if (e.target && e.target.closest && e.target.closest('.actions-cell, .btn-icon-action, button, svg, path')) {
                return;
            }
            openViewModal(app);
        });
        tableBody.appendChild(tr);
    });
    if (typeof lucide !== 'undefined')
        lucide.createIcons();
}

function openViewModal(app) {

    console.log("Applicant:", app);
    console.log("Address:", app.address);
    console.log("Latitude:", app.latitude);
    console.log("Longitude:", app.longitude);

    const viewOverlay = getEl("viewOverlay");
    const viewBody = getEl("viewBody");
    if (!viewOverlay || !viewBody)
        return;
    const statusLower = (app.status || '').toLowerCase();
    const statusClass = statusLower === 'approved' ? 'badge-approved' : (statusLower === 'rejected' ? 'badge-rejected' : 'badge-pending');
    const formattedStatus = app.status ? app.status.charAt(0).toUpperCase() + app.status.slice(1) : 'Pending';
    viewBody.innerHTML = `
    <div class="view-detail-grid">
      <div class="detail-item">
        <span class="detail-label">Student ID</span>
        <span class="detail-value mono font-mono">${app.studentId}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Status</span>
        <span class="status-badge ${statusClass}">${formattedStatus}</span>
      </div>
      <div class="detail-item full-width">
        <span class="detail-label">Full Name</span>
        <span class="detail-value highlight">${app.name}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Email Address</span>
        <span class="detail-value">${app.email}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Phone Number</span>
        <span class="detail-value font-mono">${formatPhoneNumber(app.phone || 'N/A')}</span>
      </div>
      <div class="detail-item full-width">
        <span class="detail-label">School / Program</span>
        <span class="detail-value">${app.school || 'College of Maasin'} — ${app.program}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Scholarship Type</span>
        <span class="detail-value">${app.scholarshipType}</span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Current GPA / GWA</span>
        <span class="detail-value font-mono">${app.gpa || app.gwa || 'N/A'}</span>
      </div>
      <div class="detail-item full-width">
        <span class="detail-label">Home Address</span>
        <span class="detail-value">${app.address || 'N/A'}</span>
      </div>
      <div id="applicantMap" style="height: 300px; width: 200%; margin-top: 15px;"></div>

      ${app.remarks ? `
      <div class="detail-item full-width">
        <span class="detail-label">Remarks</span>
        <span class="detail-value remarks">${app.remarks}</span>
      </div>` : ''}
    </div>
  `

  if (app.latitude && app.longitude) {
      const map = L.map("applicantMap").setView(
          [app.latitude, app.longitude],
          15
      );

      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      L.marker([app.latitude, app.longitude])
          .addTo(map)
          .bindPopup(app.address || "Applicant location")
          .openPopup();

      setTimeout(() => {
          map.invalidateSize();
      }, 200);
  }

  viewOverlay.classList.add("open");

}

function closeViewModal() {
    const viewOverlay = getEl("viewOverlay");
    if (viewOverlay)
        viewOverlay.classList.remove("open");
}
window.editApplicant = async function (event, id) {
    if (event && typeof event.stopPropagation === "function") event.stopPropagation();
    closeViewModal();
    try {
        const app = await window.apiGetApplicant(id);
        editingApplicantId = id;
        const fId = document.querySelector('[data-field="studentId"]');
        const fFirst = document.querySelector('[data-field="firstName"]');
        const fLast = document.querySelector('[data-field="lastName"]');
        const fEmail = document.querySelector('[data-field="email"]');
        const fPhone = document.querySelector('[data-field="phone"]');
        const fBirth = document.querySelector('[data-field="birthdate"]');
        const fAddr = document.querySelector('[data-field="address"]');
        const fSchool = document.querySelector('[data-field="school"]');
        const fProg = document.querySelector('[data-field="program"]');
        const fYear = document.querySelector('[data-field="yearLevel"]');
        const fGpa = document.querySelector('[data-field="gpa"]');
        const fType = document.querySelector('[data-field="scholarshipType"]');
        const fEssay = document.querySelector('[data-field="essay"]');
        if (fId)
            fId.value = app.studentId || '';
        if (fFirst)
            fFirst.value = app.firstName || '';
        if (fLast)
            fLast.value = app.lastName || '';
        if (fEmail)
            fEmail.value = app.email || '';
        if (fPhone)
            fPhone.value = app.phone || '';
        if (fBirth)
            fBirth.value = app.birthdate || '';
        if (fAddr)
            fAddr.value = app.address || '';
        if (fSchool)
            fSchool.value = app.school || '';
        if (fProg)
            fProg.value = app.program || '';
        if (fYear)
            fYear.value = app.yearLevel || '';
        if (fGpa)
            fGpa.value = app.gpa || '';
        if (fType)
            fType.value = app.scholarshipType || '';
        if (fEssay)
            fEssay.value = app.essay || '';
        openModal(true);
    }
    catch (e) {
        alert("Could not load applicant data for edit.");
    }
};
window.confirmDeleteApplicant = function (event, id) {
    if (event && typeof event.stopPropagation === "function") {
        event.stopPropagation();
    }
    closeViewModal();
    const targetId = Number(id);
    if (!targetId)
        return;
    deletingApplicantId = targetId;
    const item = loadedApplicants.find(a => Number(a.id) === targetId);
    const deleteTargetName = getEl("deleteTargetName");
    const deleteConfirmOverlay = getEl("deleteConfirmOverlay");
    if (deleteTargetName)
        deleteTargetName.textContent = item ? (item.name || `${item.firstName || ''} ${item.lastName || ''}`.trim()) : `Applicant #${targetId}`;
    if (deleteConfirmOverlay)
        deleteConfirmOverlay.classList.add("open");
};

function closeDeleteModal() {
    const deleteConfirmOverlay = getEl("deleteConfirmOverlay");
    if (deleteConfirmOverlay)
        deleteConfirmOverlay.classList.remove("open");
    deletingApplicantId = null;
}
function renderProgress() {
    const progressBar = getEl("progressBar");
    if (!progressBar)
        return;
    progressBar.innerHTML = "";
    STEPS.forEach((step, i) => {
        const wrap = document.createElement("div");
        wrap.className = "progress-step";
        const isCompleted = i < currentIndex;
        const isCurrent = i === currentIndex;
        const isReachable = i <= furthestIndex;
        const btn = document.createElement("button");
        btn.className = "step-btn";
        btn.type = "button";
        btn.disabled = !isReachable;
        btn.innerHTML = `
      <span class="step-circle ${isCompleted ? "completed" : isCurrent ? "current" : ""}">
        ${isCompleted ? checkIcon : ICONS[step.key]}
      </span>
      <span class="step-label ${isCompleted ? "completed" : isCurrent ? "current" : ""}">${step.label}</span>
    `;
        btn.addEventListener("click", () => {
            if (isReachable) {
                currentIndex = i;
                render();
            }
        });
        wrap.appendChild(btn);
        if (i < STEPS.length - 1) {
            const line = document.createElement("div");
            line.className = "step-line" + (i < currentIndex ? " completed" : "");
            wrap.appendChild(line);
        }
        progressBar.appendChild(wrap);
    });
}
function render() {
    const stepCounter = getEl("stepCounter");
    const modalFooter = getEl("modalFooter");
    const backBtn = getEl("backBtn");
    const nextBtn = getEl("nextBtn");
    document.querySelectorAll(".step-panel").forEach(panel => {
        panel.classList.remove("active");
    });
    const activePanel = document.querySelector(`.step-panel[data-step="${currentIndex}"]`);
    if (activePanel)
        activePanel.classList.add("active");
    renderProgress();
    if (stepCounter)
        stepCounter.textContent = `Step ${currentIndex + 1} of ${STEPS.length}`;
    if (currentIndex === 0) {
        if (backBtn)
            backBtn.style.display = "none";
        if (modalFooter)
            modalFooter.style.justifyContent = "flex-end";
    }
    else {
        if (backBtn)
            backBtn.style.display = "inline-flex";
        if (modalFooter)
            modalFooter.style.justifyContent = "space-between";
    }
    if (nextBtn) {
        nextBtn.innerHTML = currentIndex === STEPS.length - 1
            ? (editingApplicantId ? "Update Application" : "Submit Application")
            : `Next <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>`;
    }
    if (modalFooter)
        modalFooter.style.display = "flex";
}
function showSuccess() {
    const successMsg = getEl("successMsg");
    const modalFooter = getEl("modalFooter");
    document.querySelectorAll(".step-panel").forEach(panel => panel.classList.remove("active"));
    const successPanel = document.querySelector('.step-panel[data-step="success"]');
    if (successPanel)
        successPanel.classList.add("active");
    if (modalFooter)
        modalFooter.style.display = "none";
    const name = `${formData.firstName || "The applicant"} ${formData.lastName || ""}`.trim();
    if (successMsg)
        successMsg.textContent = `${name} has been successfully ${editingApplicantId ? "updated" : "added"} in the system.`;
}
function openModal(isEdit = false) {
    const overlay = getEl("overlay");
    if (!isEdit) {
        editingApplicantId = null;
        currentIndex = 0;
        furthestIndex = 0;
        document.querySelectorAll("[data-field]").forEach(el => {
            el.value = "";
            el.classList.remove("error");
        });
        document.querySelectorAll(".hint").forEach(el => {
            el.textContent = "PDF, JPG, or PNG · max 5MB";
        });
        document.querySelectorAll(".upload-action").forEach(el => {
            el.textContent = "Upload";
        });
        Object.keys(formData).forEach(k => delete formData[k]);
    }
    render();
    if (overlay)
        overlay.classList.add("open");
}
function closeModal() {
    const overlay = getEl("overlay");
    if (overlay)
        overlay.classList.remove("open");
    currentIndex = 0;
    furthestIndex = 0;
    editingApplicantId = null;
    document.querySelectorAll("[data-field]").forEach(el => {
        el.value = "";
        el.classList.remove("error");
    });
    document.querySelectorAll(".hint").forEach(el => {
        el.textContent = "PDF, JPG, or PNG · max 5MB";
    });
    document.querySelectorAll(".upload-action").forEach(el => {
        el.textContent = "Upload";
    });
    Object.keys(formData).forEach(k => delete formData[k]);
    render();
    loadTableData();
}
function initApplicantsPage() {
    const applicantForm = getEl("applicantForm");
    if (applicantForm) {
        applicantForm.addEventListener("submit", (e) => {
            e.preventDefault();
        });
    }
    document.addEventListener("click", (e) => {
        const target = e.target;
        if (target && target.closest("#openBtn, #emptyStateAddBtn")) {
            e.preventDefault();
            openModal(false);
        }
    });
    document.querySelectorAll("input[data-field], select[data-field], textarea[data-field]").forEach(el => {
        el.addEventListener("change", () => {
            const key = el.dataset.field;
            if (!key)
                return;
            if (el instanceof HTMLInputElement && el.type === "file") {
                const file = el.files ? el.files[0] : null;
                formData[key] = file || null;
                const hint = document.querySelector(`.hint[data-hint="${key}"]`);
                const action = document.querySelector(`.upload-action[data-action="${key}"]`);
                if (file) {
                    if (hint)
                        hint.textContent = file.name;
                    if (action)
                        action.textContent = "Replace";
                }
                else {
                    if (hint)
                        hint.textContent = "PDF, JPG, or PNG · max 5MB";
                    if (action)
                        action.textContent = "Upload";
                }
            }
            else {
                formData[key] = el.value;
            }
        });
    });
    document.querySelectorAll(".upload-action").forEach(actionEl => {
        actionEl.addEventListener("click", () => {
            const action = actionEl.dataset.action;
            if (action) {
                const fileInput = document.querySelector(`input[data-field="${action}"]`);
                if (fileInput)
                    fileInput.click();
            }
        });
    });
    const closeBtn = getEl("closeBtn");
    if (closeBtn)
        closeBtn.addEventListener("click", closeModal);
    const doneBtn = getEl("doneBtn");
    if (doneBtn)
        doneBtn.addEventListener("click", closeModal);
    const backBtn = getEl("backBtn");
    if (backBtn) {
        backBtn.addEventListener("click", () => {
            if (currentIndex > 0) {
                currentIndex--;
                render();
            }
        });
    }
    const nextBtn = getEl("nextBtn");
    if (nextBtn) {
        nextBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            document.querySelectorAll(".error").forEach(el => el.classList.remove("error"));
            const currentPanel = document.querySelector(`.step-panel[data-step="${currentIndex}"]`);
            if (!currentPanel)
                return;
            const requiredFields = currentPanel.querySelectorAll("[required]");
            let hasError = false;
            requiredFields.forEach(field => {
                if (field instanceof HTMLInputElement && field.type === "file") {
                    if (!editingApplicantId && (!field.files || field.files.length === 0)) {
                        field.classList.add("error");
                        if (!hasError)
                            field.focus();
                        hasError = true;
                    }
                }
                else {
                    if (field.value.trim() === "") {
                        field.classList.add("error");
                        if (!hasError)
                            field.focus();
                        hasError = true;
                    }
                }
            });
            if (hasError)
                return;
            if (currentIndex === STEPS.length - 1) {
                if (window.isSavingApplicant)
                    return;
                const form = document.getElementById("applicantForm");
                if (!form)
                    return;
                window.isSavingApplicant = true;
                if (nextBtn) {
                    nextBtn.disabled = true;
                    nextBtn.textContent = "Saving...";
                }
                try {
                    await window.apiSaveApplicant(form, editingApplicantId);
                    await loadTableData();
                    if (typeof window.updateNavCounts === "function") {
                        window.updateNavCounts();
                    }
                    showSuccess();
                }
                catch (err) {
                    alert(err.message || "Failed to submit application.");
                }
                finally {
                    window.isSavingApplicant = false;
                    if (nextBtn) {
                        nextBtn.disabled = false;
                    }
                }
                return;
            }
            currentIndex++;
            furthestIndex = Math.max(furthestIndex, currentIndex);
            render();
        });
    }
    const overlay = getEl("overlay");
    if (overlay) {
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay)
                closeModal();
        });
    }
    const deleteConfirmOverlay = getEl("deleteConfirmOverlay");
    if (deleteConfirmOverlay) {
        deleteConfirmOverlay.addEventListener("click", (e) => {
            if (e.target === deleteConfirmOverlay)
                closeDeleteModal();
        });
    }
    const viewOverlay = getEl("viewOverlay");
    if (viewOverlay) {
        viewOverlay.addEventListener("click", (e) => {
            if (e.target === viewOverlay)
                closeViewModal();
        });
    }
    const deleteConfirmBtn = getEl("deleteConfirmBtn");
    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener("click", async () => {
            if (!deletingApplicantId)
                return;
            try {
                const apiPath = (typeof window !== "undefined" && window.API_BASE) ? window.API_BASE : "api";
                const res = await fetch(`${apiPath}/delete_applicant.php?id=${deletingApplicantId}`, { method: "POST" });
                const json = await res.json();
                if (json.success) {
                    closeDeleteModal();
                    await loadTableData();
                    if (typeof window.updateNavCounts === "function") {
                        window.updateNavCounts();
                    }
                }
                else {
                    alert(json.message || "Failed to delete applicant.");
                }
            }
            catch (e) {
                alert("Server error when deleting applicant.");
            }
        });
    }
    const deleteCloseBtn = getEl("deleteCloseBtn");
    if (deleteCloseBtn)
        deleteCloseBtn.addEventListener("click", closeDeleteModal);
    const deleteCancelBtn = getEl("deleteCancelBtn");
    if (deleteCancelBtn)
        deleteCancelBtn.addEventListener("click", closeDeleteModal);
    const viewCloseBtn = getEl("viewCloseBtn");
    if (viewCloseBtn)
        viewCloseBtn.addEventListener("click", closeViewModal);
    const viewCloseBtn2 = getEl("viewCloseBtn2");
    if (viewCloseBtn2)
        viewCloseBtn2.addEventListener("click", closeViewModal);
    const searchInput = getEl("searchInput");
    if (searchInput)
        searchInput.addEventListener("input", renderTable);
    const filterType = getEl("filterType");
    if (filterType)
        filterType.addEventListener("change", renderTable);
    const filterStatus = getEl("filterStatus");
    if (filterStatus)
        filterStatus.addEventListener("change", loadTableData);
    const studentId = document.querySelector('[data-field="studentId"]');
    const phoneNumber = document.querySelector('[data-field="phone"]');
    if (studentId) {
        studentId.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "");
        });
    }
    if (phoneNumber) {
        phoneNumber.addEventListener("input", function () {
            this.value = formatPhoneNumber(this.value);
        });
    }
    render();
    loadTableData();
}
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initApplicantsPage);
}
else {
    initApplicantsPage();
}
