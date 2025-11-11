<?php
session_start();
require_once 'connection.php';
require __DIR__ . '/vendor/autoload.php';

// --- THIS IS THE CORRECT 'use' BLOCK ---
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\ApiCore\ApiException;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\WebDetection;
// --- (END OF CHANGES) ---

// Access control: Only logged-in users of type 'user'
if (!isset($_SESSION['user_email']) || ($_SESSION['user_type'] ?? '') !== 'user') {
    echo '<div class="text-center mt-5">';
    echo '<h2>Access Denied</h2>';
    echo '<p>You must be logged in as a user to contribute. <a href="login.php">Login here</a>.</p>';
    echo '</div>';
    exit;
}

// --- Initialize variables for BOTH forms ---
$upload_feedback = '';  // Success message for Task 3.4
$upload_errors = [];    // Error messages for Task 3.4
$found_flower = null;   // Success result for Task 5.1
$identify_error = '';   // Error message for Task 5.1

// Ensure upload directories exist
$photoDir = __DIR__ . '/flower_images';
$descDir = __DIR__ . '/flower_description';
if (!is_dir($photoDir)) mkdir($photoDir, 0755, true);
if (!is_dir($descDir)) mkdir($descDir, 0755, true);

// Helper function to sanitize file names (Unchanged)
function safe_filename($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
    return $name . ($ext ? '.' . $ext : '');
}

