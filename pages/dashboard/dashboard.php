<?php
// =========================================
// DASHBOARD.PHP
// =========================================

if (!isset($baseUrl)) {
    // Allow overriding the base URL via the BASE_URL environment variable (Vercel).
    $baseUrl = getenv('BASE_URL') ?: '';
}

include '../../config/db.php';


/*
|--------------------------------------------------------------------------
| FETCH DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$totalSeniors = 0;
$activeSeniors = 0;
$inactiveSeniors = 0;

$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM senior_citizens
");

$totalSeniors = (int) $stmt->fetchColumn();


$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM senior_citizens
    WHERE status = 'Active'
");

$activeSeniors = (int) $stmt->fetchColumn();


$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM senior_citizens
    WHERE status = 'Inactive'
");

$inactiveSeniors = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| FETCH SENIOR APPLICATIONS PER MONTH
|--------------------------------------------------------------------------
| Uses created_at to determine when each senior record was registered.
| Shows the last 12 months.
|--------------------------------------------------------------------------
*/

$monthlyApplications = [];

$stmt = $pdo->query("
    SELECT
        TO_CHAR(created_at, 'YYYY-MM') AS month,
        COUNT(*) AS total
    FROM senior_citizens
    WHERE created_at >= CURRENT_DATE - INTERVAL '11 months'
    GROUP BY TO_CHAR(created_at, 'YYYY-MM')
    ORDER BY month ASC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $monthlyApplications[$row['month']] = (int) $row['total'];
}


/*
|--------------------------------------------------------------------------
| GENERATE LAST 12 MONTHS
|--------------------------------------------------------------------------
*/

$chartLabels = [];
$chartData = [];

for ($i = 11; $i >= 0; $i--) {

    $date = new DateTime();
    $date->modify("-$i months");

    $monthKey = $date->format('Y-m');

    $monthLabel = $date->format('M Y');

    $chartLabels[] = $monthLabel;

    $chartData[] = $monthlyApplications[$monthKey] ?? 0;
}


/*
|--------------------------------------------------------------------------
| FETCH RECENT SENIOR RECORDS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        senior_id,
        first_name,
        middle_name,
        last_name,
        birth_date,
        sex,
        barangay,
        status
    FROM senior_citizens
    ORDER BY created_at DESC
    LIMIT 4
");

$stmt->execute();

$recentRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| CALCULATE AGE
|--------------------------------------------------------------------------
*/

