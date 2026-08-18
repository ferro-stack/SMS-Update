// ===========================
// IMPORT GRADES
// ===========================

const gradeFile = document.getElementById("gradeFile") as HTMLInputElement | null;
const gradeBtn = document.getElementById("gradeBtn") as HTMLElement | null;
const gradeFileName = document.getElementById("gradeFileName") as HTMLElement | null;
const gradeDeleteBtn = document.getElementById("gradeDeleteBtn") as HTMLElement | null;

if (gradeBtn && gradeFile && gradeFileName && gradeDeleteBtn) {

  gradeBtn.addEventListener("click", function (): void {
    gradeFile.click();
  });

  gradeFile.addEventListener("change", function (this: HTMLInputElement): void {

    if (this.files && this.files.length > 0) {
      gradeFileName.textContent = this.files[0].name;
      gradeDeleteBtn.style.display = "block";
    } else {
      gradeFileName.textContent = "No file selected";
      gradeDeleteBtn.style.display = "none";
    }

  });

  gradeDeleteBtn.addEventListener("click", function (): void {
    gradeFile.value = "";
    gradeFileName.textContent = "No file selected";
    gradeDeleteBtn.style.display = "none";
  });

  const gradeImportBtn = gradeBtn.parentElement ? gradeBtn.parentElement.querySelector(".import-btn") : null;
  if (gradeImportBtn) {
    gradeImportBtn.addEventListener("click", function (): void {
      if (!gradeFile.files || gradeFile.files.length === 0) {
        alert("Please select an academic file to import.");
        return;
      }
      alert("Academic records imported successfully!");
      gradeFile.value = "";
      gradeFileName.textContent = "No file selected";
      gradeDeleteBtn.style.display = "none";
    });
  }

}
// ===========================
// IMPORT ENROLLMENT
// ===========================

const enrollmentFile = document.getElementById("enrollmentFile") as HTMLInputElement | null;
const enrollmentBtn = document.getElementById("enrollmentBtn") as HTMLElement | null;
const enrollmentFileName = document.getElementById("enrollmentFileName") as HTMLElement | null;
const enrollmentDeleteBtn = document.getElementById("enrollmentDeleteBtn") as HTMLElement | null;

if (enrollmentBtn && enrollmentFile && enrollmentFileName && enrollmentDeleteBtn) {

  enrollmentBtn.addEventListener("click", function (): void {
    enrollmentFile.click();
  });

  enrollmentFile.addEventListener("change", function (this: HTMLInputElement): void {

    if (this.files && this.files.length > 0) {
      enrollmentFileName.textContent = this.files[0].name;
      enrollmentDeleteBtn.style.display = "block";
    } else {
      enrollmentFileName.textContent = "No file selected";
      enrollmentDeleteBtn.style.display = "none";
    }

  });

  enrollmentDeleteBtn.addEventListener("click", function (): void {
    enrollmentFile.value = "";
    enrollmentFileName.textContent = "No file selected";
    enrollmentDeleteBtn.style.display = "none";
  });

  const enrollmentImportBtn = enrollmentBtn.parentElement ? enrollmentBtn.parentElement.querySelector(".import-btn") : null;
  if (enrollmentImportBtn) {
    enrollmentImportBtn.addEventListener("click", function (): void {
      if (!enrollmentFile.files || enrollmentFile.files.length === 0) {
        alert("Please select an enrollment file to import.");
        return;
      }
      alert("Enrollment records imported successfully!");
      enrollmentFile.value = "";
      enrollmentFileName.textContent = "No file selected";
      enrollmentDeleteBtn.style.display = "none";
    });
  }

}
