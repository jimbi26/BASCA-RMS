<!-- =====================================
             DASHBOARD HEADER
        ====================================== -->
<link rel="stylesheet" href="../../assets/navbar/navbar.css">
<header class="dashboard-header">

    <div class="dashboard-header-left">

        <button type="button" class="mobile-menu-button" id="sidebarToggle" aria-label="Open navigation menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="page-heading">
            <h1 id="pageTitle">Dashboard</h1>
            <p id="pageSubtitle">Senior Citizens Records Management</p>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {

                const page = window.location.pathname.toLowerCase();

                const pageInfo = {
                    "dashboard.php": {
                        title: "Dashboard",
                        subtitle: "Senior Citizens Records Management"
                    },

                    "seniorlist.php": {
                        title: "Senior Citizens Records",
                        subtitle: "View and manage senior citizen records"
                    },
                    "viewrecord.php": {
                        title: "Senior Citizens Records",
                        subtitle: "View and manage senior citizen records"
                    },
                    "addnewsenior.php": {
                        title: "Add New Senior Citizen",
                        subtitle: "Register a new senior citizen"
                    }
                };

                const currentPage = page.substring(page.lastIndexOf("/") + 1);

                if (pageInfo[currentPage]) {

                    const pageTitle = document.getElementById("pageTitle");
                    const pageSubtitle = document.getElementById("pageSubtitle");

                    if (pageTitle) {
                        pageTitle.textContent = pageInfo[currentPage].title;
                    }

                    if (pageSubtitle) {
                        pageSubtitle.textContent = pageInfo[currentPage].subtitle;
                    }
                }

            });
        </script>
    </div>


    <div class="admin-profile">

        <div class="admin-avatar">
            <i class="fa-solid fa-user"></i>
        </div>

        <div class="admin-info">
            <strong>Administrator</strong>
            <span>System Administrator</span>
        </div>

    </div>

</header>