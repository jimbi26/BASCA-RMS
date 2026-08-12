<?php

include '../../config/db.php';


/* =========================================================
   SUPABASE STORAGE SETTINGS
========================================================= */

$supabaseUrl = getenv('SUPABASE_URL') ?: 'https://qrsxsxbnndjajhnjwuwv.supabase.co';

$supabaseServiceKey = getenv('SUPABASE_SERVICE_KEY') ?: '';

$storageBucket = getenv('SUPABASE_BUCKET') ?: 'senior-documents';


/* =========================================================
   DATABASE CONNECTION
========================================================= */


/* =========================================================
   SUPABASE UPLOAD FUNCTION
========================================================= */

function uploadToSupabase($file, $storagePath)
{
    global $supabaseUrl;
    global $supabaseServiceKey;
    global $storageBucket;

    /* No file selected */
    if (
        !isset($file) ||
        !isset($file['error']) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    /* PHP upload error */
    if ($file['error'] !== UPLOAD_ERR_OK) {

        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the PHP upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the form upload limit.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write the uploaded file.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload.'
        ];

        $message = $uploadErrors[$file['error']]
            ?? 'Unknown PHP upload error.';

        throw new Exception(
            "File '{$file['name']}': {$message}"
        );
    }

    /* Allowed image types */
    $allowedTypes = [
        'image/jpeg',
        'image/png'
    ];

    /*
     * Use finfo instead of trusting $_FILES['type']
     */
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $realMimeType = $finfo->file($file['tmp_name']);

    if (!in_array($realMimeType, $allowedTypes, true)) {

        throw new Exception(
            "File '{$file['name']}': Only JPG, JPEG, and PNG files are allowed."
        );
    }

    /* Maximum file size: 5MB */
    if ($file['size'] > 5 * 1024 * 1024) {

        throw new Exception(
            "File '{$file['name']}': The maximum file size is 5MB."
        );
    }

    /* Get extension */
    $extension = strtolower(
        pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
        )
    );

    /*
     * Make sure extension matches actual MIME type
     */
    if ($realMimeType === 'image/jpeg') {
        $extension = 'jpg';
    } elseif ($realMimeType === 'image/png') {
        $extension = 'png';
    }

    /* Add extension */
    $storagePath .= '.' . $extension;

    /*
     * Clean storage path
     */
    $storagePath = ltrim($storagePath, '/');

    /* Supabase Storage URL */
    $url =
        rtrim($supabaseUrl, '/') .
        '/storage/v1/object/' .
        rawurlencode($storageBucket) .
        '/' .
        str_replace(
            '%2F',
            '/',
            rawurlencode($storagePath)
        );

    /* Read temporary uploaded file */
    $fileData = file_get_contents(
        $file['tmp_name']
    );

    if ($fileData === false) {

        throw new Exception(
            "File '{$file['name']}': Unable to read uploaded file."
        );
    }

    /*
     * Upload to Supabase Storage
     */
    $ch = curl_init($url);

    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_POST => true,

        CURLOPT_POSTFIELDS => $fileData,

        CURLOPT_HTTPHEADER => [

            'Authorization: Bearer ' . $supabaseServiceKey,
            'apikey: ' . $supabaseServiceKey,
            'Content-Type: ' . $realMimeType,
            'Content-Length: ' . strlen($fileData),
            'x-upsert: true',
            'Connection: keep-alive'

        ],

        /*
         * Connection timeout
         */
        CURLOPT_CONNECTTIMEOUT => 15,

        /*
         * Maximum time allowed for
         * one upload
         */
        CURLOPT_TIMEOUT => 120,

        /*
         * Follow redirects
         */
        CURLOPT_FOLLOWLOCATION => true,

    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    $curlErrorNumber = curl_errno($ch);

    $curlError = curl_error($ch);

    curl_close($ch);


    /*
     * cURL connection error
     */
    if ($response === false) {

        throw new Exception(
            "File '{$file['name']}': Supabase connection failed. " .
            "cURL error {$curlErrorNumber}: {$curlError}"
        );
    }


    /*
     * Supabase returned an HTTP error
     */
    if (
        $httpCode < 200 ||
        $httpCode >= 300
    ) {

        /*
         * Try to decode Supabase error
         */
        $supabaseError = '';

        $decodedResponse = json_decode(
            $response,
            true
        );

        if (is_array($decodedResponse)) {

            if (isset($decodedResponse['message'])) {
                $supabaseError = $decodedResponse['message'];
            } elseif (isset($decodedResponse['error'])) {
                $supabaseError = $decodedResponse['error'];
            }
        }

        if ($supabaseError === '') {
            $supabaseError = $response;
        }

        throw new Exception(
            "File '{$file['name']}': Supabase upload failed. " .
            "HTTP {$httpCode}. " .
            $supabaseError
        );
    }


    return $storagePath;
}

