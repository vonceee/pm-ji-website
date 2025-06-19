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
                <!-- inline error container for overall messages -->
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
                <div class="input-box">
                    <input type="tel" name="contact" placeholder="Contact No." required pattern="^\d{10,15}$"
                        title="Enter a valid contact number with 10 to 15 digits">
                    <div class="field-error" id="contactError"></div>
                </div>
                <div class="input-box password-box">
                    <input type="password" name="Password" placeholder="Password" required minlength="8" pattern=".{8,}"
                        title="Password must be at least 8 characters long">
                    <i class="toggle-password fas fa-eye"></i>
                    <div class="field-error" id="passwordError"></div>
                </div>
                <div class="input-box password-box">
                    <input type="password" name="confirmPassword" placeholder="Confirm Password" required minlength="8"
                        pattern=".{8,}" title="Password must be at least 8 characters long">
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