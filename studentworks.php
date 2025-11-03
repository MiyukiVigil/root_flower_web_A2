<?php
    session_start();
    
    // Read the data from our text file database
    $works_file = __DIR__ . '/data/rootflower.txt';
    $student_works = [];
    if (file_exists($works_file)) {
        $lines = file($works_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Explode the line into parts
            $parts = explode('|', $line, 5); // Limit to 5 parts
            if (count($parts) === 5) {
                $student_works[] = [
                    'id' => $parts[0],
                    'name' => $parts[1],
                    'workshop' => $parts[2],
                    'image' => $parts[3],
                    'desc' => $parts[4]
                ];
            }
        }
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
                                <img src="./images/student_works/<?= htmlspecialchars($work['image']) ?>" alt="Work by <?= htmlspecialchars($work['name']) ?>">
                                <div class="gallery-card-overlay">
                                    <h5 class="gallery-card-title"><?= htmlspecialchars($work['name']) ?></h5>
                                    <p class="gallery-card-text">from "<?= htmlspecialchars($work['workshop']) ?>"</p>
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