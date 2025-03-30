/**
 * Authenticate a user by sending login credentials to the API
 * @param {string} email - User's email address
 * @param {string} password - User's password
 * @returns {Promise<boolean>} - Success status of authentication
 */
export async function authenticateLogin(email, password) {
    try {
        // Send a POST request to the login API endpoint
        const response = await fetch('/socialMedia/public/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json' // Specify JSON format for request body
            },
            body: JSON.stringify({ 
                "email": email,
                "password": password
            })
        });
        
        // Parse the JSON response from the server
        const data = await response.json();
        console.log(data); // Log response data for debugging
        
        return data["success"]; // Return success status from response
    } catch (error) {
        console.error('Error:', error); // Log error details to the console
        throw error;
    }
}

/**
 * Register a new user by sending form data to the API
 * @param {FormData} formData - User registration data
 * @returns {Promise<Object|boolean>} - Response data or false in case of an error
 */
export async function registerUser(formData) {
    try {
        console.log(formData); // Log form data for debugging
        
        // Send a POST request to the registration API endpoint
        const response = await fetch('/socialMedia/public/api/auth/register', {
            method: 'POST',
            body: formData // Browser will automatically set the correct Content-Type
        });
        
        // Parse the JSON response from the server
        const data = await response.json();
        console.log(data); // Log response data for debugging
        
        return data; // Return the full response data
    } catch (error) {
        console.error('Error:', error); // Log error details to the console
        throw error;
    }
}
