import { registerUser } from '../modules/auth.js';

document.getElementById('profile-picture').addEventListener('change', function() {
    const fileName = this.files[0]?.name || 'No file selected';
    document.getElementById('file-name-display').textContent = fileName;
});

document.getElementById('registration-form').addEventListener('submit', handleRegistration);

async function handleRegistration(event) {
    event.preventDefault();
    
    try {
        const formData = new FormData(document.getElementById('registration-form'));
        const result = await registerUser(formData);
        
        if (result && result['success']) {
            // Redirect to home page or dashboard
            window.location.pathname = window.location.pathname.replace("/register", "/home");
        } else {
            // Registration failed
            alert('Registration failed. ' + result['message']);
        }
    } catch (error) {
        console.error('Error during registration:', error);
        alert('An error occurred during registration. Please try again later.');
    }
};