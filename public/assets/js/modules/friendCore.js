/**
 * Handle friend-related actions with another profile
 * 
 * Sends a request to perform various friend actions such as:
 * - Sending a friend request
 * - Accepting a friend request
 * - Canceling a sent friend request
 * - Declining a received friend request
 * - Unfriending an existing friend
 * 
 * @param {number} profileId - The ID of the profile to perform the action with
 * @param {string} action - The friend action to perform ('request', 'accept', 'cancel', 'decline', 'unfriend')
 * @returns {Promise<Object>} - Response object containing success status, updated HTML elements, and message
 * @throws {Error} - If the request fails or returns an error response
 */
export async function handleFriendAction(profileId, action) {
    try {
        // Send POST request to the friends endpoint
        const response = await fetch('/socialMedia/public/api/friends', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            // Include both profile ID and the specific action in the request body
            body: JSON.stringify({
                profileId: profileId,
                action: action
            })
        });
        
        // Parse the JSON response from the server
        const responseBody = await response.json()

        // Check if the request was successful (status code 200-299)
        // If not, throw an error with the message from the server
        if (!response.ok) {
            throw new Error(responseBody.message);
        }
        
        // Return the successful response data
        // This typically contains updated UI elements or status information
        return responseBody
    } catch (error) {
        // Log any errors to the console for debugging
        console.error('Error:', error);
        // Re-throw the error to be handled by the calling function
        throw error;
    }
}

/**
 * Check if a profile is friends with the current user
 * 
 * Sends a request to determine the friendship status between
 * the current logged-in user and another profile.
 * 
 * @param {number} profileId - The ID of the profile to check friendship status with
 * @returns {Promise<boolean>} - True if profiles are friends, false otherwise
 * @throws {Error} - If the request fails or returns an error response
 */
export async function isFriend(profileId) {
    try {
        // Send GET request to the isFriend endpoint with the profile ID in the URL
        const response = await fetch(`/socialMedia/public/api/friends/${profileId}/isFriend`);
        
        // Parse the JSON response from the server
        const responseBody = await response.json()
        
        // Check if the request was successful (status code 200-299)
        // If not, throw an error with the message from the server
        if (!response.ok) {
            throw new Error(responseBody.message);
        }

        // Check if the operation was successful according to the response
        if(responseBody['success']){
            // Return the friendship status boolean
            return responseBody['isFriend']
        }else{
            // If the operation wasn't successful despite a 200 status code,
            // throw an error with the error message
            throw new Error(responseBody['message'])
        }
    } catch (error) {
        // Log any errors to the console for debugging
        console.error('Error:', error);
        // Re-throw the error to be handled by the calling function
        throw error;
    }
}