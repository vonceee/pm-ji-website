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
        fetch('/NEW-PM-JI-RESERVIFY/pages/customer/signup/check-email.php?email=' + encodeURIComponent(email))
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