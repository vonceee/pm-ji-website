<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Portfolio Section</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/public/components/portfolio/index.css">

</head>
<body>
    <div class="portfolio-section">
        <div class="container">
            <h2 class="section-title text-center">Sample Photo Templates</h2>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-btn active" data-filter="all">All Projects</button>
                <button class="filter-btn" data-filter="wedding">Weddings</button>
                <button class="filter-btn" data-filter="corporate">Corporate</button>
                <button class="filter-btn" data-filter="party">Parties</button>
                <button class="filter-btn" data-filter="reunion">Reunions</button>
            </div>

            <!-- Portfolio Grid -->
            <div class="portfolio-grid">
                <!-- Portfolio Item 1 -->
                <div class="portfolio-item" data-category="wedding" 
                     data-title="Wedding Event"
                     data-description="a beautiful wedding ceremony captured with elegance and artistic vision. Every moment from the intimate ceremony to the grand celebration was documented with precision and creativity."
                     data-date="May 2019">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work1.jpg" alt="Wedding Event">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Wedding Event</h3>
                        <p class="portfolio-date"><i class="fas fa-calendar"></i> May 2019</p>
                        <div class="portfolio-actions">
                            <a href="#" class="btn-portfolio view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Portfolio Item 2 -->
                <div class="portfolio-item" data-category="corporate"
                     data-title="Corporate Event"
                     data-description="professional coverage of a corporate gathering at Quezon City Sports Club. Captured networking moments, presentations, and team building activities with a focus on brand representation."
                     data-date="March 2019">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work2.jpg" alt="Corporate Event">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Corporate Event</h3>
                        <p class="portfolio-date"><i class="fas fa-calendar"></i> March 2019</p>
                        <div class="portfolio-actions">
                            <a href="#" class="btn-portfolio view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Portfolio Item 3 -->
                <div class="portfolio-item" data-category="party"
                     data-title="Birthday Party"
                     data-description="a lively birthday celebration with creative shots and candid moments. Focused on capturing the joy, laughter, and memorable interactions throughout the celebration."
                     data-date="March 2019">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work3.jpg" alt="Birthday Party">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Birthday Party</h3>
                        <p class="portfolio-date"><i class="fas fa-calendar"></i> March 2019</p>
                        <div class="portfolio-actions">
                            <a href="#" class="btn-portfolio view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Portfolio Item 4 -->
                <div class="portfolio-item" data-category="reunion"
                     data-title="Reunion Event"
                     data-description="reliving memories with a fun-filled reunion. Captured nostalgic moments, group photos, and the emotional connections that bind friends and family together."
                     data-date="May 2019">
                    <img src="/NEW-PM-JI-RESERVIFY/assets/portfolio/work4.jpg" alt="Reunion Event">
                    <div class="portfolio-overlay">
                        <h3 class="portfolio-title">Reunion Event</h3>
                        <p class="portfolio-date"><i class="fas fa-calendar"></i> May 2019</p>
                        <div class="portfolio-actions">
                            <a href="#" class="btn-portfolio view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="portfolioModal" tabindex="-1" aria-labelledby="portfolioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content portfolio-modal-content">
                <div class="modal-header portfolio-modal-header">
                    <h5 class="modal-title portfolio-modal-title" id="portfolioModalLabel"></h5>
                    <button type="button" class="btn-close portfolio-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="portfolioModalImage" src="" alt="Portfolio Image" class="img-fluid modal-image portfolio-modal-image">
                    <p id="portfolioModalDescription" class="modal-description portfolio-modal-description"></p>
                    <div class="modal-date">
                        <i class="fas fa-calendar"></i>
                        <span id="portfolioModalDate"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="/NEW-PM-JI-RESERVIFY/public/components/portfolio/index.js"></script>
</body>
</html>