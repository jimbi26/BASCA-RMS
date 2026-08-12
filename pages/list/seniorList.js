document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("recordSearch");
  const ageFilter = document.getElementById("ageFilter");
  const barangayFilter = document.getElementById("barangayFilter");
  const yearFilter = document.getElementById("yearFilter");
  const genderFilter = document.getElementById("genderFilter");
  const deceasedFilter = document.getElementById("deceasedFilter");
  const tableBody = document.getElementById("recordsTableBody");

  if (
    !searchInput ||
    !ageFilter ||
    !barangayFilter ||
    !yearFilter ||
    !genderFilter ||
    !deceasedFilter ||
    !tableBody
  ) {
    console.error("Records filter: Required element not found.");
    return;
  }

  const rows = Array.from(tableBody.querySelectorAll("tr[data-id]"));
  const records = rows.map((row) => ({
    row,
    id: (row.dataset.id || "").toLowerCase(),
    name: (row.dataset.name || "").toLowerCase(),
    age: row.dataset.age || "",
    barangay: (row.dataset.barangay || "").toLowerCase(),
    year: row.dataset.year || "",
    gender: (row.dataset.gender || "").toLowerCase(),
    status: (row.dataset.status || "").toLowerCase(),
    isDeceased: (row.dataset.isDeceased || "").toLowerCase(),
    rrn: (row.dataset.rrn || "").toLowerCase(),
    visible: true,
  }));

  const pageSize = 100;
  let currentPage = 1;

  const paginationContainer = document.getElementById("pagination");
  const previousPageBtn = document.getElementById("previousPage");
  const nextPageBtn = document.getElementById("nextPage");
  const paginationPages = document.getElementById("paginationPages");

  function filterRecords() {
    const searchValue = searchInput.value.trim().toLowerCase();
    const selectedAge = ageFilter.value;
    const selectedBarangay = barangayFilter.value.trim().toLowerCase();
    const selectedYear = yearFilter.value;
    const selectedGender = genderFilter.value.trim().toLowerCase();
    const showDeceased = deceasedFilter.checked;

    let visibleRecords = 0;

    records.forEach((record) => {
      const matchesSearch =
        searchValue === "" ||
        record.id.includes(searchValue) ||
        record.name.includes(searchValue) ||
        record.rrn.includes(searchValue);

      const matchesAge = selectedAge === "" || record.age === selectedAge;
      const matchesBarangay =
        selectedBarangay === "" || record.barangay === selectedBarangay;
      const matchesYear = selectedYear === "" || record.year === selectedYear;
      const matchesGender =
        selectedGender === "" || record.gender === selectedGender;

      let matchesDeceased = true;
      if (showDeceased) {
        matchesDeceased =
          record.isDeceased === "1" ||
          record.isDeceased === "yes" ||
          record.isDeceased === "true";
      }

      record.visible =
        matchesSearch &&
        matchesAge &&
        matchesBarangay &&
        matchesYear &&
        matchesGender &&
        matchesDeceased;

      record.row.style.display = "none";

      if (record.visible) {
        visibleRecords++;
      }
    });

    currentPage = 1;
    updateRecordCount(visibleRecords);
    showNoResultsMessage(visibleRecords);
    renderPage();
  }

  function renderPage() {
    const visibleRecords = records.filter((record) => record.visible);
    const totalVisible = visibleRecords.length;
    const totalPages = Math.max(1, Math.ceil(totalVisible / pageSize));

    if (currentPage > totalPages) {
      currentPage = totalPages;
    }

    visibleRecords.forEach((record, index) => {
      record.row.style.display =
        index >= (currentPage - 1) * pageSize &&
        index < currentPage * pageSize
          ? ""
          : "none";
    });

    updatePagination(totalVisible, totalPages);
  }

  function updatePagination(totalVisible, totalPages) {
    if (totalVisible <= pageSize) {
      paginationContainer.style.display = "none";
      return;
    }

    paginationContainer.style.display = "flex";
    paginationPages.innerHTML = "";

    for (let page = 1; page <= totalPages; page++) {
      const pageButton = document.createElement("button");
      pageButton.type = "button";
      pageButton.className = "pagination-page";
      pageButton.textContent = page;

      if (page === currentPage) {
        pageButton.classList.add("active");
      }

      pageButton.addEventListener("click", function () {
        currentPage = page;
        renderPage();
      });

      paginationPages.appendChild(pageButton);
    }

    previousPageBtn.disabled = currentPage <= 1;
    nextPageBtn.disabled = currentPage >= totalPages;
  }

  previousPageBtn.addEventListener("click", function (event) {
    event.preventDefault();

    if (currentPage > 1) {
      currentPage--;
      renderPage();
    }
  });

  nextPageBtn.addEventListener("click", function (event) {
    event.preventDefault();

    const totalVisible = records.filter((record) => record.visible).length;
    const totalPages = Math.max(1, Math.ceil(totalVisible / pageSize));

    if (currentPage < totalPages) {
      currentPage++;
      renderPage();
    }
  });

  function updateRecordCount(count) {
    const recordCount = document.querySelector(".record-count");

    if (!recordCount) {
      return;
    }

    if (count === 0) {
      recordCount.textContent = "No records found";
    } else {
      recordCount.textContent =
        "Showing " + count + " record" + (count !== 1 ? "s" : "");
    }
  }

  function showNoResultsMessage(count) {
    let noResultsRow = document.getElementById("noFilterResults");

    if (count === 0) {
      if (!noResultsRow) {
        noResultsRow = document.createElement("tr");
        noResultsRow.id = "noFilterResults";
        noResultsRow.innerHTML = `
                    <td colspan="7" class="no-results">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <br>
                        No senior records match your filters.
                    </td>
                `;
        tableBody.appendChild(noResultsRow);
      }
    } else if (noResultsRow) {
      noResultsRow.remove();
    }
  }

  searchInput.addEventListener("input", filterRecords);
  ageFilter.addEventListener("change", filterRecords);
  barangayFilter.addEventListener("change", filterRecords);
  yearFilter.addEventListener("change", filterRecords);
  genderFilter.addEventListener("change", filterRecords);
  deceasedFilter.addEventListener("change", filterRecords);

  filterRecords();
});

function deleteSenior(seniorId) {
  if (
    !confirm(
      "Are you sure you want to delete this senior citizen record?\n\n" +
        "Senior ID: " +
        seniorId +
        "\n\n" +
        "This action cannot be undone.",
    )
  ) {
    return;
  }

  fetch("seniorList.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "delete_senior=1" + "&senior_id=" + encodeURIComponent(seniorId),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message);
        const row = document.querySelector(
          'tr[data-id="' + CSS.escape(seniorId) + '"]',
        );

        if (row) {
          row.remove();
        }

        const recordCount = document.querySelector(".record-count");
        if (recordCount) {
          const remainingRows = document.querySelectorAll(
            "#recordsTableBody tr[data-id]",
          ).length;
          recordCount.textContent =
            "Showing " +
            remainingRows +
            " record" +
            (remainingRows !== 1 ? "s" : "");
        }
      } else {
        alert(data.message || "Failed to delete record.");
      }
    })
    .catch((error) => {
      console.error("Delete error:", error);
      alert("An error occurred while deleting the record.");
    });
}
