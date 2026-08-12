document.addEventListener("DOMContentLoaded", function () {
  /* =========================================
       ELEMENTS
    ========================================= */

  const searchInput = document.getElementById("recordSearch");
  const ageFilter = document.getElementById("ageFilter");
  const barangayFilter = document.getElementById("barangayFilter");
  const yearFilter = document.getElementById("yearFilter");
  const genderFilter = document.getElementById("genderFilter");
  const deceasedFilter = document.getElementById("deceasedFilter");

  const tableBody = document.getElementById("recordsTableBody");

  /*
    |--------------------------------------------------------------------------
    | Check required elements
    |--------------------------------------------------------------------------
    */

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

  /* =========================================
       GET TABLE ROWS
    ========================================= */

  const rows = Array.from(tableBody.querySelectorAll("tr[data-id]"));
  const pageSize = 100;
  let currentPage = 1;

  const paginationContainer = document.getElementById("pagination");
  const previousPageBtn = document.getElementById("previousPage");
  const nextPageBtn = document.getElementById("nextPage");
  const paginationPages = document.getElementById("paginationPages");

  /* =========================================
       FILTER FUNCTION
    ========================================= */

  function filterRecords() {
    const searchValue = searchInput.value.trim().toLowerCase();

    const selectedAge = ageFilter.value;

    const selectedBarangay = barangayFilter.value.trim().toLowerCase();

    const selectedYear = yearFilter.value;

    const selectedGender = genderFilter.value.trim().toLowerCase();

    const showDeceased = deceasedFilter.checked;

    let visibleRecords = 0;

    rows.forEach(function (row) {
      /*
            |--------------------------------------------------------------------------
            | Get record information
            |--------------------------------------------------------------------------
            */

      const id = (row.dataset.id || "").toLowerCase();

      const name = (row.dataset.name || "").toLowerCase();

      const age = row.dataset.age || "";

      const barangay = (row.dataset.barangay || "").toLowerCase();

      const year = row.dataset.year || "";

      const gender = (row.dataset.gender || "").toLowerCase();

      const status = (row.dataset.status || "").toLowerCase();

      /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

      const matchesSearch =
        searchValue === "" ||
        id.includes(searchValue) ||
        name.includes(searchValue);

      /*
            |--------------------------------------------------------------------------
            | AGE
            |--------------------------------------------------------------------------
            */

      const matchesAge = selectedAge === "" || age === selectedAge;

      /*
            |--------------------------------------------------------------------------
            | BARANGAY
            |--------------------------------------------------------------------------
            */

      const matchesBarangay =
        selectedBarangay === "" || barangay === selectedBarangay;

      /*
            |--------------------------------------------------------------------------
            | YEAR
            |--------------------------------------------------------------------------
            */

      const matchesYear = selectedYear === "" || year === selectedYear;

      /*
            |--------------------------------------------------------------------------
            | GENDER
            |--------------------------------------------------------------------------
            */

      const matchesGender = selectedGender === "" || gender === selectedGender;

      /*
            |--------------------------------------------------------------------------
            | DECEASED
            |--------------------------------------------------------------------------
            */

      let matchesDeceased = true;

      if (showDeceased) {
        matchesDeceased = status === "deceased" || status === "dead";
      }

      /*
            |--------------------------------------------------------------------------
            | FINAL FILTER
            |--------------------------------------------------------------------------
            */

      const shouldShow =
        matchesSearch &&
        matchesAge &&
        matchesBarangay &&
        matchesYear &&
        matchesGender &&
        matchesDeceased;

      /*
            |--------------------------------------------------------------------------
            | STORE FILTER STATE
            |--------------------------------------------------------------------------
            */

      row.dataset.visible = shouldShow ? "true" : "false";
      row.style.display = "none";

      if (shouldShow) {
        visibleRecords++;
      }
    });

    currentPage = 1;
    updateRecordCount(visibleRecords);
    showNoResultsMessage(visibleRecords);
    renderPage();
  }

  function renderPage() {
    const visibleRows = rows.filter((row) => row.dataset.visible === "true");
    const totalVisible = visibleRows.length;
    const totalPages = Math.max(1, Math.ceil(totalVisible / pageSize));

    if (currentPage > totalPages) {
      currentPage = totalPages;
    }

    visibleRows.forEach((row, index) => {
      row.style.display =
        index >= (currentPage - 1) * pageSize && index < currentPage * pageSize
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

    const totalVisible = rows.filter(
      (row) => row.dataset.visible === "true",
    ).length;
    const totalPages = Math.max(1, Math.ceil(totalVisible / pageSize));

    if (currentPage < totalPages) {
      currentPage++;
      renderPage();
    }
  });

  /* =========================================
       UPDATE RECORD COUNT
    ========================================= */

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

  /* =========================================
       NO RESULTS MESSAGE
    ========================================= */

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
    } else {
      if (noResultsRow) {
        noResultsRow.remove();
      }
    }
  }

  /* =========================================
       EVENT LISTENERS
    ========================================= */

  searchInput.addEventListener("input", filterRecords);

  ageFilter.addEventListener("change", filterRecords);

  barangayFilter.addEventListener("change", filterRecords);

  yearFilter.addEventListener("change", filterRecords);

  genderFilter.addEventListener("change", filterRecords);

  deceasedFilter.addEventListener("change", filterRecords);

  /* =========================================
       INITIAL FILTER
    ========================================= */

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

        // Find and remove the deleted row
        const row = document.querySelector(
          'tr[data-id="' + CSS.escape(seniorId) + '"]',
        );

        if (row) {
          row.remove();
        }

        // Update the record count
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
