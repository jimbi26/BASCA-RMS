<?php

include '../../config/db.php';
// Base URL -- use `BASE_URL` env var on platforms like Vercel, otherwise default to root
$baseUrl = getenv('BASE_URL') ?: '';
/* =========================================
   DELETE SENIOR CITIZEN
========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_record'])) {

    $deleteSeniorId = $_POST['senior_id'] ?? null;

    if (!$deleteSeniorId) {
        die("Invalid senior citizen ID.");
    }

    try {

        $deleteStmt = $pdo->prepare("
            DELETE FROM senior_citizens
            WHERE senior_id = ?
        ");

        $deleteStmt->execute([$deleteSeniorId]);

        if ($deleteStmt->rowCount() > 0) {

            // Redirect back to senior list
            header("Location: " . $baseUrl . "/pages/list/seniorList.php?deleted=1");
            exit;

        } else {

            die("Senior citizen record not found.");

        }

    } catch (PDOException $e) {

        die("Failed to delete record: " . $e->getMessage());

    }
}

/* =========================================
   UPDATE SENIOR CITIZEN
========================================= */

/* =========================================
   UPDATE SENIOR CITIZEN
========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_record'])) {

    $existingSeniorId = $_POST['existing_senior_id'] ?? null;
    $newSeniorId = $_POST['senior_id'] ?? null;

    if (!$existingSeniorId) {
        die("Invalid senior citizen ID.");
    }

    /* =========================================
       PERSONAL INFORMATION
    ========================================= */

    $senior_id = !empty($newSeniorId)
        ? trim($newSeniorId)
        : $existingSeniorId;

    $rrn = !empty($_POST['rrn'])
        ? trim($_POST['rrn'])
        : null;

    $birth_date = !empty($_POST['birth_date'])
        ? $_POST['birth_date']
        : null;

    $first_name = !empty($_POST['first_name'])
        ? trim($_POST['first_name'])
        : null;

    $middle_name = !empty($_POST['middle_name'])
        ? trim($_POST['middle_name'])
        : null;

    $last_name = !empty($_POST['last_name'])
        ? trim($_POST['last_name'])
        : null;

    $sex = !empty($_POST['sex'])
        ? trim($_POST['sex'])
        : null;

    $purok = !empty($_POST['purok'])
        ? trim($_POST['purok'])
        : null;

    $barangay = !empty($_POST['barangay'])
        ? trim($_POST['barangay'])
        : null;

    $contact_number = !empty($_POST['contact_number'])
        ? trim($_POST['contact_number'])
        : null;

    $status = !empty($_POST['status'])
        ? trim($_POST['status'])
        : null;


    /* =========================================
       SENIOR CITIZEN INFORMATION
    ========================================= */

    $pension = !empty($_POST['pension'])
        ? trim($_POST['pension'])
        : null;

    $philhealth_number = !empty($_POST['philhealth_number'])
        ? trim($_POST['philhealth_number'])
        : null;

    $dependency = !empty($_POST['dependency'])
        ? trim($_POST['dependency'])
        : null;

    $housing = !empty($_POST['housing'])
        ? trim($_POST['housing'])
        : null;

    $health_problems = !empty($_POST['health_problems'])
        ? trim($_POST['health_problems'])
        : null;

    $medicines = !empty($_POST['medicines'])
        ? trim($_POST['medicines'])
        : null;

    $disability = !empty($_POST['disability'])
        ? trim($_POST['disability'])
        : null;


    /* =========================================
       IS DECEASED
    ========================================= */

    if (isset($_POST['is_deceased'])) {

        $rawIsDeceased = (string) $_POST['is_deceased'];

        $is_deceased = in_array(
            $rawIsDeceased,
            ['1', 'true', 'yes', 'Yes', 'TRUE', 'YES'],
            true
        )
            ? 'Yes'
            : 'No';

    } else {

        $is_deceased = null;

    }


    /* =========================================
       UPDATE DATABASE
    ========================================= */

    try {

        $updateStmt = $pdo->prepare("
            UPDATE senior_citizens
            SET
                senior_id = ?,
                rrn = ?,
                birth_date = ?,
                first_name = ?,
                middle_name = ?,
                last_name = ?,
                sex = ?,
                purok = ?,
                barangay = ?,
                contact_number = ?,
                status = ?,

                pension = ?,
                philhealth_number = ?,
                dependency = ?,
                housing = ?,
                health_problems = ?,
                medicines = ?,
                disability = ?,

                is_deceased = ?

            WHERE senior_id = ?
        ");

        $updateStmt->execute([

            // Personal Information
            $senior_id,
            $rrn,
            $birth_date,
            $first_name,
            $middle_name,
            $last_name,
            $sex,
            $purok,
            $barangay,
            $contact_number,
            $status,

            // Senior Citizen Information
            $pension,
            $philhealth_number,
            $dependency,
            $housing,
            $health_problems,
            $medicines,
            $disability,

            // Deceased
            $is_deceased,

            // WHERE
            $existingSeniorId

        ]);


        /* =========================================
           REDIRECT AFTER SUCCESS
        ========================================= */

        header(
            "Location: " .
            $baseUrl .
            "/pages/list/viewRecord.php?id=" .
            urlencode($senior_id) .
            "&updated=1"
        );

        exit;


    } catch (PDOException $e) {

        die(
            "Failed to update record: " .
            $e->getMessage()
        );

    }

}

