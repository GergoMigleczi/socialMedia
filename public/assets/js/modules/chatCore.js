export async function sendMessage(chatId, messageText) {
    // Don't submit empty messages
    if (!messageText.trim()) {
      return;
    }
    
    // Prepare the data
    const data = {
      content: messageText,
      returnFormat: 'html' // Request HTML response; use 'json' if you want JSON
    };
    
    try {
      const response = await fetch(`/socialMedia/public/api/chats/${chatId}/messages`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data),
        credentials: 'same-origin' // Send cookies/session data for authentication
      });
      
      if (!response.ok) {
        let contentType = response.headers.get("Content-Type");
        if (contentType.includes("application/json")) {
            const responseJson = await response.json(); // Parse JSON if it's a JSON response
            throw new Error(responseJson['message']);
        }
      }

      return await response.text();

    } catch (error) {
      alert(error);
      console.error("Error sending message:", error);
      return '';
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
      const response = await fetch(`/socialMedia/public/api/chats/private/${profileId}`, {
          method: 'GET',
          headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
          }
      });

      if (!response.ok) {
          throw new Error('Network response was not ok');
      }

      const data = await response.json();

      return {
          success: data.success,
          chatId: data.chatId || null,
          message: data.error || 'Chat retrieval processed'
      };

  } catch (error) {
      alert(error.message);
      console.error('Error getting private chat:', error);
      return {
          success: false,
          chatId: null,
          message: error.message || 'Failed to retrieve chat'
      };
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
      const response = await fetch('/socialMedia/public/api/chats/private', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
          },
          body: JSON.stringify({ profileId })
      });

      const data = await response.json();
      if (!response.ok) {
        throw new Error(data.message || 'Request failed');
      }
      return {
          success: data.success,
          chatId: data.chatId || null,
          message: data.message || (data.success ? 'Chat created successfully' : 'Chat creation failed')
      };

  } catch (error) {
      alert(error);
      console.error('Error creating private chat:', error);
      return {
          success: false,
          chatId: null,
          message: error.message || 'Failed to create chat'
      };
  }
}