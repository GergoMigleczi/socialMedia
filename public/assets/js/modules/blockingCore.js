/**
 * Block a user profile
 * 
 * Sends a request to the server to block a specified profile.
 * When a profile is blocked:
 * - Any existing friend connections are removed
 * - Friend requests are canceled
 * - The blocked user cannot send new friend requests
 * 
 * @param {number} profileId - The ID of the profile to block
 * @returns {Promise<Object>} - Response containing success status, updated friend button HTML, and message
 * @throws {Error} - If the request fails or returns an error response
 */
export async function blockProfile(profileId) {
    try {
        // Send POST request to the block endpoint
        const response = await fetch('/socialMedia/public/api/profiles/block', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            // Convert profile ID to JSON string in request body
            body: JSON.stringify({
                profileId: profileId
            })
        });
        
        // Parse the JSON response
        const responseBody = await response.json();
        
        // Check if the request was successful (status code 200-299)
        // If not, throw an error with the error message from the server
        if (!response.ok) {
            throw new Error(responseBody.message);
        }
        
        // Return the successful response data
        return responseBody;
    } catch (error) {
        // Log any errors to the console
        console.error('Error:', error);
        // Re-throw the error to be handled by the calling function
        throw error;
    }
}

/**
 * Unblock a previously blocked user profile
 * 
 * Sends a request to the server to unblock a specified profile.
 * When a profile is unblocked:
 * - Friend relationships are NOT automatically restored
 * - Friend requests can be sent again between the users
 * 
 * @param {number} profileId - The ID of the profile to unblock
 * @returns {Promise<Object>} - Response containing success status, blocked status, and message
 * @throws {Error} - If the request fails or returns an error response
 */
export async function unblockProfile(profileId) {
    try {
        // Send POST request to the unblock endpoint
        const response = await fetch('/socialMedia/public/api/profiles/unblock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            // Convert profile ID to JSON string in request body
            body: JSON.stringify({
                profileId: profileId
            })
        });
        
        // Parse the JSON response
        const responseBody = await response.json();
        
        // Check if the request was successful (status code 200-299)
        // If not, throw an error with the error message from the server
        if (!response.ok) {
            throw new Error(responseBody.message);
        }
        
        // Return the successful response data
        // Contains isLoggedInProfileBlocked flag showing if the other user has blocked you
        return responseBody;
    } catch (error) {
        // Log any errors to the console
        console.error('Error:', error);
        // Re-throw the error to be handled by the calling function
        throw error;
    }
}