function calculateAge($birthDate)
{
    $birthDate = new DateTime($birthDate);
    $today = new DateTime();

    return $today->diff($birthDate)->y;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BASCA-RMS Dashboard</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="../../pages/dashboard/dashboard.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

</head>

<body>


    <!-- =========================================
         SIDEBAR
    ========================================= -->

    <?php include '../../assets/sidebar/sidebar.php'; ?>


    <main class="main-content">

        <?php include '../../assets/navbar/navbar.php'; ?>


        <!-- =========================================
             WELCOME BANNER
        ========================================= -->

        <section class="welcome-card">

            <div class="welcome-text">

                <h2>
                    MUNICIPALITY OF BAGABAG, NUEVA VIZCAYA
                </h2>

                <p>
                    Manage and monitor senior citizen records efficiently.
                </p>

            </div>

            <div class="welcome-icon">

                <i class="fa-solid fa-users"></i>

            </div>

        </section>


        <!-- =========================================
             STATISTICS
        ========================================= -->

        <section class="stats-grid">

            <!-- Total Seniors -->

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="stat-info">

                    <span style="font-size: 20px;">
                        Total Senior Citizens
                    </span>

                    <strong>
                        <?php echo $totalSeniors ?? 0; ?>
                    </strong>

                </div>

            </div>


            <!-- Active Seniors -->

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="fa-solid fa-user-check"></i>
                </div>

                <div class="stat-info">

                    <span style="font-size: 20px;">
                        Active Seniors
                    </span>

                    <strong>
                        <?php echo $activeSeniors ?? 0; ?>
                    </strong>

                </div>

            </div>


            <!-- Inactive Seniors -->

            <div class="stat-card">

                <div class="stat-icon">
                    <i class="fa-solid fa-user-slash"></i>
                </div>

                <div class="stat-info">

                    <span style="font-size: 20px;">
                        Inactive Seniors
                    </span>

                    <strong>
                        <?php echo $inactiveSeniors ?? 0; ?>
                    </strong>

                </div>

            </div>

        </section>


        <!-- =========================================
             SENIOR CITIZEN APPLICATIONS GRAPH
        ========================================= -->

        <!-- <section class="content-card monthly-chart-card">

            <div class="content-card-header">

                <div>

                    <h2 style="font-size: 25px;">
                        Senior Citizen Applications
                    </h2>

                    <p>
                        Number of senior citizens registered per month.
                    </p>

                </div>

            </div>


            <div class="monthly-chart-container">

                <canvas id="seniorApplicationsChart"></canvas>

            </div>

        </section> -->


        <!-- =========================================
             RECENT SENIOR RECORDS
        ========================================= -->

        <section class="content-card">

            <div class="content-card-header">

                <div>

                    <h2 style="font-size: 25px;">
                        Recent Senior Records
                    </h2>

                    <p>
                        Recently registered senior citizens.
                    </p>

                </div>

            </div>


            <div class="table-wrapper">

                <table class="records-table">

                    <thead>

                        <tr>

                            <th>ID Number</th>

                            <th>Senior Citizen</th>

                            <th>Age</th>

                            <th>Sex</th>

                            <th>Barangay</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (!empty($recentRecords)): ?>

                            <?php foreach ($recentRecords as $record): ?>

                                <?php

                                $fullName = $record['first_name'];

                                if (!empty($record['middle_name'])) {
                                    $fullName .= ' ' . $record['middle_name'];
                                }

                                $fullName .= ' ' . $record['last_name'];

                                $age = calculateAge($record['birth_date']);

                                $statusClass =
                                    $record['status'] === 'Active'
                                    ? 'status-active'
                                    : 'status-inactive';

                                ?>

                                <tr>

                                    <td>
                                        <?php echo htmlspecialchars($record['senior_id']); ?>
                                    </td>

                                    <td class="senior-name" style="text-transform: uppercase;">

                                        <?php echo htmlspecialchars($fullName); ?>

                                    </td>

                                    <td>
                                        <?php echo $age; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($record['sex']); ?>
                                    </td>

                                    <td style="text-transform: uppercase; font-weight: 600;">

                                        <?php echo htmlspecialchars($record['barangay']); ?>

                                    </td>

                                    <td>

                                        <span class="status <?php echo $statusClass; ?>">

                                            <?php echo htmlspecialchars($record['status']); ?>

                                        </span>

                                    </td>

                                    <td>

                                        <a href="../../pages/list/viewRecord.php?id=<?php echo urlencode($record['senior_id']); ?>"
                                            class="action-button" title="View Record">

                                            <i class="fa-solid fa-eye"></i>

                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="7" style="text-align: center;">

                                    No senior records found.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <div class="table-footer">

                <span class="record-count">

                    Showing
                    <?php echo count($recentRecords ?? []); ?>
                    recent records

                </span>


                <a href="<?php echo $baseUrl; ?>/pages/list/seniorList.php" class="view-all">

                    View All Records

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

            </div>

        </section>

    </main>


    <!-- =========================================
         MOBILE SIDEBAR SCRIPT
    ========================================= -->

    <script>

        const sidebarToggle =
            document.getElementById('sidebarToggle');

        const sidebar =
            document.querySelector('.sidebar');


        if (sidebarToggle && sidebar) {

            sidebarToggle.addEventListener('click', function () {

                sidebar.classList.toggle('show');

            });

        }

    </script>


    <!-- =========================================
         SENIOR APPLICATIONS LINE GRAPH
    ========================================= -->

    <script>

        document.addEventListener("DOMContentLoaded", function () {

            const canvas =
                document.getElementById('seniorApplicationsChart');

            if (!canvas) {
                return;
            }


            const labels =
                <?php echo json_encode($chartLabels); ?>;

            const applicationData =
                <?php echo json_encode($chartData); ?>;


            new Chart(canvas, {

                type: 'line',

                data: {

                    labels: labels,

                    datasets: [

                        {

                            label: 'Senior Applications',

                            data: applicationData,

                            borderColor: '#3272c7',

                            backgroundColor: 'rgba(50, 114, 199, 0.12)',

                            borderWidth: 3,

                            pointRadius: 5,

                            pointHoverRadius: 7,

                            pointBorderWidth: 2,

                            tension: 0.4,

                            fill: true

                        }

                    ]

                },


                options: {

                    responsive: true,

                    maintainAspectRatio: false,


                    interaction: {

                        intersect: false,

                        mode: 'index'

                    },


                    plugins: {

                        legend: {

                            display: true,

                            position: 'top',

                            labels: {

                                font: {

                                    family: 'Times New Roman',

                                    size: 16

                                }

                            }

                        },


                        tooltip: {

                            callbacks: {

                                label: function (context) {

                                    return ' Applications: ' + context.parsed.y;

                                }

                            },

                            titleFont: {

                                family: 'Times New Roman',

                                size: 15

                            },

                            bodyFont: {

                                family: 'Times New Roman',

                                size: 15

                            }

                        }

                    },


                    scales: {

                        x: {

                            grid: {

                                display: false

                            },

                            ticks: {

                                font: {

                                    family: 'Times New Roman',

                                    size: 16

                                }

                            }

                        },


                        y: {

                            beginAtZero: true,

                            ticks: {

                                precision: 0,

                                stepSize: 1,

                                font: {

                                    family: 'Times New Roman',

                                    size: 16

                                }

                            },

                            title: {

                                display: true,

                                text: 'MONTHLY',

                                font: {

                                    family: 'Times New Roman',

                                    size: 16

                                }

                            }

                        }

                    }

                }

            });

        });

    </script>


</body>

</html>