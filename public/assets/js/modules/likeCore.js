/**
 * @param {number} postId - The ID of the post to like
 * @returns {Promise<Object>} Response data from the server
 */
export async function createLike(postId) {
    try {
      const response = await fetch(`/socialMedia/public/api/posts/${postId}/likes`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      });
  
      const responseBody = await response.json();
      if (!response.ok) {
        throw new Error(responseBody.message || `Error creating like: ${response.status}`);
      }

      // For 201 Created responses, return the response data
      // For 204 No Content responses, return an empty object
      return response.status === 204 ? {} : responseBody;
    } catch (error) {
      console.error('Create like request failed:', error);
      throw error;
    }
  }
  
  /**
   * Delete a like from a post following strict REST principles
   * @param {number} postId - The ID of the post to unlike
   * @returns {Promise<Object>} Response data from the server
   */
  export async function deleteLike(postId) {
    try {
      const response = await fetch(`/socialMedia/public/api/posts/${postId}/likes`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      });
  
      const responseBody = await response.json();
      if (!response.ok) {
        throw new Error(responseBody.message || `Error deleting like: ${response.status}`);
      }
  
      // For successful deletion, return an empty object if no content is returned
      return response.status === 204 ? {} : responseBody;
    } catch (error) {
      console.error('Delete like request failed:', error);
      throw error;
    }
  }