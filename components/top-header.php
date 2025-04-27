<!-- Header -->
<header>
    <div class="top-header">
        <div class="top-header-left">
            <img src="/NEW-PM-JI-RESERVIFY/assets/logo/PM&JI-logo.png" alt="PM&JI Reservify" class="company-logo" />
            <span class="company-name">PM&JI Reservify</span>
        </div>
        <div class="top-header-right">
            <a href="#" class="login-register" data-toggle="modal" data-target="#loginModal">Login</a>
        </div>

    </div>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <!-- Social Icons on Left -->
            <div class="navbar-social">
                <a href="https://www.facebook.com/pmandjipictures" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-facebook-f social-icon"></i>
                </a>
                <a href="mailto:photoapp@example.com">
                    <i class="fas fa-envelope social-icon"></i>
                </a>

            </div>

            <!-- Navigation Links on Right -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/NEW-PM-JI-RESERVIFY/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/NEW-PM-JI-RESERVIFY/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/NEW-PM-JI-RESERVIFY/index.php#services-section">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/NEW-PM-JI-RESERVIFY/index.php#footer-section">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<!-- End Header -->

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content login-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="loginForm" action="/NEW-PM-JI-RESERVIFY/pages/customer/login.php" method="POST">
                    <div class="input-box">
                        <input type="email" name="Email" placeholder="Email" id="username" required>
                        <i class='bx bxs-envelope'></i>
                    </div>
                    <div class="input-box password-box">
                        <input type="password" name="Password" placeholder="Password" id="password" required>
                        <i class='bx bxs-lock-alt'></i>
                        <i class="toggle-password fas fa-eye"></i>
                    </div>
                    <button type="submit" class="btn-login">Login</button>
                    <div id="loginError" class="error-message"></div>
                    <div class="register-link">
                        <p>Don't have an account? <a href="#" data-dismiss="modal" data-toggle="modal"
                                data-target="#signupModal">Sign Up</a>

                            <br>
                            <a href="recover-account.php" class="forgot-password">Forgot Password?</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Login Modal -->

<!-- Sign Up Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="signupModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content login-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signupModalLabel">Sign Up</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
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
                        <i class='bx bxs-lock-alt'></i>
                        <i class="toggle-password fas fa-eye"></i>
                        <div class="field-error" id="passwordError"></div>
                    </div>
                    <div class="input-box password-box">
                        <input type="password" name="confirmPassword" placeholder="Confirm Password" required
                            minlength="8" pattern=".{8,}" title="Password must be at least 8 characters long">
                        <i class='bx bxs-lock-alt'></i>
                        <i class="toggle-password fas fa-eye"></i>
                        <div class="field-error" id="confirmPasswordError"></div>
                    </div>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the
                            <a href="/NEW-PM-JI-RESERVIFY/terms-and-condition.php" target="_blank">
                                Terms &amp; Conditions
                            </a>
                        </label>
                        <div class="field-error" id="termsError"></div>
                    </div>
                    <button type="submit" class="btn-login">Sign Up</button>
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
                url: '/NEW-PM-JI-RESERVIFY/pages/customer/process_login.php', // URL of login script
                data: $(this).serialize(),
                success: function (response) {
                    if (response.trim() === 'success') {
                        // redirect to the home page successful login
                        window.location.href = '/NEW-PM-JI-RESERVIFY/pages/customer/home.php';
                    } else if (response.trim() === 'unverified') {
                        // handle unverified account
                        $('#loginError').text('Your account is not verified yet. Please check your email.');
                    } else {
                        // handle invalid login
                        $('#loginError').text('Invalid email or password.');
                    }
                },
                error: function () {
                    // handle AJAX errors
                    $('#loginError').text('An error occurred. Please try again.');
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
            fetch('pages/customer/signup/check_email.php?email=' + encodeURIComponent(email))
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