/* =========================================================
   FORM SUBMISSION
========================================================= */

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        /* =================================================
           GET FORM VALUES
        ================================================= */

        $senior_id = trim(
            $_POST['senior_id'] ?? ''
        );

        $rrn = trim(
            $_POST['rrn'] ?? ''
        );

        $first_name = trim(
            $_POST['first_name'] ?? ''
        );

        $middle_name = trim(
            $_POST['middle_name'] ?? ''
        );

        $last_name = trim(
            $_POST['last_name'] ?? ''
        );

        $birth_date = $_POST['birth_date'] ?? '';

        $sex = $_POST['sex'] ?? '';

        $barangay = trim(
            $_POST['barangay'] ?? ''
        );

        /* =================================================
           PUROK
        ================================================= */

        $purokNumber = $_POST['purok'] ?? '';

        if ($purokNumber !== '') {

            $purok = 'Purok ' . (int) $purokNumber;

        } else {

            $purok = null;

        }


        /* =================================================
           CALCULATE AGE
        ================================================= */

        $birthDateObject = new DateTime($birth_date);
        $today = new DateTime();

        $age = $today->diff($birthDateObject)->y;


        $contact_number = trim(
            $_POST['contact_number'] ?? ''
        );


        /* =================================================
           VALIDATION
        ================================================= */

        if (
            $senior_id === '' ||
            $first_name === '' ||
            $last_name === '' ||
            $birth_date === '' ||
            $sex === '' ||
            $barangay === '' ||
            $purok === null
        ) {

            throw new Exception(
                "Please fill in all required fields."
            );

        }


        /* =================================================
           CLEAN BARANGAY
        ================================================= */

        $barangayFolder = preg_replace(
            '/[^A-Za-z0-9 _-]/',
            '',
            $barangay
        );

        $barangayFolder = trim(
            $barangayFolder
        );


        /* =================================================
           CLEAN SENIOR ID
        ================================================= */

        $seniorFolder = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '',
            $senior_id
        );


        /* =================================================
           STORAGE FOLDER
        ================================================= */

        $storageFolder =
            $barangayFolder .
            '/' .
            $seniorFolder;


        /* =================================================
           UPLOAD PROFILE PHOTO
        ================================================= */

        $photo = uploadToSupabase(
            $_FILES['photo'] ?? null,
            $storageFolder . '/profile'
        );


        /* =================================================
           UPLOAD PSA
        ================================================= */

        $psa = uploadToSupabase(
            $_FILES['psa'] ?? null,
            $storageFolder . '/psa'
        );


        /* =================================================
           UPLOAD NCSC
        ================================================= */

        $ncsc_form = uploadToSupabase(
            $_FILES['ncsc_form'] ?? null,
            $storageFolder . '/ncsc'
        );


        /* =================================================
           UPLOAD SENIOR CITIZEN ID
        ================================================= */

        $senior_id_image = uploadToSupabase(
            $_FILES['senior_id_image'] ?? null,
            $storageFolder . '/senior-id'
        );


        /* =================================================
           INSERT INTO DATABASE
        ================================================= */

        $sql = "
            INSERT INTO senior_citizens
            (
                senior_id,
                rrn,
                first_name,
                middle_name,
                last_name,
                birth_date,
                sex,
                barangay,
                purok,
                contact_number,
                photo,
                psa,
                ncsc_form,
                senior_id_image
            )

            VALUES
            (
                :senior_id,
                :rrn,
                :first_name,
                :middle_name,
                :last_name,
                :birth_date,
                :sex,
                :barangay,
                :purok,
                :contact_number,
                :photo,
                :psa,
                :ncsc_form,
                :senior_id_image
            )
        ";


        $stmt = $pdo->prepare($sql);


        $stmt->execute([

            ':senior_id' =>
                $senior_id,

            ':rrn' =>
                $rrn !== ''
                ? $rrn
                : null,

            ':first_name' =>
                $first_name,

            ':middle_name' =>
                $middle_name !== ''
                ? $middle_name
                : null,

            ':last_name' =>
                $last_name,

            ':birth_date' =>
                $birth_date,

            ':sex' =>
                $sex,

            ':barangay' =>
                $barangay,

            ':purok' =>
                $purok,

            ':contact_number' =>
                $contact_number !== ''
                ? $contact_number
                : null,

            ':photo' =>
                $photo,

            ':psa' =>
                $psa,

            ':ncsc_form' =>
                $ncsc_form,

            ':senior_id_image' =>
                $senior_id_image

        ]);


        /* =================================================
           SUCCESS
        ================================================= */

        header(
            'Location: seniorList.php?success=1'
        );

        exit;


    } catch (Exception $e) {

        $errorMessage =
            $e->getMessage();

    }
}

