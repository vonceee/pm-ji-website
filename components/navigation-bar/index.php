<?php
// check for session variable $_SESSION['user_email']
$isLoggedIn = !empty($_SESSION['user_email']);

$homeLink = $isLoggedIn
    ? "/NEW-PM-JI-RESERVIFY/pages/customer/home.php"
    : "/NEW-PM-JI-RESERVIFY/public/index.php";
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <link rel="stylesheet" href="/NEW-PM-JI-RESERVIFY/components/navigation-bar/navigation-bar.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<!-- Single Navigation Header -->
<header>
    <nav class="unified-navbar">
        <!-- Left Section: Logo + Company Name -->
        <div class="navbar-left">
            <img src="/NEW-PM-JI-RESERVIFY/assets/logo/PM&JI-logo.png" alt="PM&JI Reservify" class="company-logo" />
            <span class="company-name">PM&JI Reservify</span>
        </div>

        <!-- Center Section: Social Icons -->
        <div class="navbar-center">
            <a href="https://www.facebook.com/pmandjipictures" target="_blank" rel="noopener noreferrer"
                class="social-icon" title="Follow us on Facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="mailto:photoapp@example.com" class="social-icon" title="Send us an email">
                <i class="fas fa-envelope"></i>
            </a>
        </div>

        <!-- Right Section: Navigation + User Actions -->
        <div class="navbar-right">
            <!-- Main Navigation -->
            <ul class="main-navigation">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $homeLink; ?>">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Services
                    </a>
                    <div class="dropdown-menu" aria-labelledby="servicesDropdown">
                        <a class="dropdown-item service-link" href="#baptism-card">
                            <i class="fas fa-baby"></i> Baptism
                        </a>
                        <a class="dropdown-item service-link" href="#birthday-card">
                            <i class="fas fa-birthday-cake"></i> Birthday
                        </a>
                        <a class="dropdown-item service-link" href="#company-card">
                            <i class="fas fa-building"></i> Company Event
                        </a>
                        <a class="dropdown-item service-link" href="#reunion-card">
                            <i class="fas fa-users"></i> Reunion
                        </a>
                        <a class="dropdown-item service-link" href="#wedding-card">
                            <i class="fas fa-heart"></i> Wedding
                        </a>
                    </div>
                </li>
                                <li class="nav-item">
                    <a class="nav-link" href="#about-section">About</a>
                </li>
            </ul>

            <!-- User Actions -->
            <div class="user-actions">
                <?php if ($isLoggedIn): ?>
                    <!-- My Bookings Link -->
                    <a href="/NEW-PM-JI-RESERVIFY/pages/customer/views/dashboard.php" class="bookings-link"
                        title="My Bookings">
                        <i class="fas fa-calendar-check"></i>
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="dropdown profile-dropdown">
                        <a href="#" class="profile-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            title="My Profile">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="/NEW-PM-JI-RESERVIFY/pages/customer/profile/profile.php">
                                <i class="fas fa-user-circle"></i> Profile
                            </a>
                            <a class="dropdown-item" href="inbox.php">
                                <i class="fas fa-inbox"></i> Inbox
                            </a>
                            <a class="dropdown-item" href="preference.php">
                                <i class="fas fa-cog"></i> Preferences
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/NEW-PM-JI-RESERVIFY/pages/customer/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="#" class="login-register" data-toggle="modal" data-target="#loginModal"
                        title="Login to your account">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
<!-- End Enhanced Header -->

