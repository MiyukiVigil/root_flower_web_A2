<?php
session_start();

// Include database connection
require_once 'connection.php';

// Fetch student works from database
$student_works = [];
try {
    $stmt = $conn->query("SELECT id, first_name, last_name, workshop_title, workshop_image, description FROM studentwork_table ORDER BY id ASC");
    $student_works = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Optional: handle error
    echo "Error fetching student works: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Student Works</title>
        <meta name="author" content="Ivan">
        <meta name="keywords" content="Student Works, Gallery">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
        <link href="./style/style.css" rel="stylesheet">
        <link rel="icon" href="./images/logo.svg">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    </head>
    <body class="studentworks-page">
       <header>
            <?php include 'includes/navbar.inc'; ?>
       </header>

        <section class="page-header text-center text-white py-5" style="background: url('./images/hero.jpg') no-repeat center center/cover;">
            <div class="container hero-content">
                <h1 class="display-4 fw-bold">Student Works</h1>
                <p class="lead">Discover the beautiful creations from our talented workshop attendees.</p>
            </div>
        </section>

        <main class="container my-5">
            <div class="row g-4">
                <?php if (empty($student_works)): ?>
                    <div class="col-12">
                        <p class="text-center text-muted">No student works to display yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($student_works as $work): ?>
                        <div class="col-lg-4 col-md-6">
                            <a href="studentwork_detail.php?id=<?= htmlspecialchars($work['id']) ?>" class="gallery-card">
                                <img src="./images/student_works/<?= htmlspecialchars($work['workshop_image']) ?>" alt="Work by <?= htmlspecialchars($work['first_name'] . ' ' . $work['last_name']) ?>">
                                <div class="gallery-card-overlay">
                                    <h5 class="gallery-card-title"><?= htmlspecialchars($work['first_name'] . ' ' . $work['last_name']) ?></h5>
                                    <p class="gallery-card-text">from "<?= htmlspecialchars($work['workshop_title']) ?>"</p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <?php include 'includes/footer.inc'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>