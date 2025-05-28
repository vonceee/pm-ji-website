<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Service Cards</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/NEW-PM-JI-RESERVIFY/styles/color-theme.css" rel="stylesheet">
    <link href="/NEW-PM-JI-RESERVIFY/public/components/service-cards/index.css" rel="stylesheet">
</head>

<body>
    <div class="animated-bg"></div>

    <div class="divider-section">
        <div class="wave-divider"></div>
    </div>

    <section class="service-container">
        <div class="section-header fade-in">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">professional photography services tailored to capture your most precious moments
                with style and elegance</p>
        </div>

        <div class="inclusions-card fade-in stagger-1">
            <h3 class="inclusions-title">
                <i class="fas fa-star"></i>
                All Packages Include
            </h3>
            <ul class="inclusions-list">
                <li><span>🖼️</span> Personalized Photo Layout Design</li>
                <li><span>💎</span> High-Quality 4-Frame Prints</li>
                <li><span>🎨</span> Custom Event-Themed Layouts</li>
                <li><span>☁️</span> Digital Copies via Google Drive</li>
            </ul>
        </div>

        <div class="cards-grid">
            <!-- Baptism Card -->
            <div class="service-card fade-in stagger-2" data-service="baptism">
                <button class="info-btn" data-service="baptism">
                    <i class="fas fa-info"></i>
                </button>
                <div class="service-icon">
                    <i class="fas fa-church"></i>
                </div>
                <h3 class="card-title">Baptism</h3>
                <p class="card-description">Capture sacred moments with professional coverage designed for this blessed
                    occasion.</p>
                <div class="pricing-section">
                    <ul class="pricing-list">
                        <li class="pricing-item">
                            <span class="pricing-duration">3 Hours</span>
                            <div>
                                <span class="pricing-amount">₱4,500</span>
                                <span class="pricing-down">(50% down: ₱2,250)</span>
                            </div>
                        </li>
                        <li class="pricing-item">
                            <span class="pricing-duration">4 Hours</span>
                            <div>
                                <span class="pricing-amount">₱4,600</span>
                                <span class="pricing-down">(50% down: ₱2,300)</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="service-tooltip" id="tooltip-baptism">
                    <div class="tooltip-title">
                        <i class="fas fa-church"></i>
                        Baptism Inclusions
                    </div>
                    <ul class="tooltip-list">
                        <li>Unlimited Photo Sessions</li>
                        <li>Baptism-Themed Photo Layout</li>
                        <li>Church-Inspired Backdrops</li>
                        <li>Baby-Friendly Props</li>
                        <li>Angel Wings, Halos, Crosses, and more</li>
                        <li>Digital Copy via Google Drive</li>
                        <li>1 Printed Copy</li>
                    </ul>
                </div>
            </div>

            <!-- Birthday Card -->
            <div class="service-card fade-in stagger-3" data-service="birthday">
                <button class="info-btn" data-service="birthday">
                    <i class="fas fa-info"></i>
                </button>
                <div class="service-icon">
                    <i class="fas fa-birthday-cake"></i>
                </div>
                <h3 class="card-title">Birthday</h3>
                <p class="card-description">Celebrate in style with lively and creative coverage that brings the party
                    to life.</p>
                <div class="pricing-section">
                    <ul class="pricing-list">
                        <li class="pricing-item">
                            <span class="pricing-duration">3 Hours</span>
                            <div>
                                <span class="pricing-amount">₱4,000</span>
                                <span class="pricing-down">(50% down: ₱2,000)</span>
                            </div>
                        </li>
                        <li class="pricing-item">
                            <span class="pricing-duration">4 Hours</span>
                            <div>
                                <span class="pricing-amount">₱4,500</span>
                                <span class="pricing-down">(50% down: ₱2,750)</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="service-tooltip" id="tooltip-birthday">
                    <div class="tooltip-title">
                        <i class="fas fa-birthday-cake"></i>
                        Birthday Inclusions
                    </div>
                    <ul class="tooltip-list">
                        <li>Unlimited Photo Sessions</li>
                        <li>Personalized Birthday-Themed Layout</li>
                        <li>Fun Birthday Props and Decorations</li>
                        <li>Printed Photo Strips 2x6 Format</li>
                        <li>Polaroid Style Design Options</li>
                        <li>Photo Standee (4 shots)</li>
                        <li>3 Refrigerator Magnets (Single Shot)</li>
                    </ul>
                </div>
            </div>

            <!-- Company Event Card -->
            <div class="service-card fade-in stagger-4" data-service="company">
                <button class="info-btn" data-service="company">
                    <i class="fas fa-info"></i>
                </button>
                <div class="service-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3 class="card-title">Company Event</h3>
                <p class="card-description">Professional coverage for your corporate gatherings with branded excellence.
                </p>
                <div class="pricing-section">
                    <ul class="pricing-list">
                        <li class="pricing-item">
                            <span class="pricing-duration">3 Hours</span>
                            <div>
                                <span class="pricing-amount">₱7,000</span>
                                <span class="pricing-down">(50% down: ₱3,500)</span>
                            </div>
                        </li>
                        <li class="pricing-item">
                            <span class="pricing-duration">4 Hours</span>
                            <div>
                                <span class="pricing-amount">₱8,000</span>
                                <span class="pricing-down">(50% down: ₱4,000)</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="service-tooltip" id="tooltip-company">
                    <div class="tooltip-title">
                        <i class="fas fa-briefcase"></i>
                        Company Event Inclusions
                    </div>
                    <ul class="tooltip-list">
                        <li>Company-Branded Photo Layouts</li>
                        <li>Professional Lighting Setup</li>
                        <li>Corporate Backdrop Integration</li>
                        <li>Free Digital Copies via Google Drive</li>
                        <li>Corporate-Appropriate Props</li>
                        <li>On-site Assistant/Operator</li>
                        <li>Branding & Sponsor Logo Options</li>
                        <li>Data Collection Features Available</li>
                    </ul>
                </div>
            </div>

            <!-- Reunion Card -->
            <div class="service-card fade-in stagger-5" data-service="reunion">
                <button class="info-btn" data-service="reunion">
                    <i class="fas fa-info"></i>
                </button>
                <div class="service-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="card-title">Reunion</h3>
                <p class="card-description">Relive old memories and create new ones with comprehensive family coverage.
                </p>
                <div class="pricing-section">
                    <ul class="pricing-list">
                        <li class="pricing-item">
                            <span class="pricing-duration">3 Hours</span>
                            <div>
                                <span class="pricing-amount">₱5,000</span>
                                <span class="pricing-down">(50% down: ₱2,500)</span>
                            </div>
                        </li>
                        <li class="pricing-item">
                            <span class="pricing-duration">4 Hours</span>
                            <div>
                                <span class="pricing-amount">₱6,500</span>
                                <span class="pricing-down">(50% down: ₱3,250)</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="service-tooltip" id="tooltip-reunion">
                    <div class="tooltip-title">
                        <i class="fas fa-users"></i>
                        Reunion Inclusions
                    </div>
                    <ul class="tooltip-list">
                        <li>Unlimited Family/Group Photos</li>
                        <li>Customized Batch/Family Layouts</li>
                        <li>Props Suitable for All Ages</li>
                        <li>Classic & Themed Backdrops</li>
                        <li>Free Digital Copy via Google Drive</li>
                        <li>Printed Photo Strips & Standees</li>
                        <li>Refrigerator Magnets</li>
                    </ul>
                </div>
            </div>

            <!-- Wedding Card -->
            <div class="service-card fade-in stagger-6" data-service="wedding">
                <button class="info-btn" data-service="wedding">
                    <i class="fas fa-info"></i>
                </button>
                <div class="service-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="card-title">Wedding</h3>
                <p class="card-description">Timeless coverage of your special day with elegance and romantic
                    sophistication.</p>
                <div class="pricing-section">
                    <ul class="pricing-list">
                        <li class="pricing-item">
                            <span class="pricing-duration">3 Hours</span>
                            <div>
                                <span class="pricing-amount">₱7,500</span>
                                <span class="pricing-down">(50% down: ₱3,750)</span>
                            </div>
                        </li>
                        <li class="pricing-item">
                            <span class="pricing-duration">4 Hours</span>
                            <div>
                                <span class="pricing-amount">₱11,000</span>
                                <span class="pricing-down">(50% down: ₱5,500)</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="service-tooltip" id="tooltip-wedding">
                    <div class="tooltip-title">
                        <i class="fas fa-heart"></i>
                        Wedding Inclusions
                    </div>
                    <ul class="tooltip-list">
                        <li>Romantic Layouts w/Couple's Names</li>
                        <li>Floral, White Backdrop Options</li>
                        <li>Premium Wedding Props</li>
                        <li>Printed Strips & Polaroid Designs</li>
                        <li>Free Digital Copies via Google Drive</li>
                        <li>Extended Guest Coverage Time</li>
                        <li>Optional Live Slideshow Display</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <script src="/NEW-PM-JI-RESERVIFY/public/components/service-cards/index.js"></script>
</body>

</html>