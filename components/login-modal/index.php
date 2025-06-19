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