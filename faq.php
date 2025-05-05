<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | PM&JI Reservify</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/styles/about.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/top-header.css">
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/top-header.php'; ?>

    <!-- Main Content Section -->
    <main class="container" style="margin-top: 120px; margin-bottom: 120px;">
        <div class="row">
            <!-- Sidebar / Side Panel Column -->
            <aside class="col-md-2">
                <div class="side-panel">
                    <ul>
                        <li><a href="/NEW-PM-JI-RESERVIFY/about.php">About PM&JI</a></li>
                        <li><a href="/NEW-PM-JI-RESERVIFY/faq.php" class="active">FAQ</a></li>
                        <li><a href="/NEW-PM-JI-RESERVIFY/terms-and-condition.php">Terms &amp; Conditions</a></li>
                    </ul>
                </div>
            </aside>

            <!-- Main Content Column -->
            <section class="col-md-9">
                <div class="card p-4">
                    <section id="faq-section" class="mb-4">
                        <h1>Frequently Asked Questions</h1>
                        <hr class="custom-hr">
                        <div id="faqAccordion">
                            <!-- FAQ Items -->
                            <div class="card">
                                <div class="card-header" id="faqHeading1">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link" data-toggle="collapse" data-target="#faq1" aria-expanded="true" aria-controls="faq1">
                                            What are your pricing/packages?
                                        </button>
                                    </h5>
                                </div>
                                <div id="faq1" class="collapse show" aria-labelledby="faqHeading1" data-parent="#faqAccordion">
                                    <div class="card-body">
                                        We offer a variety of packages tailored to different events and budgets. You can view our pricing and package details on the <a href="/NEW-PM-JI-RESERVIFY/packages.php">Packages</a> page.
                                    </div>
                                </div>
                            </div>
                            <!-- Add more FAQ items here -->
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </main>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/components/footer.html'; ?>

</body>

</html>