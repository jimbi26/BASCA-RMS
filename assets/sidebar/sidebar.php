<?php
// sidebar.php

if (!isset($baseUrl)) {
    // Allow overriding the base URL via the BASE_URL environment variable (Vercel).
    $baseUrl = getenv('BASE_URL') ?: '';
}
?>
<link rel="stylesheet" href="../../assets/sidebar/sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<!-- Page Loading Overlay -->
<div id="pageLoader" class="page-loader">
    <div class="loader-content">
        <div class="loading-spinner"></div>
        <p>Loading...</p>
        <span>Please wait</span>
    </div>
</div>
<aside class="sidebar">

    <div class="sidebar-header">
        <img src="../../pages/login/LOGO.jpg" alt="Organization Logo">

        <div>
            <h2>BASCA-RMS</h2>
            <p>Record Management System</p>
        </div>
    </div>

    <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>

    <nav class="sidebar-nav">

        <!-- Dashboard -->
        <a href="<?php echo $baseUrl; ?>/pages/dashboard/dashboard.php"
            class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-gauge"></i>&nbsp;&nbsp;
            <span>Dashboard</span>
        </a>

        <!-- Reports -->
        <!--
    <a href="<?php echo $baseUrl; ?>/admin/reports.php">
        <i class="fa-solid fa-chart-column"></i>&nbsp;&nbsp;
        <span>Reports</span>
    </a>
    -->

        <a href="<?php echo $baseUrl; ?>/pages/list/seniorList.php" class="<?php echo (
               strtolower($currentPage) === 'seniorlist.php' ||
               strtolower($currentPage) === 'viewrecord.php' ||
               strtolower($currentPage) === 'addnewsenior.php'
           ) ? 'active' : ''; ?>">

            <i class="fa-solid fa-users"></i>&nbsp;&nbsp;
            <span>Senior List</span>

        </a>

        <!-- Logout -->
        <a href="<?php echo $baseUrl; ?>/assets/sidebar/logout.php" class="logout-link">
            <i class="fa-solid fa-right-from-bracket"></i>&nbsp;&nbsp;
            <span>Logout</span>
        </a>

    </nav>

</aside>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const pageLoader = document.getElementById("pageLoader");

        if (!pageLoader) return;

        const navLinks = document.querySelectorAll(".sidebar-nav a");

        navLinks.forEach(function (link) {

            link.addEventListener("click", function (event) {

                // Don't show loader for links opening in a new tab
                if (link.target === "_blank") {
                    return;
                }

                // Don't show loader for javascript links
                if (link.getAttribute("href") === "#" ||
                    link.getAttribute("href") === "") {
                    return;
                }

                // Show loading screen
                pageLoader.classList.add("show");

            });

        });

        // Hide loader when page is loaded
        window.addEventListener("pageshow", function () {
            pageLoader.classList.remove("show");
        });

    });
</script>