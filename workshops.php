<?php 
    session_start();

    // Page is for logged-in users only
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }

    // Tells the browser and any proxies not to cache the page.
    header("Cache-Control: no-cache, no-store, must-revalidate");
    // For older HTTP/1.0 clients.
    header("Pragma: no-cache");
    // For proxies and old browsers, sets the expiration date to the past.
    header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Workshops</title>
        <meta name="author" content="Ivan">
        <meta name="keywords" content="Workshops, Floral Design, Classes">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
        <link href="./style/style.css" rel="stylesheet">
        <link rel="icon" href="./images/logo.svg">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    </head>
    <body class="workshop-page">
       <header>
            <?php include 'includes/navbar.inc'; ?>
       </header>

        <section class="page-header text-center text-white py-5" style="background: url('./images/hero.jpg') no-repeat center center/cover;">
            <div class="container hero-content">
                <h1 class="display-4 fw-bold">Our Workshops</h1>
                <p class="lead">Join us to learn the art of floral design from our expert florists.</p>
            </div>
        </section>

        <main class="container my-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="accordion workshop-accordion" id="workshopAccordion">

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingHobby">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHobby" aria-expanded="true" aria-controls="collapseHobby">
                                    <i class="bi bi-calendar3-event me-3"></i>
                                    Hobby Class
                                </button>
                            </h2>
                            <div id="collapseHobby" class="accordion-collapse collapse show" aria-labelledby="headingHobby" data-bs-parent="#workshopAccordion">
                                <div class="accordion-body">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-5">
                                            <img src="./images/workshops/class4.jpg" class="img-fluid rounded shadow-sm" alt="Hobby Class Flyer">
                                        </div>
                                        <div class="col-md-7">
                                            <p>Perfect for beginners or as a fun activity. Join our single-day workshops to learn a specific skill and take home a beautiful creation.</p>
                                            <h6 class="text-muted">Details:</h6>
                                            <ul class="list-unstyled workshop-details">
                                                <li><i class="bi bi-calendar-check"></i> <strong>Upcoming Dates:</strong> Oct 18, 2025 | Nov 15, 2025</li>
                                                <li><i class="bi bi-clock"></i> <strong>Time:</strong> 10:00 AM - 1:00 PM</li>
                                                <li><i class="bi bi-tag"></i> <strong>Price:</strong> RM 180</li>
                                                <li><i class="bi bi-geo-alt"></i> <strong>Venue:</strong> Root Flowers Studio, Kuching</li>
                                            </ul>
                                            <a href="workshop_reg.php?title=Hobby+Class" class="btn btn-primary w-100 mt-3">Register for Hobby Class</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingHandtied">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHandtied" aria-expanded="false" aria-controls="collapseHandtied">
                                    <i class="bi bi-scissors me-3"></i>
                                    Hand-tied Bouquet Course
                                </button>
                            </h2>
                            <div id="collapseHandtied" class="accordion-collapse collapse" aria-labelledby="headingHandtied" data-bs-parent="#workshopAccordion">
                                <div class="accordion-body">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-5">
                                            <img src="./images/workshops/class1.jpg" class="img-fluid rounded shadow-sm" alt="Hand-tied Bouquet Course Flyer">
                                        </div>
                                        <div class="col-md-7">
                                            <p>Master the art of bouquet making. This comprehensive 2-day course covers a wide range of popular styles, from classic to contemporary hand-tied designs.</p>
                                            <h6 class="text-muted">Details:</h6>
                                            <ul class="list-unstyled workshop-details">
                                                <li><i class="bi bi-calendar-check"></i> <strong>Date:</strong> Oct 25-26, 2025 (Sat & Sun)</li>
                                                <li><i class="bi bi-clock"></i> <strong>Time:</strong> 10:00 AM - 4:00 PM</li>
                                                <li><i class="bi bi-tag"></i> <strong>Price:</strong> RM 650</li>
                                                <li><i class="bi bi-geo-alt"></i> <strong>Venue:</strong> Root Flowers Studio, Kuching</li>
                                            </ul>
                                            <a href="workshop_reg.php?title=Hand-tied+Bouquet+Course" class="btn btn-primary w-100 mt-3">Register for this Course</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFlorist1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFlorist1" aria-expanded="false" aria-controls="collapseFlorist1">
                                    <i class="bi bi-award me-3"></i>
                                    Florist To Be 1
                                </button>
                            </h2>
                            <div id="collapseFlorist1" class="accordion-collapse collapse" aria-labelledby="headingFlorist1" data-bs-parent="#workshopAccordion">
                                <div class="accordion-body">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-5">
                                            <img src="./images/workshops/class2.jpg" class="img-fluid rounded shadow-sm" alt="Florist To Be 1 Flyer">
                                        </div>
                                        <div class="col-md-7">
                                            <p>Your first step to becoming a professional. This foundational module covers essential floristry skills and a broad range of classic designs.</p>
                                            <h6 class="text-muted">Details:</h6>
                                            <ul class="list-unstyled workshop-details">
                                                <li><i class="bi bi-calendar-check"></i> <strong>Next Intake:</strong> November 2025 (4 Days)</li>
                                                <li><i class="bi bi-clock"></i> <strong>Time:</strong> 10:00 AM - 4:00 PM</li>
                                                <li><i class="bi bi-tag"></i> <strong>Price:</strong> RM 1,500</li>
                                                <li><i class="bi bi-geo-alt"></i> <strong>Venue:</strong> Root Flowers Studio, Kuching</li>
                                            </ul>
                                            <a href="workshop_reg.php?title=Florist+To+Be+1+(Foundational)" class="btn btn-primary w-100 mt-3">Register for this Course</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFlorist2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFlorist2" aria-expanded="false" aria-controls="collapseFlorist2">
                                    <i class="bi bi-journal-check me-3"></i>
                                    Florist To Be 2
                                </button>
                            </h2>
                            <div id="collapseFlorist2" class="accordion-collapse collapse" aria-labelledby="headingFlorist2" data-bs-parent="#workshopAccordion">
                                <div class="accordion-body">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-md-5">
                                            <img src="./images/workshops/class3.jpg" class="img-fluid rounded shadow-sm" alt="Florist To Be 2 Flyer">
                                        </div>
                                        <div class="col-md-7">
                                            <p>Take your skills to the next level. This advanced module explores artistic designs, specialized techniques, and larger, more complex arrangements.</p>
                                            <h6 class="text-muted">Details:</h6>
                                            <ul class="list-unstyled workshop-details">
                                                <li><i class="bi bi-calendar-check"></i> <strong>Next Intake:</strong> December 2025 (4 Days)</li>
                                                <li><i class="bi bi-clock"></i> <strong>Time:</strong> 10:00 AM - 4:00 PM</li>
                                                <li><i class="bi bi-tag"></i> <strong>Price:</strong> RM 1,800</li>
                                                <li><i class="bi bi-geo-alt"></i> <strong>Venue:</strong> Root Flowers Studio, Kuching</li>
                                            </ul>
                                            <a href="workshop_reg.php?title=Florist+To+Be+2+(Advanced)" class="btn btn-primary w-100 mt-3">Register for this Course</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>

        <?php include 'includes/footer.inc'; ?>
      
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>