// --- Main Form Processing ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Task 3.4: Handle Flower Contribution (NOW WITH AI VALIDATION) ---
    if (isset($_POST['upload_flower'])) {
        $scientific_name = trim($_POST['scientific_name'] ?? '');
        $common_name = trim($_POST['common_name'] ?? '');
        
        if ($scientific_name === '') $upload_errors[] = 'Scientific Name is required.';
        if ($common_name === '') $upload_errors[] = 'Common Name is required.';

        // --- NEW AI VALIDATION STEP ---
        $is_a_flower = false; // Flag to check if AI validation passes
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            
            try {
                $options = ['credentials' => 'gcloud-service-key.json'];
                $imageAnnotator = new ImageAnnotatorClient($options);
                $imageContent = file_get_contents($_FILES['photo']['tmp_name']);
                
                $image = (new Image())->setContent($imageContent);
                $feature = (new Feature())->setType(Type::LABEL_DETECTION);
                $request = (new AnnotateImageRequest())->setImage($image)->setFeatures([$feature]);
                $batchRequest = (new BatchAnnotateImagesRequest())->setRequests([$request]);
                
                $response = $imageAnnotator->batchAnnotateImages($batchRequest);
                $labels = $response->getResponses()[0]->getLabelAnnotations();

                if ($labels) {
                    $labelsArray = iterator_to_array($labels); 
                    // --- END OF FIX ---

                    // Check the top 5 labels from the AI
                    $flower_keywords = ['flower', 'plant', 'petal', 'rose', 'daisy', 'sunflower', 'tulip', 'lily', 'orchid', 'flora'];
                    
                    // Now, use array_slice on our new $labelsArray
                    foreach (array_slice($labelsArray, 0, 5) as $label) {
                        if (in_array(strtolower($label->getDescription()), $flower_keywords)) {
                            $is_a_flower = true; // It's a flower!
                            break;
                        }
                    }
                }
                
                if (!$is_a_flower) {
                    $upload_errors[] = "AI Rejected: The uploaded image does not appear to be a flower.";
                }
                $imageAnnotator->close();

            } catch (ApiException $e) {
                $upload_errors[] = "AI Service Error: " . $e->getMessage();
            }
            
        } else {
            // No photo was uploaded
            $upload_errors[] = "A flower photo is required for AI validation.";
        }
        // --- END OF AI VALIDATION ---

        // Handle flower photo (only if AI check passed)
        $photo_path = null;
        if (empty($upload_errors)) {
            $pf = $_FILES['photo'];
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($pf['type'], $allowedMimes)) $upload_errors[] = 'Photo must be JPG, JPEG, or PNG.';
            if ($pf['size'] > 5 * 1024 * 1024) $upload_errors[] = 'Photo must be 5MB or smaller.';
            
            if (empty($upload_errors)) {
                $safe = safe_filename($pf['name']);
                $uniq = uniqid('flower_') . '_' . $safe;
                $dest = $photoDir . '/' . $uniq;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                    $photo_path = 'flower_images/' . $uniq;
                } else {
                    $upload_errors[] = 'Failed to move uploaded photo.';
                }
            }
        }

        // Handle description file (PDF) (only if AI check passed)
        $desc_path = null;
        if (empty($upload_errors) && isset($_FILES['description']) && $_FILES['description']['error'] !== UPLOAD_ERR_NO_FILE) {
            $df = $_FILES['description'];
            if ($df['error'] !== UPLOAD_ERR_OK) {
                $upload_errors[] = 'Error uploading description file.';
            } else {
                $allowedExt = ['pdf'];
                $ext = strtolower(pathinfo($df['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt)) $upload_errors[] = 'Description must be a PDF file.';
                if ($df['size'] > 7 * 1024 * 1024) $upload_errors[] = 'Description must be 7MB or smaller.';
                
                if (empty($upload_errors)) {
                    $safe = safe_filename($df['name']);
                    $uniq = uniqid('desc_') . '_' . $safe;
                    $dest = $descDir . '/' . $uniq;
                    if (move_uploaded_file($df['tmp_name'], $dest)) {
                        $desc_path = 'flower_description/' . $uniq;
                    } else {
                        $upload_errors[] = 'Failed to move uploaded description.';
                    }
                }
            }
        }

        // Insert into database if NO errors at all
        if (empty($upload_errors)) {
            try {
                $stmt = $conn->prepare("INSERT INTO flower_table (Scientific_Name, Common_Name, plants_image, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$scientific_name, $common_name, $photo_path, $desc_path]);
                $upload_feedback = 'AI Approved! Your flower contribution has been added.';
            } catch (PDOException $e) {
                $upload_errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
    
    // --- Task 5.1: Handle Flower Identification (NEW Weighted Score Logic) ---
    if (isset($_POST['identify_flower'])) {
        if (isset($_FILES['identify_photo']) && $_FILES['identify_photo']['error'] === UPLOAD_ERR_OK) {
            
            try {
                $options = ['credentials' => 'gcloud-service-key.json'];
                $imageAnnotator = new ImageAnnotatorClient($options);
                $imageContent = file_get_contents($_FILES['identify_photo']['tmp_name']);
                
                $image = (new Image())->setContent($imageContent);
                $feature = (new Feature())->setType(Type::WEB_DETECTION);
                $request = (new AnnotateImageRequest())->setImage($image)->setFeatures([$feature]);
                
                $batchRequest = (new BatchAnnotateImagesRequest())->setRequests([$request]);
                $response = $imageAnnotator->batchAnnotateImages($batchRequest);
                
                $webDetection = $response->getResponses()[0]->getWebDetection();
                $labels = $webDetection->getWebEntities();

                if ($labels) {
                    
                    // 1. Blacklist of generic terms to ignore
                    $blacklist = [
                        'flower', 'plant', 'flowering plant', 'petal', 'flora', 
                        'leaf', 'sky', 'water', 'nature', 'botany', 'close-up',
                        'wildflower', 'herbaceous plant'
                    ];
                    
                    // 2. Junk words to remove from specific guesses
                    $junk_words = [
                        'wild', 'in habitat', 'blue', 'purple', 'white', 'red',
                        'yellow', 'pink', 'macro', 'close up'
                    ];

                    $sql_score_parts = []; // To build the (CASE ... THEN X) parts
                    $search_params = [];   // To hold all the '%word%'
                    $loop_count = 0;
                    $ai_top_specific_guess = ''; // For error messages

                    // 3. --- THIS IS THE NEW LOGIC ---
                    // Loop through ALL guesses and build a weighted query
                    foreach ($labels as $label) {
                        $guess = $label->getDescription(); // e.g., "alstroemeria peruvian lily"
                        
                        // 4. If the guess is NOT on the main blacklist...
                        if (!in_array(strtolower($guess), $blacklist)) {
                            
                            $loop_count++;
                            if ($loop_count == 1) $ai_top_specific_guess = $guess;

                            // 5. Assign a weight. Top guess gets 5 points, 2nd gets 3, rest get 1.
                            $weight = 1; 
                            if ($loop_count == 1) $weight = 5; 
                            if ($loop_count == 2) $weight = 3; 

                            // 6. Clean the guess
                            $clean_guess = str_ireplace($junk_words, '', $guess);
                            $clean_guess = trim(preg_replace('/\s+/', ' ', $clean_guess));

                            // 7. Split into keywords
                            $search_words = explode(' ', $clean_guess);

                            foreach ($search_words as $word) {
                                if (strlen($word) > 2) { 
                                    // 8. Add this keyword to the query with its weight
                                    $sql_score_parts[] = "(CASE WHEN (Common_Name LIKE ? OR Scientific_Name LIKE ?) THEN $weight ELSE 0 END)";
                                    $search_params[] = '%' . $word . '%';
                                    $search_params[] = '%' . $word . '%';
                                }
                            }
                        }
                    }

                    // 9. Now, run ONE query that scores based on ALL keywords
                    if (!empty($sql_score_parts)) {
                        $sql = "SELECT *, (" . implode(' + ', $sql_score_parts) . ") AS match_score
                                FROM flower_table 
                                HAVING match_score > 0
                                ORDER BY match_score DESC
                                LIMIT 1";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->execute($search_params);
                        $found_flower = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    // --- END OF NEW LOGIC ---

                    // 10. If no match was found...
                    if (!$found_flower) {
                        if (!empty($ai_top_specific_guess)) {
                            $identify_error = "AI identified this as '" . htmlspecialchars($ai_top_specific_guess) . "', but we don't have that in our database.";
                        } else {
                            $identify_error = "AI could not find a specific flower name for this image.";
                        }
                    }
                    
                } else {
                    $identify_error = "AI could not identify this image. Please try a clearer photo.";
                }
                
                $imageAnnotator->close();

            } catch (ApiException $e) {
                $identify_error = "AI Service Error: " . $e->getMessage();
            } catch (Exception $e) {
                $identify_error = "General Error: " . $e->getMessage();
            }

        } else {
            $identify_error = "Error uploading photo for identification.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contribute & Identify Flower</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="style/style.css" rel="stylesheet">
</head>
<body class="contribute-flower-page">
<?php include 'includes/navbar.inc'; ?>

<main class="container my-4">

    <div class="form-card">
        <h2 class="form-card-title">🌸 Contribute a New Flower</h2>

        <?php if ($upload_feedback): ?>
            <div class="alert alert-success"><?= htmlspecialchars($upload_feedback) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($upload_errors)): ?>
            <div class="alert alert-danger">
                <p class="fw-bold mb-1">Upload Failed:</p>
                <ul class="mb-0">
                    <?php foreach ($upload_errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="flower.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="scientific_name" class="form-label">Scientific Name</label>
                <input type="text" class="form-control" id="scientific_name" name="scientific_name" required>
            </div>
            <div class="mb-3">
                <label for="common_name" class="form-label">Common Name</label>
                <input type="text" class="form-control" id="common_name" name="common_name" required>
            </div>
            <div class="mb-3">
                <label for="photo" class="form-label">Flower Photo (Required for AI Validation)</label>
                <input type="file" class="form-control" id="photo" name="photo" accept=".jpg,.jpeg,.png" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description File (PDF | ≤7MB)</label>
                <input type="file" class="form-control" id="description" name="description" accept=".pdf">
            </div>
            <button type="submit" name="upload_flower" class="btn btn-primary">Upload Flower</button>
        </form>
    </div>

    <div class="form-card mt-5">
        <h2 class="form-card-title">🤖 Identify with AI</h2>
        
        <form action="flower.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="identify_photo" class="form-label">Upload a Flower Photo</label>
                <input type="file" class="form-control" id="identify_photo" name="identify_photo" accept="image/png, image/jpeg, image/jpg" required>
            </div>
            <button type="submit" name="identify_flower" class="btn btn-primary">Identify</button>
        </form>

        <?php if ($identify_error): ?>
            <div class="alert alert-warning text-center mt-4">
                <i class="bi bi-search-heart fs-3"></i><br>
                <?php echo htmlspecialchars($identify_error); ?>
            </div>
        <?php endif; ?>

        <?php if ($found_flower): ?>
            <div class="result-card p-4 mt-4">
                <h3 class="text-center mb-4">Identification Result</h3>
                <div class="row g-4 align-items-center">
                    <div class="col-md-5 text-center">
                        <img src="<?php echo htmlspecialchars($found_flower['plants_image']); ?>" class="img-fluid rounded shadow-sm" alt="<?php echo htmlspecialchars($found_flower['Common_Name']); ?>">
                    </div>
                    <div class="col-md-7">
                        <h4><?php echo htmlspecialchars($found_flower['Common_Name']); ?></h4>
                        <p class="text-muted fst-italic"><?php echo htmlspecialchars($found_flower['Scientific_Name']); ?></p>
                        
                        <hr>
                        
                        <h5>Downloads</h5>
                        <?php if (!empty($found_flower['description'])): ?>
                            <a href="<?php echo htmlspecialchars($found_flower['description']); ?>" class="btn btn-outline-dark" target="_blank">
                                <i class="bi bi-file-earmark-pdf me-1"></i> View PDF Description
                            </a>
                        <?php else: ?>
                            <p class="text-muted">No description file available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.inc'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>