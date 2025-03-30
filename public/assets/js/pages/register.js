import { registerUser } from "../modules/auth.js";
import { showFeedbackMessage } from "../modules/feedback.js";

// Execute when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {    
    // Update the file name display when a user selects a file for profile picture
    document.getElementById('profile-picture').addEventListener('change', function() {
        const fileName = this.files[0]?.name || 'No file selected';  // Get the selected file name or default message
        document.getElementById('file-name-display').textContent = fileName;  // Update the display with file name
    });

    // Listen for form submission to handle registration
    document.getElementById('registration-form').addEventListener('submit', handleRegistration);
});

/**
 * Handles the user registration form submission.
 * @param {Event} event - The form submit event.
 */
async function handleRegistration(event) {
    event.preventDefault();  // Prevent the default form submission behavior
    
    try {
        // Create a FormData object from the registration form
        const formData = new FormData(document.getElementById('registration-form'));
        
        // Attempt to register the user by sending form data
        const result = await registerUser(formData);
        
        if (result && result['success']) {
            // If registration is successful, redirect to the home page
            window.location.pathname = window.location.pathname.replace("/register", "/home");
        } else {
            // If registration fails, show an error message
            showFeedbackMessage(result['message'], 'danger');
        }
    } catch (error) {
        // Log and show an error message if the registration process fails
        console.error('Error during registration:', error);
        showFeedbackMessage(error.message || 'Internal server error', 'danger');
    }
};