?>

<?php include '../../assets/sidebar/sidebar.php'; ?>
<?php include '../../assets/navbar/navbar.php'; ?>

<main class="main-content">
    <link rel="stylesheet" href="../../pages/list/viewrecord.css">
    <section>

        <a href="seniorList.php" class="back-button-add-new">

            <i class="fa-solid fa-arrow-left"></i>

            BACK

        </a>

        <br>
        <br>


        <?php if ($errorMessage !== ''): ?>

            <div class="error-message">

                <?= htmlspecialchars($errorMessage) ?>

            </div>

        <?php endif; ?>


        <!-- =========================================
         PERSONAL INFORMATION
    ========================================== -->

        <form method="POST" enctype="multipart/form-data">

            <div class="form-card">

                <div class="card-header">

                    <div class="card-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>

                        <h3 style="font-size: 25px;">
                            Personal Information
                        </h3>

                        <p>
                            Enter the basic information of the senior citizen.
                        </p>

                    </div>

                </div>


                <div class="form-grid">


                    <!-- Senior ID -->

                    <div class="form-group">

                        <label for="senior_id">
                            Senior ID <span>*</span>
                        </label>

                        <input type="text" id="senior_id" name="senior_id" placeholder="ENTER SENIOR ID" required
                            style="font-size: 22px; text-transform: uppercase;">

                    </div>


                    <!-- RRN -->

                    <div class="form-group">

                        <label for="rrn">
                            RRN
                        </label>

                        <input type="text" id="rrn" name="rrn" placeholder="ENTER RRN" style="font-size: 22px;">

                    </div>


                    <!-- First Name -->

                    <div class="form-group">

                        <label for="first_name">
                            First Name <span>*</span>
                        </label>

                        <input type="text" id="first_name" name="first_name" placeholder="Enter first name"
                            style="font-size: 22px; text-transform: uppercase;" required>

                    </div>


                    <!-- Middle Name -->

                    <div class="form-group">

                        <label for="middle_name">
                            Middle Name
                        </label>

                        <input type="text" id="middle_name" name="middle_name" placeholder="Enter middle name"
                            style="font-size: 22px; text-transform: uppercase;">

                    </div>


                    <!-- Last Name -->

                    <div class="form-group">

                        <label for="last_name">
                            Last Name <span>*</span>
                        </label>

                        <input type="text" id="last_name" name="last_name" placeholder="Enter last name"
                            style="font-size: 22px; text-transform: uppercase;" required>

                    </div>


                    <!-- Barangay -->

                    <div class="form-group">

                        <label for="barangay">
                            Barangay <span>*</span>
                        </label>

                        <input type="text" id="barangay" name="barangay" placeholder="ENTER BARANGAY"
                            style="font-size: 22px;" required>

                    </div>

                    <div class="form-group">

                        <label for="purok">
                            Purok <span>*</span>
                        </label>

                        <input type="number" id="purok" name="purok" placeholder="ENTER PUROK" style="font-size: 22px;"
                            min="1" required>

                    </div>


                    <!-- Contact Number -->

                    <div class="form-group">

                        <label for="contact_number">
                            Contact Number
                        </label>

                        <input type="text" id="contact_number" name="contact_number" placeholder="ENTER CONTACT NUMBER"
                            style="font-size: 22px;">

                    </div>


                    <!-- Sex -->

                    <div class="form-group">

                        <label>
                            Sex <span>*</span>
                        </label>

                        <div class="radio-group">

                            <label class="radio-option">

                                <input type="radio" name="sex" value="Male" required>

                                MALE

                            </label>

                            <label class="radio-option">

                                <input type="radio" name="sex" value="Female">

                                FEMALE

                            </label>

                        </div>

                    </div>


                    <!-- Birth Date -->

                    <div class="form-group">

                        <label for="birth_date">
                            Birth Date <span>*</span>
                        </label>

                        <input type="date" id="birth_date" name="birth_date" style="font-size: 22px;" required>

                    </div>


                    <!-- Date Registered -->

                    <div class="form-group">

                        <label for="date_registered">
                            Date Registered
                        </label>

                        <input type="date" id="date_registered" name="date_registered" value="<?= date('Y-m-d') ?>"
                            style="font-size: 22px;" readonly>

                    </div>


                </div>

            </div>


            <!-- =========================================
             PROFILE PHOTO
        ========================================== -->

            <div class="form-card">

                <div class="card-header">

                    <div class="card-icon">
                        <i class="fa-solid fa-camera"></i>
                    </div>

                    <div>

                        <h3 style="font-size: 22px;">
                            Profile Photo
                        </h3>

                        <p>
                            Upload a photo of the senior citizen.
                        </p>

                    </div>

                </div>


                <div class="upload-box">

                    <i class="fa-solid fa-cloud-arrow-up"></i>

                    <strong style="font-size: 20px">
                        Upload Photo
                    </strong>

                    <span>
                        JPG, JPEG or PNG
                    </span>

                    <small>
                        Optional
                    </small>

                    <input type="file" name="photo" accept=".jpg,.jpeg,.png" style="font-size: 15px">

                </div>

            </div>


            <!-- =========================================
             DOCUMENTS
        ========================================== -->

            <div class="form-card">

                <div class="card-header">

                    <div class="card-icon">
                        <i class="fa-solid fa-file-image"></i>
                    </div>

                    <div>

                        <h3>
                            Documents & Identification
                        </h3>

                        <p>
                            Upload the required senior citizen documents.
                        </p>

                    </div>

                </div>


                <div class="document-grid">


                    <!-- PSA -->

                    <div class="document-upload">

                        <div class="document-icon">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>

                        <h4>
                            PSA
                        </h4>

                        <p>
                            Upload PSA document
                        </p>

                        <input type="file" name="psa" accept=".jpg,.jpeg,.png" style="font-size: 15px">

                    </div>


                    <!-- NCSC -->

                    <div class="document-upload">

                        <div class="document-icon">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>

                        <h4>
                            NCSC Form
                        </h4>

                        <p>
                            Upload NCSC Form
                        </p>

                        <input type="file" name="ncsc_form" accept=".jpg,.jpeg,.png" style="font-size: 15px">

                    </div>


                    <!-- Back-to-Back ID -->

                    <div class="document-upload">

                        <div class="document-icon">
                            <i class="fa-solid fa-id-card"></i>
                        </div>

                        <h4>
                            Senior Citizen ID
                        </h4>

                        <p>
                            Front & back ID image
                        </p>

                        <input type="file" name="senior_id_image" accept=".jpg,.jpeg,.png" style="font-size: 15px">

                    </div>

                </div>

            </div>


            <!-- =========================================
             ACTION BUTTONS
        ========================================== -->

            <div class="form-actions">

                <a href="seniorList.php" class="cancel-button">
                    Cancel
                </a>


                <button type="submit" class="save-button">

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Senior Citizen

                </button>

            </div>

        </form>

    </section>
</main>
