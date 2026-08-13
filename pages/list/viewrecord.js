document.addEventListener("DOMContentLoaded", function () {
  const buttons = document.querySelectorAll(".id-switch-option");

  const title = document.getElementById("idTitle");
  const subtitle = document.getElementById("idSubtitle");
  const badge = document.getElementById("idBadge");
  const image = document.getElementById("idImage");

  const documents = {
    psa: {
      title: "PSA Identification",
      subtitle: "Philippine Statistics Authority identification document",
      badge: "PSA",
      imageAttribute: "data-psa",
    },

    ncsc: {
      title: "NCSC Identification",
      subtitle:
        "National Commission of Senior Citizens identification document",
      badge: "NCSC",
      imageAttribute: "data-ncsc",
    },

    back: {
      title: "B2B Senior ID",
      subtitle: "Front and back identification document",
      badge: "Senior Citizen ID",
      imageAttribute: "data-back",
    },
  };

  buttons.forEach(function (button) {
    button.addEventListener("click", function () {
      const selectedDocument = this.dataset.id;

      // Remove active state
      buttons.forEach(function (btn) {
        btn.classList.remove("active");
      });

      // Add active state
      this.classList.add("active");

      // Check if document exists
      if (!documents[selectedDocument]) {
        return;
      }

      const documentInfo = documents[selectedDocument];

      // Update title
      if (title) {
        title.textContent = documentInfo.title;
      }

      // Update subtitle
      if (subtitle) {
        subtitle.textContent = documentInfo.subtitle;
      }

      // Update badge
      if (badge) {
        badge.textContent = documentInfo.badge;
      }

      // Update image
      if (image) {
        const imageUrl = image.getAttribute(documentInfo.imageAttribute);

        // Only change image if a URL exists
        if (imageUrl) {
          image.src = imageUrl;

          image.alt = documentInfo.title;
        }
      }
    });
  });
});
// Print Image Function
function printIDImage() {
  const image = document.getElementById("idImage");

  if (!image || !image.src) {
    alert("No identification image available to print.");
    return;
  }

  const printWindow = window.open("", "_blank", "width=900,height=900");

  printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>

            <meta charset="UTF-8">

            <title>Print Identification Document</title>

            <style>

                @page {
                    size: A4 portrait;
                    margin: 0 !important;
                }

                html {
                    width: 210mm !important;
                    height: 297mm !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                body {
                    width: 210mm !important;
                    height: 297mm !important;

                    margin: 0 !important;
                    padding: 0 !important;

                    overflow: hidden !important;

                    background: white;
                }

                .print-page {
                    width: 210mm !important;
                    height: 297mm !important;

                    margin: 0 !important;
                    padding: 0 !important;

                    overflow: hidden !important;
                }

                .print-page img {
                    display: block !important;

                    width: 210mm !important;
                    height: 297mm !important;

                    min-width: 210mm !important;
                    min-height: 297mm !important;

                    max-width: none !important;
                    max-height: none !important;

                    margin: 0 !important;
                    padding: 0 !important;

                    object-fit: fill !important;
                }

                @media print {

                    html,
                    body {
                        width: 210mm !important;
                        height: 297mm !important;

                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    .print-page {
                        width: 210mm !important;
                        height: 297mm !important;

                        margin: 0 !important;
                        padding: 0 !important;
                    }

                    .print-page img {
                        width: 210mm !important;
                        height: 297mm !important;

                        min-width: 210mm !important;
                        min-height: 297mm !important;

                        max-width: none !important;
                        max-height: none !important;

                        object-fit: fill !important;
                    }

                }

            </style>

        </head>

        <body>

            <div class="print-page">

                <img
                    id="printImage"
                    src="${image.src}"
                    alt="Identification Document"
                >

            </div>

            <script>

                const printImage = document.getElementById("printImage");

                printImage.onload = function () {

                    setTimeout(function () {

                        window.focus();
                        window.print();

                    }, 300);

                };

            <\/script>

        </body>
        </html>
    `);

  printWindow.document.close();
}

// Open MOdal
function openRRNModal(event) {
  event.preventDefault();

  const modal = document.getElementById("rrnModal");
  const input = document.getElementById("rrn");

  if (!modal) {
    console.error("RRN modal not found.");
    return;
  }

  modal.classList.add("active");

  if (input) {
    setTimeout(() => {
      input.focus();
    }, 100);
  }
}

function closeRRNModal() {
  const modal = document.getElementById("rrnModal");

  if (modal) {
    modal.classList.remove("active");
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("rrnModal");

  if (!modal) {
    return;
  }

  modal.addEventListener("click", function (event) {
    if (event.target === modal) {
      closeRRNModal();
    }
  });
});

document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    closeRRNModal();
  }
});

function getRecordSeniorId() {
  const metadata = document.getElementById("recordMetadata");
  if (metadata && metadata.dataset.seniorId) {
    return metadata.dataset.seniorId;
  }
  const params = new URLSearchParams(window.location.search);
  return params.get("id") || "";
}

function deleteSenior() {
  const seniorId = getRecordSeniorId();

  const confirmed = confirm(
    "Are you sure you want to delete this senior citizen record?\n\n" +
      "Senior ID: " +
      seniorId +
      "\n\n" +
      "This action cannot be undone.",
  );

  if (!confirmed) {
    return;
  }

  // Create POST form without changing the button's position
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "";

  const seniorIdInput = document.createElement("input");
  seniorIdInput.type = "hidden";
  seniorIdInput.name = "senior_id";
  seniorIdInput.value = seniorId;

  const deleteInput = document.createElement("input");
  deleteInput.type = "hidden";
  deleteInput.name = "delete_record";
  deleteInput.value = "1";

  form.appendChild(seniorIdInput);
  form.appendChild(deleteInput);

  document.body.appendChild(form);
  form.submit();
}

/* =========================================
   EDIT MODE: enable inline editing and save
========================================= */

let __isEditing = false;

function toggleEditMode() {
  if (!__isEditing) {
    enableEditing();
  } else {
    showPageLoader(
      "Saving Changes...",
      "Please wait while we update the record.",
    );

    // Force browser to render the loading overlay first
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        saveEdits();
      });
    });
  }
}

function showPageLoader(title, message) {
  const loader = document.getElementById("pageLoader");

  if (!loader) {
    console.error("Page loader not found.");
    return;
  }

  const loaderTitle = document.getElementById("loaderTitle");
  const loaderMessage = document.getElementById("loaderMessage");

  if (loaderTitle) {
    loaderTitle.textContent = title || "Saving Changes...";
  }

  if (loaderMessage) {
    loaderMessage.textContent = message || "Please wait...";
  }

  loader.classList.add("show");

  // Force browser to recognize the new visual state
  loader.offsetHeight;
}
function enableEditing() {
  __isEditing = true;

  const editBtn = document.getElementById("editButton");
  if (editBtn) {
    editBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> SAVE';
  }

  document.body.classList.add("editing-active");

  // Add cancel button
  const rightActions = document.querySelector(".record-view-right-actions");
  if (rightActions && !document.getElementById("cancelEditButton")) {
    const cancelBtn = document.createElement("button");
    cancelBtn.type = "button";
    cancelBtn.id = "cancelEditButton";
    cancelBtn.className = "delete-button";
    cancelBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> CANCEL';
    cancelBtn.onclick = cancelEditing;
    rightActions.insertBefore(
      cancelBtn,
      rightActions.querySelector(".delete-button"),
    );
  }

  // Turn editable spans into inputs/selects
  const editableEls = document.querySelectorAll('[data-editable="true"]');

  editableEls.forEach(function (el) {
    const field = el.dataset.field;
    const original = el.textContent.trim();

    // Create input element based on field
    let input;

    if (field === "is_deceased") {
      input = document.createElement("select");
      input.innerHTML =
        '<option value="0">No</option><option value="1">Yes</option>';
      input.value = original.toLowerCase().startsWith("y") ? "1" : "0";
    } else if (field === "birth_date") {
      input = document.createElement("input");
      input.type = "date";
      let parsedDate = new Date(
        original.replace(/([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})/, "$1 $2, $3"),
      );
      if (!isNaN(parsedDate.getTime())) {
        input.value = parsedDate.toISOString().slice(0, 10);
      } else {
        input.value = "";
      }
    } else if (field === "age") {
      input = document.createElement("input");
      input.type = "number";
      input.min = "0";
      input.step = "1";
      const parsedAge = parseInt(original, 10);
      input.value = !isNaN(parsedAge) ? parsedAge : "";
    } else {
      input = document.createElement("input");
      input.type = "text";
      input.value = original === "Not provided" ? "" : original;
    }

    input.dataset.field = field;
    input.className = "inline-edit-input";
    // store original html for cancel
    input.dataset.originalHtml = el.outerHTML;

    if (["first_name", "middle_name", "last_name"].includes(field)) {
      const fullNameDisplay = document.getElementById("fullNameDisplay");
      if (fullNameDisplay) {
        fullNameDisplay.style.display = "none";
      }
    }

    // Replace element with input
    el.parentNode.replaceChild(input, el);
  });
}

function cancelEditing() {
  // simply reload page to discard changes
  location.reload();
}

function saveEdits() {
  // collect inputs
  const inputs = document.querySelectorAll(".inline-edit-input");
  if (!inputs || inputs.length === 0) {
    // nothing to save
    __isEditing = false;
    return;
  }

  // Build form and submit
  const form = document.createElement("form");
  form.method = "POST";
  form.action =
    window.location.pathname + "?id=" + encodeURIComponent(getRecordSeniorId());

  const updateInput = document.createElement("input");
  updateInput.type = "hidden";
  updateInput.name = "update_record";
  updateInput.value = "1";
  form.appendChild(updateInput);

  const existingSeniorIdInput = document.createElement("input");
  existingSeniorIdInput.type = "hidden";
  existingSeniorIdInput.name = "existing_senior_id";
  existingSeniorIdInput.value = getRecordSeniorId();
  form.appendChild(existingSeniorIdInput);

  inputs.forEach(function (input) {
    const name = input.dataset.field;
    const value = input.value;

    const h = document.createElement("input");
    h.type = "hidden";
    h.name = name;
    h.value = value;
    form.appendChild(h);
  });

  document.body.appendChild(form);
  form.submit();
}
