/**
 * Creates a new post by submitting form data to the server.
 * @param {FormData} formData - The form data containing post content and media files
 * @returns {Promise<Object>} A promise that resolves with the server response
 * @throws {Error} If the network request fails or server returns an error
 */
export async function createPost(formData) {
    try {
        // Send POST request to the posts API endpoint
        const response = await fetch('/socialMedia/public/api/posts', {
            method: 'POST',
            // FormData will automatically set Content-Type to multipart/form-data
            // and include proper boundary for file uploads
            body: formData  
        });
        
        // Parse JSON response from server
        const responseBody = await response.json();
        
        // Check response
        if(!response.ok){
            throw new Error(responseBody['message'])
        }

        // Return response
        return responseBody;
        
    } catch (error) {
        // Log detailed error information for debugging
        console.error('Error submitting post:', error);
        
        // Re-throw the error to allow calling code to handle it
        throw error;
    }
}


