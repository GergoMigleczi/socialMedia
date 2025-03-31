/**
 * Reports a user profile to the server with specified reason and optional details.
 * @param {string} profileId - The ID of the profile being reported
 * @param {string} reason - The reason for reporting the profile
 * @param {string} [details=''] - Additional details about the report (optional)
 * @returns {Promise<Object>} The response body from the server if successful
 * @throws {Error} If the request fails or server returns an error response
 */
export async function reportProfile(profileId, reason, details = '') {
    try {
        // Make POST request to report endpoint
        const response = await fetch('/socialMedia/public/api/profiles/report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'  // Specify JSON content type
            },
            // Include report data in request body
            body: JSON.stringify({
                profileId: profileId,  // ID of reported profile
                reason: reason,       // Primary reason for report
                details: details      // Additional context (optional)
            })
        });
        
        // Parse JSON response from server
        const responseBody = await response.json();
        
        // Check if request was successful (status code 2xx)
        if (!response.ok) {
            // If server returned error, throw with server's error message
            throw new Error(responseBody.message);
        }

        // Return successful response data
        return responseBody;
    } catch (error) {
        // Log error to console for debugging
        console.error('Error reporting profile:', error);
        // Re-throw error to allow calling code to handle it
        throw error;
    }
}