<!-- Login Modal -->
<div class="modal login-modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel"
    aria-hidden="true">
    <div class="modal-dialog login-modal-dialog" role="document">
        <div class="modal-content login-modal-content">
            <div class="modal-header login-modal-header">
                <h5 class="modal-title login-modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body login-modal-body">
                <form id="loginForm" action="/NEW-PM-JI-RESERVIFY/pages/customer/login.php" method="POST">
                    <label>Email</label>
                    <div class="input-box">
                        <input type="email" name="Email" placeholder="Email" id="username" required>
                        <i class='bx bxs-envelope'></i>
                    </div>
                    <label>Password</label>
                    <div class="input-box password-box">
                        <input type="password" name="Password" placeholder="Password" id="password" required>
                        <i class="toggle-password fas fa-eye"></i>
                    </div>
                    <button type="submit" class="btn-login btn-primary">Login</button>
                    <div id="loginError" class="error-message"></div>
                    <div class="register-link">
                        <p>Don't have an account? <a href="#" data-dismiss="modal" data-toggle="modal"
                                data-target="#signupModal">Sign Up</a>
                            <br>
                            <a href="/NEW-PM-JI-RESERVIFY/recover-account.php" class="forgot-password">Forgot
                                Password?</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Login Modal -->

<!-- Sign Up Modal -->
<div class="modal login-modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="signupModalLabel">
    <div class="modal-dialog login-modal-dialog" role="document">
        <div class="modal-content login-modal-content">
            <div class="modal-header login-modal-header">
                <h5 class="modal-title login-modal-title" id="signupModalLabel">Sign Up</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body login-modal-body">
                <form id="signupForm" action="/NEW-PM-JI-RESERVIFY/pages/customer/signup/signup.php" method="POST">
                    <!-- inline error container for overall messages (optional) -->
                    <div id="signupError" class="error-message" style="color: red;"></div>

                    <div class="input-box">
                        <input type="text" name="firstName" placeholder="First Name" required pattern="^[A-Za-z ]+$"
                            title="Invalid Characters Detected. Only letters and spaces allowed.">
                        <div class="field-error" id="firstNameError"></div>
                    </div>
                    <div class="input-box">
                        <input type="text" name="middleName" placeholder="Middle Name" pattern="^[A-Za-z]*$"
                            title="Invalid Characters Detected. Only letters allowed.">
                    </div>
                    <div class="input-box">
                        <input type="text" name="lastName" placeholder="Last Name" required pattern="^[A-Za-z]+$"
                            title="Invalid Characters Detected. Only letters allowed.">
                        <div class="field-error" id="lastNameError"></div>
                    </div>
                    <div class="input-box">
                        <input type="email" name="email" placeholder="Email" required>
                        <i class='bx bxs-envelope'></i>
                        <div class="field-error" id="emailError"></div>
                    </div>
                    <!-- New Contact Number Field -->
                    <div class="input-box">
                        <input type="tel" name="contact" placeholder="Contact No." required pattern="^\d{10,15}$"
                            title="Enter a valid contact number with 10 to 15 digits">
                        <div class="field-error" id="contactError"></div>
                    </div>
                    <div class="input-box password-box">
                        <input type="password" name="Password" placeholder="Password" required minlength="8"
                            pattern=".{8,}" title="Password must be at least 8 characters long">
                        <i class="toggle-password fas fa-eye"></i>
                        <div class="field-error" id="passwordError"></div>
                    </div>
                    <div class="input-box password-box">
                        <input type="password" name="confirmPassword" placeholder="Confirm Password" required
                            minlength="8" pattern=".{8,}" title="Password must be at least 8 characters long">
                        <i class="toggle-password fas fa-eye"></i>
                        <div class="field-error" id="confirmPasswordError"></div>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the
                            <a href="/NEW-PM-JI-RESERVIFY/components/terms-and-conditions/index.php" target="_blank">
                                Terms &amp; Conditions
                            </a>
                        </label>
                        <div class="field-error" id="termsError"></div>
                    </div>
                    <button type="submit" class="btn-login btn-primary">Sign Up</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Sign Up Modal -->

