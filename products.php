<?php 
    session_start();

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
        <title>Products</title>
        <meta name="author" content="Ivan">
        <meta name="keywords" content="Products">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
        <link href="./style/style.css" rel="stylesheet">
        <link rel="icon" href="./images/logo.svg">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    </head>
    <body class="product-page">
       <header>
            <?php include 'includes/navbar.inc'; ?>
       </header>
        <section class="page-header text-center text-white py-5" style="background: url('./images/hero.jpg') no-repeat center center/cover;">
            <div class="container hero-content">
                <h1 class="display-4 fw-bold">Our Products</h1>
                <p class="lead">Handcrafted bouquets and arrangements for every occasion.</p>
            </div>
        </section>
        <main class="container my-5">
            
            <div class="mb-5">
                <h2 class="section-title">Occasion Bouquets</h2>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/occasion_prod/product7.jpg" class="card-img-top" alt="Graduation Bear Bouquet">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Graduation Bouquet</h5>
                                <p class="card-text">A perfect tribute to their hard work! This charming bouquet features an adorable graduation bear to celebrate their achievement in style.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/occasion_prod/product10.jpg" class="card-img-top" alt="Birthday Balloon Bouquet">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Soap Roses Bouquet</h5>
                                <p class="card-text">Celebrate their special day with a gift that lasts. This stunning bouquet features intricately crafted soap roses and a personalized birthday balloon.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/occasion_prod/product9.jpg" class="card-img-top" alt="Snack Bouquet">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Snacks Bouquet</h5>
                                <p class="card-text">Surprise the foodie in your life! This fun and generous bouquet is overflowing with a delicious assortment of popular snacks.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="collapse" id="moreOccasions">
                    <div class="row g-4 mt-0">
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/occasion_prod/product12.jpg" class="card-img-top" alt="Chinese New Year Lantern Arrangement">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Lunar New Year Arrangement</h5>
                                    <p class="card-text">Usher in good fortune for the Lunar New Year. This vibrant arrangement is designed with auspicious colors and a classic red lantern.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/occasion_prod/product11.jpg" class="card-img-top" alt="Lucky Sunflower Bouquet">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Opening Flowers Stand</h5>
                                    <p class="card-text">Wish them great success with this impressive opening flower stand. Bright sunflowers and a lucky cat bring energy and good fortune.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/occasion_prod/product8.jpg" class="card-img-top" alt="Grand Opening Flower Basket">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Opening Flowers Basket</h5>
                                    <p class="card-text">A classic gesture of success. Congratulate a new venture with this elegant flower basket, designed to wish them luck and prosperity.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a class="btn btn-outline-primary rounded-pill view-more-btn" data-bs-toggle="collapse" href="#moreOccasions" role="button" aria-expanded="false" aria-controls="moreOccasions">
                        <span class="view-more-text">View More</span>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </a>
                </div>
            </div>

           <div class="mb-5">
                <h2 class="section-title">The Signature Collection</h2>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/signature_prod/product1.jpg" class="card-img-top" alt="Crimson Velvet Bouquet">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Crimson Velvet</h5>
                                <p class="card-text">A rich and passionate arrangement of deep red gerberas, roses, and textural accents, wrapped in rustic kraft paper.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/signature_prod/product2.jpg" class="card-img-top" alt="Red Daisy Delight Bouquet">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Red Daisy Delight</h5>
                                <p class="card-text">Vibrant red gerbera daisies are paired with cheerful white accent flowers to create a classic and heartwarming bouquet.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/signature_prod/product3.jpg" class="card-img-top" alt="Modern Elegance Bouquet">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Modern Elegance</h5>
                                <p class="card-text">A single, striking white calla lily stands out amongst deep red roses in this chic and contemporary bouquet wrapped in black.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="collapse" id="moreSignature">
                    <div class="row g-4 mt-0">
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/signature_prod/product4.jpg" class="card-img-top" alt="Sweetly Pink Bouquet">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Sweetly Pink</h5>
                                    <p class="card-text">A charming and sweet bouquet of soft pink roses, accented with playful pops of red and wrapped in beautiful floral paper.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/signature_prod/product5.jpg" class="card-img-top" alt="Pastel Dream Bouquet">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Pastel Dream</h5>
                                    <p class="card-text">A dreamy and artistic mix of gentle pastel blooms, featuring a soft purple rose and a delicate yellow gerbera.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/signature_prod/product6.jpg" class="card-img-top" alt="Sunshine Bouquet">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Sunshine Bouquet</h5>
                                    <p class="card-text">Bring a smile to their face with this radiant bouquet of cheerful sunflowers, a perfect symbol of happiness and adoration.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a class="btn btn-outline-primary rounded-pill view-more-btn" data-bs-toggle="collapse" href="#moreSignature" role="button" aria-expanded="false" aria-controls="moreOccasions">
                        <span class="view-more-text">View More</span>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </a>
                </div>
            </div>

            <div class="mb-5">
                <h2 class="section-title">Special</h2>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/special_prod/product13.jpg" class="card-img-top" alt="The Enchanted Rose">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">The Enchanted Rose</h5>
                                <p class="card-text">A single, real rose, perfectly preserved to last for years. Encased in a glass dome with warm fairy lights, this is a timeless gift of everlasting love.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/special_prod/product18.jpg" class="card-img-top" alt="Unicorn's Dream">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Unicorn's Dream</h5>
                                <p class="card-text">Capture a little magic with this whimsical dome. A beautifully preserved light blue rose accompanies a mystical unicorn amidst glowing fairy lights.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm product-card">
                            <img src="./images/products/special_prod/product17.jpg" class="card-img-top" alt="Woodland Serenity">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Woodland Serenity</h5>
                                <p class="card-text">A serene forest scene under glass. This arrangement features a deep red preserved rose and a peaceful sleeping deer nestled in rich foliage.</p>
                                <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                <a href="#" class="btn btn-primary disabled">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="collapse" id="moreSpecial">
                    <div class="row g-4 mt-0">
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/special_prod/product16.jpg" class="card-img-top" alt="Our Sweet Moment">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Our Sweet Moment</h5>
                                    <p class="card-text">Celebrate a special connection with this charming dome, featuring a lovely couple, festive balloons, and a garden of pastel preserved flowers.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/special_prod/product15.jpg" class="card-img-top" alt="A Love Song">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">A Love Song</h5>
                                    <p class="card-text">For the music lover in your life. A single, perfect red rose is accompanied by a miniature grand piano in this artistic and romantic keepsake.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 shadow-sm product-card">
                                <img src="./images/products/special_prod/product14.jpg" class="card-img-top" alt="Gentle Lullaby Music Box">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">Gentle Lullaby Music Box</h5>
                                    <p class="card-text">A sweet and gentle gift. This musical dome features a soft blue preserved rose and a tiny teddy bear, playing a soothing melody.</p>
                                    <p class="fw-bold fs-5 text-primary mt-auto pt-2">Price: N/A</p>
                                    <a href="#" class="btn btn-primary disabled">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a class="btn btn-outline-primary rounded-pill view-more-btn" data-bs-toggle="collapse" href="#moreSpecial" role="button" aria-expanded="false" aria-controls="moreOccasions">
                        <span class="view-more-text">View More</span>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </a>
                </div>
            </div>

        </main>

        <?php include 'includes/footer.inc'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
         <script>
            document.addEventListener('DOMContentLoaded', function () {
                const viewMoreButtons = document.querySelectorAll('.view-more-btn');

                viewMoreButtons.forEach(button => {
                    // Part 1: Prevent the page from jumping on click
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                    });

                    // Part 2: Update text and icon when content is shown or hidden
                    const collapseTarget = document.querySelector(button.getAttribute('href'));
                    const buttonText = button.querySelector('.view-more-text');
                    const buttonIcon = button.querySelector('i');
                    const categorySection = button.closest('.mb-5'); // Get the parent category section

                    if (collapseTarget) {
                        // When the collapsible section begins to show
                        collapseTarget.addEventListener('show.bs.collapse', function () {
                            buttonText.textContent = 'View Less';
                            buttonIcon.classList.remove('bi-chevron-down');
                            buttonIcon.classList.add('bi-chevron-up');
                        });

                        // When the collapsible section begins to hide
                        collapseTarget.addEventListener('hide.bs.collapse', function () {
                            buttonText.textContent = 'View More';
                            buttonIcon.classList.remove('bi-chevron-up');
                            buttonIcon.classList.add('bi-chevron-down');
                        });

                        // Part 3: After the section is hidden, scroll back smoothly
                        collapseTarget.addEventListener('hidden.bs.collapse', function () {
                            if (categorySection) {
                                categorySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        });
                    }
                });
            });
        </script>
        </body>
</html>