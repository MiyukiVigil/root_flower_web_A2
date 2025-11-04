<?php
session_start();
require_once 'connection.php';

// Access control: Only logged-in users of type 'user'
if (!isset($_SESSION['user_email']) || ($_SESSION['user_type'] ?? '') !== 'user') {
    echo '<div class="text-center mt-5">';
    echo '<h2>Access Denied</h2>';
    echo '<p>You must be logged in as a user to contribute. <a href="login.php">Login here</a>.</p>';
    echo '</div>';
    exit;
}

$feedback = '';
$errors = [];

// Ensure upload directories exist
$photoDir = __DIR__ . '/flower_images';
$descDir = __DIR__ . '/flower_description';
if (!is_dir($photoDir)) mkdir($photoDir, 0755, true);
if (!is_dir($descDir)) mkdir($descDir, 0755, true);

// Helper function to sanitize file names
function safe_filename($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
    return $name . ($ext ? '.' . $ext : '');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scientific_name = trim($_POST['scientific_name'] ?? '');
    $common_name = trim($_POST['common_name'] ?? '');
    
    // Validation
    if ($scientific_name === '') $errors[] = 'Scientific Name is required.';
    if ($common_name === '') $errors[] = 'Common Name is required.';
    
    // Handle flower photo
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $pf = $_FILES['photo'];
        if ($pf['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading flower photo.';
        } else {
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($pf['type'], $allowedMimes)) $errors[] = 'Photo must be JPG, JPEG, or PNG.';
            if ($pf['size'] > 5 * 1024 * 1024) $errors[] = 'Photo must be 5MB or smaller.';
            
            if (empty($errors)) {
                $safe = safe_filename($pf['name']);
                $uniq = uniqid('flower_') . '_' . $safe;
                $dest = $photoDir . '/' . $uniq;
                if (move_uploaded_file($pf['tmp_name'], $dest)) {
                    $photo_path = 'flower_images/' . $uniq;
                } else {
                    $errors[] = 'Failed to move uploaded photo.';
                }
            }
        }
    }

    // Handle description file (PDF)
    $desc_path = null;
    if (isset($_FILES['description']) && $_FILES['description']['error'] !== UPLOAD_ERR_NO_FILE) {
        $df = $_FILES['description'];
        if ($df['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading description file.';
        } else {
            $allowedExt = ['pdf'];
            $ext = strtolower(pathinfo($df['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) $errors[] = 'Description must be a PDF file.';
            if ($df['size'] > 7 * 1024 * 1024) $errors[] = 'Description must be 7MB or smaller.';
            
            if (empty($errors)) {
                $safe = safe_filename($df['name']);
                $uniq = uniqid('desc_') . '_' . $safe;
                $dest = $descDir . '/' . $uniq;
                if (move_uploaded_file($df['tmp_name'], $dest)) {
                    $desc_path = 'flower_description/' . $uniq;
                } else {
                    $errors[] = 'Failed to move uploaded description.';
                }
            }
        }
    }

    // Insert into database if no errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO flower_table (Scientific_Name, Common_Name, plants_image, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$scientific_name, $common_name, $photo_path, $desc_path]);
            $feedback = 'Flower contribution uploaded successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contribute Flower</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="contribute-flower-page">
<header class="container py-3">
    <h2>Contribute a Flower</h2>
</header>

<main class="container my-4">
    <?php if ($feedback): ?>
        <div class="alert alert-success"><?= htmlspecialchars($feedback) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
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
            <label for="photo" class="form-label">Flower Photo (JPG, JPEG, PNG | ≤5MB)</label>
            <input type="file" class="form-control" id="photo" name="photo" accept=".jpg,.jpeg,.png">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description File (PDF | ≤7MB)</label>
            <input type="file" class="form-control" id="description" name="description" accept=".pdf">
        </div>
        <button type="submit" class="btn btn-primary">Upload Flower</button>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
