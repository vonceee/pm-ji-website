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