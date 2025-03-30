/**
 * Sends a request to create a new post.
 * 
 * @param {FormData} formData - The form data containing post content and any attachments.
 * @returns {Promise<Object>} - A promise that resolves with the response body from the server.
 * @throws {Error} - Throws an error if the request fails.
 */
export async function createPost(formData) {
    try {
        const response = await fetch('/socialMedia/public/api/posts', {
            method: 'POST',
            body: formData
        });
        
        const responseBody = await response.json();
        return responseBody;
    } catch (error) {
        console.error('Error submitting post:', error);
        throw error;
    }
}