<!-- Login Modal Script -->
<script>
    $(document).ready(function () {
        $('#loginForm').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: '/NEW-PM-JI-RESERVIFY/pages/customer/process_login.php',
                data: $(this).serialize(),

                // start the loading bar just before sending
                beforeSend: function () {
                    NProgress.start();
                },

                success: function (response) {
                    response = response.trim();
                    if (response === 'success') {
                        window.location.href = '/NEW-PM-JI-RESERVIFY/pages/customer/home.php';
                    } else if (response === 'unverified') {
                        $('#loginError').text('Your account is not verified yet. Please check your email.');
                    } else {
                        $('#loginError').text('invalid email or password.');
                    }
                },

                error: function () {
                    $('#loginError').text('An error occurred. Please try again.');
                },

                // finish the loading bar when the request is done
                complete: function () {
                    NProgress.done();
                }
            });
        });
    });
</script>
<!-- End Login Modal Script -->

<!-- Toggle Visibility of Password -->
<script>
    $(document).on('click', '.toggle-password', function () {
        var input = $(this).siblings('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

</script>
<!-- End Toggle Visibility of Password -->

<!-- Sign Up Modal Script -->
<script>
    document.getElementById("signupForm").addEventListener("submit", function (event) {
        event.preventDefault();

        // clear error messages
        document.getElementById("signupError").innerHTML = "";
        document.getElementById("firstNameError").innerHTML = "";
        document.getElementById("lastNameError").innerHTML = "";
        document.getElementById("emailError").innerHTML = "";
        document.getElementById("passwordError").innerHTML = "";
        document.getElementById("confirmPasswordError").innerHTML = "";
        document.getElementById("termsError").innerHTML = "";

        // form values
        var firstName = document.querySelector('input[name="firstName"]').value.trim();
        var lastName = document.querySelector('input[name="lastName"]').value.trim();
        var email = document.querySelector('#signupForm input[name="email"]').value.trim();
        var password = document.querySelector('#signupForm input[name="Password"]').value.trim();
        var confirmPassword = document.querySelector('#signupForm input[name="confirmPassword"]').value.trim();
        var termsAccepted = document.getElementById("terms").checked;

        var valid = true;

        // client-side validation
        if (!firstName) {
            document.getElementById("firstNameError").innerHTML = "First name is required.";
            valid = false;
        }

        if (!lastName) {
            document.getElementById("lastNameError").innerHTML = "Last name is required.";
            valid = false;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            document.getElementById("emailError").innerHTML = "Email is required.";
            valid = false;
        } else if (!emailRegex.test(email)) {
            document.getElementById("emailError").innerHTML = "Please enter a valid email.";
            valid = false;
        }

        if (!password) {
            document.getElementById("passwordError").innerHTML = "Password is required.";
            valid = false;
        }

        if (!confirmPassword) {
            document.getElementById("confirmPasswordError").innerHTML = "Confirm your password.";
            valid = false;
        } else if (password !== confirmPassword) {
            document.getElementById("confirmPasswordError").innerHTML = "Passwords do not match.";
            valid = false;
        }

        if (!termsAccepted) {
            document.getElementById("termsError").innerHTML = "You must agree to the Terms & Conditions.";
            valid = false;
        }

        // check duplicate email only if basic validation passed
        if (valid) {
            fetch('/NEW-PM-JI-RESERVIFYpages/customer/signup/check_email.php?email=' + encodeURIComponent(email))
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'exists') {
                        document.getElementById("emailError").innerHTML = "This email is already registered.";
                    } else if (data.status === 'available') {
                        // no errors, now submit the form manually
                        document.getElementById("signupForm").submit();
                    } else {
                        document.getElementById("signupError").innerHTML = data.message || "Something went wrong.";
                    }
                })
                .catch(error => {
                    console.error("Error checking email:", error);
                    document.getElementById("signupError").innerHTML = "Could not verify email. Try again.";
                });
        }
    });
</script>
<!-- End Sign Up Modal Script -->

<script src="/NEW-PM-JI-RESERVIFY/components/navigation-bar/navigation-bar.js"></script>