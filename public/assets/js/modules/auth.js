export async function authenticateLogin(email, password) {

    try {
        const response = await fetch('/socialMedia/public/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ "email": email,
                "password": password
            })
        });
        
        const data = await response.json(); // Parse JSON response
        console.log(data);
        
        return data["success"];
    } catch (error) {
        alert(error);
        console.error('Error:', error);
        return false;
    }
}

// Function to make the registration API call
export async function registerUser(formData) {
    try {
        console.log(formData);
        const response = await fetch('/socialMedia/public/api/auth/register', {
            method: 'POST',
            body: formData // Browser will set appropriate Content-Type with boundary
        });
        
        const data = await response.json();
        console.log(data);
        
        return data;
    } catch (error) {
        alert(error);
        console.error('Error:', error);
        return false;
    }
}