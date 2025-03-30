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
  
      if (!response.ok) {
        const errorData = await response.json().catch(() => null);
        console.error(errorData?.message || `Error creating like: ${response.status}`);
        return {};
      }
  
      // For 201 Created responses, return the response data
      // For 204 No Content responses, return an empty object
      return response.status === 204 ? {} : await response.json();
    } catch (error) {
      alert(error);
      console.error('Create like request failed:', error);
      return {};
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
  
      if (!response.ok) {
        const errorData = await response.json().catch(() => null);
        console.error(errorData?.message || `Error creating like: ${response.status}`);
        return {};
      }
  
      // For successful deletion, return an empty object if no content is returned
      return response.status === 204 ? {} : await response.json();
    } catch (error) {
      alert(error);
      console.error('Delete like request failed:', error);
      return {};
    }
  }