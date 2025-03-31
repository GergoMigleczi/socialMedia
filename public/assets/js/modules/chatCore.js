/**
 * Send a message to a chat
 * @param {number} chatId - ID of the chat
 * @param {string} messageText - Content of the message
 * @returns {Promise<string>} HTML of the newly created message
 * @throws {Error} If the submission fails
 */
export async function sendMessage(chatId, messageText) {
  // Don't submit empty messages
  if (!messageText.trim()) {
    return;
  }
  
  // Prepare the data for the API request
  const data = {
    content: messageText,
    returnFormat: 'html' // Request HTML response; use 'json' if you want JSON
  };
  
  try {
    // Make API call to send the message
    const response = await fetch(`/socialMedia/public/api/chats/${chatId}/messages`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data),
      credentials: 'same-origin' // Send cookies/session data for authentication
    });
    
    // Check if response is successful
    if (!response.ok) {
      let contentType = response.headers.get("Content-Type");
      // Handle JSON error responses
      if (contentType.includes("application/json")) {
          const responseJson = await response.json(); // Parse JSON if it's a JSON response
          throw new Error(responseJson['message']);
      }
    }

    // Return the HTML representation of the message
    return await response.text();

  } catch (error) {
    // Log and propagate the error
    console.error("Error sending message:", error);
    throw error;
  }
}

/**
* Get existing chat ID for two profiles
* 
* @param {number} profileId - The profile ID to create/find a chat with
* @returns {Promise<{success: boolean, chatId: number|null, message: string}>}
*/
export async function getPrivateChatId(profileId) {
try {
    // Make API call to get existing chat
    const response = await fetch(`/socialMedia/public/api/chats/private/${profileId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    });

    // Check for network/server errors
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }

    // Parse the response data
    const data = await response.json();

    // Return formatted response object
    return {
        success: data.success,
        chatId: data.chatId || null,
        message: data.error || 'Chat retrieval processed'
    };

} catch (error) {
    // Log and propagate the error
    console.error('Error getting private chat:', error);
    throw error;
}
}

/**
* Create a new private chat with a profile
* 
* @param {number} profileId - The profile ID to create a chat with
* @returns {Promise<{success: boolean, chatId: number|null, message: string}>}
*/
export async function createPrivateChat(profileId) {
try {
    // Make API call to create new private chat
    const response = await fetch('/socialMedia/public/api/chats/private', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ profileId })
    });

    // Parse response data
    const data = await response.json();
    
    // Check for API-level errors
    if (!response.ok) {
      throw new Error(data.message || 'Request failed');
    }
    
    // Return formatted response object
    return {
        success: data.success,
        chatId: data.chatId || null,
        message: data.message || (data.success ? 'Chat created successfully' : 'Chat creation failed')
    };

} catch (error) {
    // Log and propagate the error
    console.error('Error creating private chat:', error);
    throw error;
}
}