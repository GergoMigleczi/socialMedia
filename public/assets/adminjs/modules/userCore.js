/**
 * Makes an API call to block a user until a specified date
 * @param {number} userId - The ID of the user to block
 * @param {string} blockUntilDate - The date until which the user will be blocked (YYYY-MM-DD)
 * @returns {Promise<Object>} - Response from the server
 */
export async function blockUser(userId, blockUntilDate) {
    try {
      const response = await fetch('/socialMedia/admin/api/users/block', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          userId: userId,
          blockedUntil: blockUntilDate
        })
      });
  
      const data = await response.json();
      console.log(data)

      if (!response.ok) {
        throw new Error(data.message || 'Error blocking user');
      }
      
      if(data['success']){
        return data['success'];
      }else{
        throw new Error('Failed to block user');
      }
    } catch (error) {
      console.error('Error in blockUser:', error);
      throw error; // Re-throw to allow handling in the calling function
    }
  }

/**
 * Makes an API call to unblock a user
 * @param {number} userId - The ID of the user to unblock
 * @returns {Promise<Object>} - Response from the server
 */
export async function unblockUser(userId) {
    try {
      const response = await fetch('/socialMedia/admin/api/users/unblock', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          userId: userId
        })
      });
  
      const data = await response.json();
      console.log(data)

      if (!response.ok) {
        throw new Error(data.message || 'Error unblocking user');
      }
      
      if(data['success']){
        return data['success'];
      }else{
        throw new Error('Failed to unblock user');
      }
    } catch (error) {
      console.error('Error in unblockUser:', error);
      throw error; // Re-throw to allow handling in the calling function
    }
  }

/**
 * Makes an API call to delete a user
 * @param {number} userId - The ID of the user to delete
 * @returns {Promise<Object>} - Response from the server
 */
export async function deleteUser(userId) {
    try {
      const response = await fetch(`/socialMedia/admin/api/users/${userId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json'
        }
      });
  
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Error deleting user');
      }
      
      if(data['success']){
        return data['success'];
      }else{
        throw new Error('Failed to delete user');
      }
    } catch (error) {
      console.error('Error in deleteUser:', error);
      throw error; // Re-throw to allow handling in the calling function
    }
  }