<?php

include '../../config/db.php';


/* =========================================
   DELETE SENIOR RECORD
========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_senior'])) {

    $seniorId = $_POST['senior_id'] ?? '';

    if (empty($seniorId)) {
        echo json_encode([
            'success' => false,
            'message' => 'Senior ID is missing.'
        ]);
        exit;
    }

    try {

        $stmt = $pdo->prepare("
            DELETE FROM senior_citizens
            WHERE senior_id = ?
        ");

        $stmt->execute([$seniorId]);

        if ($stmt->rowCount() > 0) {

            echo json_encode([
                'success' => true,
                'message' => 'Senior citizen record deleted successfully.'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Senior citizen record not found.'
            ]);
        }

    } catch (PDOException $e) {

        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete record.'
        ]);
    }

    exit;
}


/* =========================================
   FETCH RECENT SENIOR RECORDS
========================================= */

$stmt = $pdo->prepare("
    SELECT
        senior_id,
        rrn,
        first_name,
        middle_name,
        last_name,
        birth_date,
        sex,
        barangay,
        status
    FROM senior_citizens
    ORDER BY created_at DESC
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
    <title>Senior Records</title>
</head>

<body>
    <?php include '../../assets/sidebar/sidebar.php'; ?>
    <link rel="stylesheet" href="../../pages/list/seniorList.css">
    <main class="main-content">
        <?php include '../../assets/navbar/navbar.php'; ?>

        <section class="content-card">

            <div class="content-card-header">

                <div>
                    <h2>
                        Senior Citizen Records
                    </h2>

                    <p>
                        Recently registered senior citizens.
                    </p>
                </div>

                <a href="addNewSenior.php" class="add-button">
                    <i class="fa-solid fa-plus"></i>
                    Add Senior Citizen
                </a>

            </div>


            <!-- =========================================
         SEARCH / FILTER SECTION
    ========================================= -->

            <div class="records-filter">

                <!-- Search -->
                <div class="filter-search">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" id="recordSearch" placeholder="Search by ID or senior citizen name...">

                </div>


                <!-- Age -->
                <div class="filter-group">

                    <label for="ageFilter">
                        Age
                    </label>

                    <select id="ageFilter" style="font-size: 18px;">

                        <option value="">All Ages</option>

                        <?php for ($i = 60; $i <= 120; $i++): ?>

                            <option value=" <?php echo $i; ?>">
                                <?php echo $i; ?> years old
                            </option>

                        <?php endfor; ?>

                    </select>

                </div>


                <div class="filter-group">

                    <label for="barangayFilter">
                        Barangay
                    </label>

                    <select id="barangayFilter" style="font-size: 18px;">

                        <option value="">
                            All Barangays
                        </option>

                        <?php

                        $barangays = [
                            "Bakir",
                            "Baretbet",
                            "Careb",
                            "Lantap",
                            "Murong",
                            "Paniki",
                            "San Geronimo",
                            "San Pedro",
                            "Sta Lucia",
                            "Villa Coloma",
                            "Villaros",
                            "Quirino",
                        ];

                        sort($barangays);

                        foreach ($barangays as $barangay):
                            ?>

                            <option value="<?php echo htmlspecialchars($barangay); ?>">
                                <?php echo htmlspecialchars($barangay); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="filter-group">

                    <label for="yearFilter">
                        Year of Birth
                    </label>

                    <select id="yearFilter" style="font-size: 18px;">

                        <option value="">
                            All Years
                        </option>

                        <?php

                        $currentYear = date('Y');

                        for ($year = $currentYear; $year >= 1966; $year--):

                            ?>

                            <option value="<?php echo $year; ?>">

                                <?php echo $year; ?>

                            </option>

                        <?php endfor; ?>

                    </select>

                </div>


                <!-- Gender -->
                <div class="filter-group">

                    <label for="genderFilter">
                        Gender
                    </label>

                    <select id="genderFilter" style="font-size: 18px;">

                        <option value="">
                            All Genders
                        </option>

                        <option value="Male">
                            Male
                        </option>

                        <option value="Female">
                            Female
                        </option>

                    </select>

                </div>


                <!-- Deceased -->
                <div class="filter-checkbox">

                    <label>

                        <input type="checkbox" id="deceasedFilter">

                        <span>
                            Deceased
                        </span>

                    </label>

                </div>

            </div>


            <!-- =========================================
         TABLE
    ========================================= -->

            <div class="table-wrapper">

                <table class="records-table">

                    <thead>

                        <tr>

                            <th>ID Number</th>

                            <th>Senior Citizen</th>

                            <th>Age</th>

                            <th>Sex</th>

                            <th>Barangay</th>

                            <th>RRN</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody id="recordsTableBody">


                        <?php if (!empty($recentRecords)): ?>

                            <?php foreach ($recentRecords as $record): ?>


                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | CREATE FULL NAME
                                |--------------------------------------------------------------------------
                                */

                                $fullName = $record['first_name'];

                                if (!empty($record['middle_name'])) {

                                    $fullName .= ' ' . $record['middle_name'];

                                }

                                $fullName .= ' ' . $record['last_name'];


                                /*
                                |--------------------------------------------------------------------------
                                | CALCULATE AGE
                                |--------------------------------------------------------------------------
                                */

                                $age = calculateAge($record['birth_date']);


                                /*
                                |--------------------------------------------------------------------------
                                | STATUS MALE/FEMALE CLASS
                                |--------------------------------------------------------------------------
                                */

                                if ($record['sex'] === 'Male') {

                                    $genderClass = 'gender-male';

                                } else {

                                    $genderClass = 'gender-female';

                                }


                                ?>


                                <tr data-id="<?php echo htmlspecialchars($record['senior_id']); ?>"
                                    data-name="<?php echo htmlspecialchars($fullName); ?>" data-age="<?php echo $age; ?>"
                                    data-gender="<?php echo htmlspecialchars($record['sex']); ?>"
                                    data-barangay="<?php echo htmlspecialchars($record['barangay']); ?>"
                                    data-year="<?php echo date('Y', strtotime($record['birth_date'])); ?>"
                                    data-rrn="<?php echo htmlspecialchars($record['rrn']); ?>"
                                    data-is-deceased="<?php echo htmlspecialchars($record['is_deceased'] ?? 'No'); ?>">

                                    <td style="text-transform: uppercase;">
                                        <?php echo htmlspecialchars($record['senior_id']); ?>
                                    </td>

                                    <td class="senior-name" style="text-transform: uppercase;">
                                        <?php echo htmlspecialchars($fullName); ?>
                                    </td>

                                    <td>
                                        <?php echo $age; ?>
                                    </td>
                                    <?php
                                    $genderClass =
                                        $record['sex'] === 'Male'
                                        ? 'gender-male'
                                        : 'gender-female';

                                    $genderIcon =
                                        $record['sex'] === 'Male'
                                        ? 'fa-mars'
                                        : 'fa-venus';
                                    ?>

                                    <td>
                                        <span class="gender-badge <?php echo $genderClass; ?>" style="
                                                display: inline-flex;
                                                align-items: center;
                                                gap: 6px;
                                                padding: 6px 12px;
                                                border-radius: 20px;
                                                font-size: 14px;
                                                font-weight: 600;
                                                <?php echo $record['sex'] === 'Male'
                                                    ? 'color: #2563eb; background-color: #dbeafe;'
                                                    : 'color: #db2777; background-color: #fce7f3;'; ?>
                                            ">
                                            <i class="fa-solid <?php echo $genderIcon; ?>"></i>
                                            <?php echo htmlspecialchars($record['sex']); ?>
                                        </span>
                                    </td>
                                    <td style="text-transform: uppercase; font-weight: 600;">
                                        <?php echo htmlspecialchars($record['barangay']); ?>
                                    </td>


                                    <td style="text-transform: uppercase; font-weight: 600;">
                                        <?php echo htmlspecialchars($record['rrn']); ?>
                                    </td>

                                    <td>

                                        <a href="../../pages/list/viewRecord.php?id=<?php echo urlencode($record['senior_id']); ?>"
                                            class="action-button" title="View Record">

                                            <i class="fa-solid fa-eye"></i>

                                        </a>

                                        <a href="#" class="action-button delete-action" title="Delete Record"
                                            onclick="deleteSenior('<?php echo htmlspecialchars($record['senior_id'], ENT_QUOTES); ?>'); return false;">

                                            <i class="fa-solid fa-trash"></i>

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


            <!-- =========================================
         TABLE FOOTER
    ========================================= -->

            <div class="table-footer">

                <span class="record-count">

                    Showing
                    <?php echo count($recentRecords); ?>
                    recent records

                </span>


                <div class="pagination" id="pagination">

                    <a href="#" class="pagination-btn" id="previousPage">
                        <i class="fa-solid fa-chevron-left"></i>
                        Previous
                    </a>

                    <div id="paginationPages"></div>

                    <a href="#" class="pagination-btn" id="nextPage">
                        Next
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>

                </div>

            </div>

        </section>

        <script src="../../pages/list/seniorList.js"></script>
    </main>
</body>

</html>