/* =========================================
   SUPABASE STORAGE
========================================= */

$supabaseUrl = getenv('SUPABASE_URL') ?: 'https://qrsxsxbnndjajhnjwuwv.supabase.co';

$storageBucket = getenv('SUPABASE_BUCKET') ?: 'senior-documents';


/* =========================================
   GET SENIOR ID
========================================= */

$seniorId = $_GET['id'] ?? null;

if (!$seniorId) {
    die("Senior citizen record not found.");
}


/* =========================================
   GET RECORD
========================================= */

$stmt = $pdo->prepare("
    SELECT *
    FROM senior_citizens
    WHERE senior_id = ?
");

$stmt->execute([$seniorId]);

$record = $stmt->fetch();


if (!$record) {
    die("Senior citizen record not found.");
}


/* =========================================
   FULL NAME
========================================= */

$fullName = trim(
    $record['first_name'] . ' ' .
    ($record['middle_name'] ?? '') . ' ' .
    $record['last_name']
);


/* =========================================
   BIRTH DATE
========================================= */

$birthDate = !empty($record['birth_date'])
    ? date('F d, Y', strtotime($record['birth_date']))
    : 'Not provided';


/* =========================================
   AGE
========================================= */

if (!empty($record['birth_date'])) {

    $birthDateObject = new DateTime(
        $record['birth_date']
    );

    $today = new DateTime();

    $age = $today->diff(
        $birthDateObject
    )->y;

} else {

    $age = '';

}


/* =========================================
   REGISTRATION DATE
========================================= */

$registrationDate = !empty($record['created_at'])
    ? date(
        'F d, Y',
        strtotime($record['created_at'])
    )
    : 'Not provided';


/* =========================================
   STATUS CLASS
========================================= */

$statusClass = strtolower(
    str_replace(
        ' ',
        '-',
        $record['status'] ?? ''
    )
);


/* =========================================
   SUPABASE STORAGE URL
========================================= */

function storageUrl($path)
{

    global $supabaseUrl;
    global $storageBucket;


    if (empty($path)) {
        return '';
    }


    /*
     * Encode each folder/file separately.
     *
     * Example:
     *
     * Bagabag/SC-0001/profile.jpg
     *
     * becomes a safe URL.
     */

    $parts = explode('/', $path);

    $encodedParts = array_map(
        'rawurlencode',
        $parts
    );


    return rtrim($supabaseUrl, '/') .
        '/storage/v1/object/public/' .
        $storageBucket .
        '/' .
        implode('/', $encodedParts);
}


/* =========================================
   IMAGE URLS
========================================= */

$photo = storageUrl(
    $record['photo'] ?? null
);

$psaImage = storageUrl(
    $record['psa'] ?? null
);

$ncscImage = storageUrl(
    $record['ncsc_form'] ?? null
);

$seniorIdImage = storageUrl(
    $record['senior_id_image'] ?? null
);

?>

This uses the `$pdo` connection from your `db.php`, which is the connection your project actually creates.



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Record</title>
</head>

<body>
    <?php include '../../assets/sidebar/sidebar.php'; ?>
    <link rel="stylesheet" href="../../pages/list/viewrecord.css">
    </aside>
    <main class="main-content">
        <?php include '../../assets/navbar/navbar.php'; ?>
        <div class="record-view">

            <!-- =========================================
             HEADER
        ========================================= -->

            <div class="record-view-actions">

                <a href="<?php echo $baseUrl; ?>/pages/list/seniorList.php" class="back-button">

                    <i class="fa-solid fa-arrow-left"></i>
                    BACK

                </a>

                <div class="record-view-right-actions">

                    <button type="button" class="edit-button" id="editButton" onclick="toggleEditMode()">

                        <i class="fa-solid fa-pen-to-square"></i>
                        EDIT

                    </button>

                    <button type="button" class="delete-button" onclick="deleteSenior()">

                        <i class="fa-solid fa-trash"></i>
                        DELETE

                    </button>

                </div>

            </div>
        </div> <br>




        <!-- =========================================
             MAIN RECORD CONTENT
        ========================================= -->

        <div class="record-layout">


            <!-- =====================================
                 2x2 PHOTO
            ====================================== -->

            <div class="record-photo-card">

                <div class="photo-card-title">

                    <i class="fa-solid fa-camera"></i>

                    <span>2 × 2 Photo</span>

                </div>


                <div class="photo-container">

                    <img src="<?php echo htmlspecialchars($photo); ?>"
                        alt="<?php echo htmlspecialchars($fullName); ?> Photo" class="senior-photo">

                </div>


                <div class="photo-name">

                    <strong>
                        <?php echo htmlspecialchars(strtoupper($fullName)); ?>
                    </strong>

                    <span>Senior Citizen</span>

                </div>

            </div>


            <!-- =====================================
                 PERSONAL INFORMATION
            ====================================== -->

            <div class="record-info-card">

                <div class="record-section-header">

                    <div>

                        <h2>
                            <i class="fa-solid fa-user-tie" style="color: #3272c7;"></i>
                            PERSONAL INFORMATION
                        </h2>

                        <p>
                            Basic information of the senior citizen.
                        </p>

                    </div>


                    <span class="status-badge <?php echo $statusClass; ?>" id="statusValue" data-editable="true"
                        data-field="status">

                        <?php echo htmlspecialchars($record['status']); ?>

                    </span>

                </div>


                <div class="record-info-grid">


                    <!-- SENIOR ID -->
                    <div class="info-item">

                        <span class="info-label">
                            Senior ID
                        </span>

                        <span class="info-value">

                            <span id="seniorIdValue" data-editable="true" data-field="senior_id">
                                <?php echo htmlspecialchars($record['senior_id']); ?>
                            </span>

                        </span>

                    </div>


                    <!-- RRN -->
                    <div class="info-item">

                        <span class="info-label">
                            RRN
                        </span>

                        <span class="info-value">

                            <?php if (!empty($record['rrn'])): ?>

                                <span id="rrnValue" data-editable="true"
                                    data-field="rrn"><?php echo htmlspecialchars($record['rrn']); ?></span>

                            <?php else: ?>

                                <span id="rrnValue" data-editable="true" data-field="rrn" class="not-provided">
                                    Not provided
                                </span>

                                <a href="#" class="add-rrn-button" onclick="openRRNModal(event)">

                                    <i class="fa-solid fa-plus"></i>
                                    Add RRN

                                </a>
                                <!-- =========================================
                                    ADD RRN MODAL
                                ========================================= -->

                                <div class="rrn-modal-overlay" id="rrnModal">

                                    <div class="rrn-modal">

                                        <div class="rrn-modal-header">

                                            <div>
                                                <h2>Add RRN</h2>
                                                <p>Enter the RRN for this senior citizen.</p>
                                            </div>

                                            <button type="button" class="rrn-modal-close" onclick="closeRRNModal()">

                                                <i class="fa-solid fa-xmark"></i>

                                            </button>

                                        </div>


                                        <form method="POST" action="">

                                            <div class="rrn-form-group">

                                                <label for="rrn">
                                                    RRN
                                                </label>

                                                <input type="text" id="rrn" name="rrn" placeholder="Enter RRN"
                                                    maxlength="50" required>

                                            </div>


                                            <div class="rrn-modal-actions">

                                                <button type="button" class="rrn-cancel-button" onclick="closeRRNModal()">

                                                    Cancel

                                                </button>

                                                <button type="submit" class="rrn-save-button">

                                                    <i class="fa-solid fa-floppy-disk"></i>
                                                    Save RRN

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            <?php endif; ?>


                        </span>

                    </div>


                    <!-- FULL NAME -->
                    <div class="info-item">

                        <span class="info-label">
                            Full Name
                        </span>

                        <span class="info-value">

                            <span id="fullNameDisplay"><?php echo htmlspecialchars(strtoupper($fullName)); ?></span>

                            <span id="firstNameValue" data-editable="true" data-field="first_name"
                                style="display:none;">
                                <?php echo htmlspecialchars($record['first_name']); ?>
                            </span>

                            <span id="middleNameValue" data-editable="true" data-field="middle_name"
                                style="display:none;">
                                <?php echo htmlspecialchars($record['middle_name'] ?? ''); ?>
                            </span>

                            <span id="lastNameValue" data-editable="true" data-field="last_name" style="display:none;">
                                <?php echo htmlspecialchars($record['last_name']); ?>
                            </span>

                        </span>

                    </div>


                    <!-- BIRTH DATE -->
                    <div class="info-item">

                        <span class="info-label">
                            Birth Date
                        </span>

                        <span class="info-value">

                            <span id="birthDateValue" data-editable="true"
                                data-field="birth_date"><?php echo htmlspecialchars(strtoupper($birthDate)); ?></span>

                        </span>

                    </div>


                    <!-- AGE -->
                    <div class="info-item">

                        <span class="info-label">
                            Age
                        </span>

                        <span class="info-value">

                            <span id="ageValue" data-editable="true" data-field="age">
                                <?php echo $age !== '' ? htmlspecialchars($age . ' years old') : 'Not provided'; ?>
                            </span>

                        </span>

                    </div>


                    <!-- SEX -->
                    <div class="info-item">

                        <span class="info-label">
                            Sex
                        </span>

                        <span class="info-value">

                            <span id="sexValue" data-editable="true"
                                data-field="sex"><?php echo htmlspecialchars($record['sex']); ?></span>

                        </span>

                    </div>

                    <!-- ADDRESS -->
                    <div class="info-item">

                        <span class="info-label">
                            Address (Purok, Barangay)
                        </span>

                        <span class="info-value">

                            <?php
                            $purok = !empty($record['purok'])
                                ? $record['purok']
                                : '';

                            $barangay = !empty($record['barangay'])
                                ? $record['barangay']
                                : '';
                            ?>

                            <span id="purokValue" data-editable="true"
                                data-field="purok"><?php echo htmlspecialchars(strtoupper($purok)); ?></span><?php if (!empty($purok) && !empty($barangay))
                                       echo ', '; ?><span id="barangayValue" data-editable="true"
                                data-field="barangay"><?php echo htmlspecialchars(strtoupper($barangay)); ?></span>

                        </span>

                    </div>


                    <!-- CONTACT NUMBER -->
                    <div class="info-item">

                        <span class="info-label">
                            Contact Number
                        </span>

                        <span class="info-value">

                            <?php if (!empty($record['contact_number'])): ?>

                                <span id="contactNumberValue" data-editable="true"
                                    data-field="contact_number"><?php echo htmlspecialchars($record['contact_number']); ?></span>

                            <?php else: ?>

                                <span id="contactNumberValue" data-editable="true" data-field="contact_number"
                                    class="not-provided" style="font-size: 21;">
                                    Not provided
                                </span>

                            <?php endif; ?>

                        </span>

                    </div>


                    <!-- DATE REGISTERED -->
                    <div class="info-item">

                        <span class="info-label">
                            Date Registered
                        </span>

                        <span class="info-value">

                            <?php echo htmlspecialchars(strtoupper($registrationDate)); ?>

                        </span>

                    </div>


                    <!-- DECEASED STATUS -->
                    <div class="info-item">

                        <span class="info-label">
                            Deceased
                        </span>


                        <span class="info-value">

                            <span id="isDeceasedValue" data-editable="true"
                                data-field="is_deceased"><?php echo $record['is_deceased'] == 1 ? 'Yes' : 'No'; ?></span>

                        </span>

                    </div>

                </div>


            </div>

        </div>
        </div>

        <!-- =========================================
     SENIOR CITIZEN INFORMATION
========================================= -->

        <div class="record-info-card">

            <div class="record-section-header">

                <div>
                    <h2>
                        <i class="fa-solid fa-user-pen" style="color: #3272c7;"></i>
                        SENIOR CITIZEN INFORMATION
                    </h2>

                    <p>
                        Pension, PhilHealth, dependency, housing, health, and medication information.
                    </p>
                </div>

            </div>


            <div class="record-info-grid">


                <!-- PENSION -->
                <div class="info-item">

                    <span class="info-label">
                        Pension
                    </span>

                    <span class="info-value">

                        <span id="pensionValue" data-editable="true" data-field="pension">
                            <?php
                            echo !empty($record['pension'])
                                ? htmlspecialchars($record['pension'])
                                : 'Not provided';
                            ?>
                        </span>

                    </span>

                </div>


                <!-- PHILHEALTH NUMBER -->
                <div class="info-item">

                    <span class="info-label">
                        PhilHealth Number
                    </span>

                    <span class="info-value">

                        <span id="philhealthNumberValue" data-editable="true" data-field="philhealth_number">
                            <?php
                            echo !empty($record['philhealth_number'])
                                ? htmlspecialchars($record['philhealth_number'])
                                : 'Not provided';
                            ?>
                        </span>

                    </span>

                </div>


                <!-- DEPENDENCY -->
                <div class="info-item">

                    <span class="info-label">
                        Dependency
                    </span>

                    <span class="info-value">

                        <span id="dependencyValue" data-editable="true" data-field="dependency">
                            <?php
                            echo !empty($record['dependency'])
                                ? htmlspecialchars($record['dependency'])
                                : 'Not provided';
                            ?>
                        </span>

                    </span>

                </div>


                <!-- HOUSING -->
                <div class="info-item">

                    <span class="info-label">
                        Housing
                    </span>

                    <span class="info-value">

                        <span id="housingValue" data-editable="true" data-field="housing">
                            <?php
                            echo !empty($record['housing'])
                                ? htmlspecialchars($record['housing'])
                                : 'Not provided';
                            ?>
                        </span>

                    </span>

                </div>


                <!-- HEALTH PROBLEMS -->
                <div class="info-item">

                    <span class="info-label">
                        Health Problems
                    </span>

                    <span class="info-value">

                        <span id="healthProblemsValue" data-editable="true" data-field="health_problems">
                            <?php
                            echo !empty($record['health_problems'])
                                ? nl2br(htmlspecialchars($record['health_problems']))
                                : 'Not provided';
                            ?>
                        </span>

                    </span>

                </div>


                <!-- MEDICINES -->
                <div class="info-item">

                    <span class="info-label">
                        Medicines / Maintenance Drugs
                    </span>

                    <span class="info-value">

                        <span id="medicinesValue" data-editable="true" data-field="medicines">
                            <?php
                            echo !empty($record['medicines'])
                                ? nl2br(htmlspecialchars($record['medicines']))
                                : 'Not provided';
                            ?>
                        </span>

                    </span>

                </div>


                <!-- DISABILITY -->
                <div class="info-item">

                    <span class="info-label">
                        Disability & Reference Code
                    </span>

                    <span class="info-value">

                        <span id="disabilityValue" data-editable="true" data-field="disability">
                            <?php
                            echo !empty($record['disability'])
                                ? htmlspecialchars($record['disability'])
                                : 'Not provided';
                            ?>
                        </span>

                    </span>

                </div>


            </div>

        </div>
        <br>
        <!-- =========================================
             ID TYPE SWITCH
        ========================================= -->

        <div class="id-switch-card">

            <div class="id-switch-label">
                <i class="fa-solid fa-id-card"></i>

                <div>
                    <strong style="font-size: 20px;">Identification Document</strong>
                    <span style="font-size: 14px;"> Select the document to view</span>
                </div>
            </div>


            <div class="id-switch">

                <button type="button" class="id-switch-option active" data-id="psa">

                    PSA

                </button>

                <button type="button" class="id-switch-option" data-id="ncsc">

                    NCSC

                </button>

                <button type="button" class="id-switch-option" data-id="back">

                    Senior ID

                </button>

            </div>

        </div>

        <!-- =========================================
             ID PREVIEW
        ========================================= -->

        <div class="id-preview-card">

            <div class="id-preview-header">

                <div>
                    <h3 id="idTitle" style="font-size: 20px;">PSA Identification</h3>

                    <p id="idSubtitle" style="font-size: 15px;">
                        Philippine Statistics Authority identification document
                    </p>
                </div>
                <span>
                    <button type="button" class="print-button" onclick="printIDImage()">

                        <i class="fa-solid fa-print"></i>
                        PRINT

                    </button>
                </span>
                <!-- <span class="document-badge" id="idBadge">
                    PSA
                </span> -->

            </div>


            <div class="id-image-container">

                <img id="idImage" src="<?php echo htmlspecialchars($psaImage); ?>" alt="Identification Document"
                    data-psa="<?php echo htmlspecialchars($psaImage); ?>"
                    data-ncsc="<?php echo htmlspecialchars($ncscImage); ?>"
                    data-back="<?php echo htmlspecialchars($seniorIdImage); ?>">

            </div>

        </div>

        </div>

        </div>

    </main>
    <div id="recordMetadata" data-senior-id="<?php echo htmlspecialchars($record['senior_id'], ENT_QUOTES); ?>"
        style="display:none;"></div>
    <script src="../../pages/list/viewrecord.js"></script>
</body>

</html>