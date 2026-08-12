<?php
// sidebar.php

if (!isset($baseUrl)) {
    // Allow overriding the base URL via the BASE_URL environment variable (Vercel).
    $baseUrl = getenv('BASE_URL') ?: '';
}
?>
<link rel="stylesheet" href="../../assets/sidebar/sidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<aside class="sidebar">

    <div class="sidebar-header">
        <img src="../../pages/login/mfscap logo.png" alt="Organization Logo">

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