import { authenticateLogin } from "../modules/auth.js";
import { showFeedbackMessage } from "../modules/feedback.js";

// Execute when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {    
    document.getElementById('login-form').addEventListener('submit', login);
});

/**
 * Handles the login form submission.
 * @param {Event} event - The form submit event.
 */
async function login(event) {
    event.preventDefault(); // Prevent default form submission

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        // Attempt to authenticate the user
        const loginSuccessful = await authenticateLogin(email, password);

        if (loginSuccessful) {
            // Redirect to the home page on success
            window.location.pathname = window.location.pathname.replace("/login", "/home");
        } else {
            // Show feedback message for incorrect credentials
            showFeedbackMessage('Invalid email or password', 'danger');
        }
    } catch (error) {
        // Show feedback message for an error
        showFeedbackMessage(error.message || 'Internal server error', 'danger');
    }
}
