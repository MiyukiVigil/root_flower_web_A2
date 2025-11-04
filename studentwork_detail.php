<?php
session_start();

// Include DB connection
require_once 'connection.php';

// Get the ID from the URL, default to null if not set
$work_id = $_GET['id'] ?? null;
$selected_work = null;

if ($work_id) {
    try {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, workshop_title, workshop_image, description 
                                FROM studentwork_table 
                                WHERE id = ?");
        $stmt->execute([$work_id]);
        $selected_work = $stmt->fetch(PDO::FETCH_ASSOC);

        // Combine first_name and last_name into 'name' for consistency with old code
        if ($selected_work) {
            $selected_work['name'] = $selected_work['first_name'] . ' ' . $selected_work['last_name'];
            $selected_work['workshop'] = $selected_work['workshop_title'];
            $selected_work['image'] = $selected_work['workshop_image'];
            $selected_work['desc'] = $selected_work['description'];
        }
    } catch (PDOException $e) {
        echo "Error fetching student work: " . $e->getMessage();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $selected_work ? htmlspecialchars($selected_work['name']) . "'s Work" : 'Work Not Found' ?></title>
    <meta name="author" content="Ivan">
    <meta name="keywords" content="Student Work, Details">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="./style/style.css" rel="stylesheet">
    <link rel="icon" href="./images/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="studentwork-details-page">
<header>
    <?php include 'includes/navbar.inc'; ?>
</header>

<main class="container my-5">
    <?php if ($selected_work): ?>
        <div class="details-content-box">
            <div class="row g-6 align-items-center">
                <div class="col-lg-5">
                    <img src="./images/student_works/<?= htmlspecialchars($selected_work['image']) ?>" class="img-fluid rounded shadow-lg student_img" alt="Work by <?= htmlspecialchars($selected_work['name']) ?>">
                </div>
                <div class="col-lg-7">
                    <h1 class="display-5 fw-bold"><?= htmlspecialchars($selected_work['name']) ?></h1>

                    <p class="workshop-badge mb-3">
                        <i class="bi bi-palette-fill me-1"></i>
                        From the "<?= htmlspecialchars($selected_work['workshop']) ?>" workshop
                    </p>

                    <hr class="my-4">

                    <blockquote class="student-description fs-5">
                        <p>"<?= nl2br(htmlspecialchars($selected_work['desc'])) ?>"</p>
                    </blockquote>

                    <a href="studentworks.php" class="btn btn-primary mt-4 shadow-sm">
                        <i class="bi bi-arrow-left"></i> Back to Gallery
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center bg-white p-5 rounded shadow-lg">
            <h2 class="text-primary">Work Not Found</h2>
            <p class="lead">Sorry, we couldn't find the student work you were looking for.</p>
            <a href="studentworks.php" class="btn btn-primary mt-3">Back to Gallery</a>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.inc'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
