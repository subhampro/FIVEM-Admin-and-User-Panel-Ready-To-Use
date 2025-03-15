/**
 * Main website JavaScript functionality
 */

$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Toggle password visibility in login/registration forms
    $('.toggle-password').on('click', function() {
        const passwordField = $($(this).data('toggle'));
        const passwordFieldType = passwordField.attr('type');
        
        if (passwordFieldType === 'password') {
            passwordField.attr('type', 'text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Registration form validation
    if ($('#registrationForm').length) {
        $('#registrationForm').submit(function(e) {
            let hasError = false;
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').hide();
            
            // Username validation
            if ($('#username').val().trim() === '') {
                $('#username').addClass('is-invalid');
                $('#usernameFeedback').show();
                hasError = true;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test($('#email').val())) {
                $('#email').addClass('is-invalid');
                $('#emailFeedback').show();
                hasError = true;
            }
            
            // Password validation
            if ($('#password').val().length < 8) {
                $('#password').addClass('is-invalid');
                $('#passwordFeedback').show();
                hasError = true;
            }
            
            // Password confirmation
            if ($('#password').val() !== $('#confirmPassword').val()) {
                $('#confirmPassword').addClass('is-invalid');
                $('#confirmPasswordFeedback').show();
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
            }
        });
    }
    
    // User profile form validation
    if ($('#profileForm').length) {
        $('#profileForm').submit(function(e) {
            let hasError = false;
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').hide();
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test($('#email').val())) {
                $('#email').addClass('is-invalid');
                $('#emailFeedback').show();
                hasError = true;
            }
            
            // New password validation (if provided)
            if ($('#newPassword').val() !== '') {
                if ($('#newPassword').val().length < 8) {
                    $('#newPassword').addClass('is-invalid');
                    $('#newPasswordFeedback').show();
                    hasError = true;
                }
                
                if ($('#newPassword').val() !== $('#confirmNewPassword').val()) {
                    $('#confirmNewPassword').addClass('is-invalid');
                    $('#confirmNewPasswordFeedback').show();
                    hasError = true;
                }
            }
            
            if (hasError) {
                e.preventDefault();
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    $('.alert-dismissible').delay(5000).fadeOut(500);
}); 