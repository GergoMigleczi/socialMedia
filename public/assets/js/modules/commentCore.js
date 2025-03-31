/**
 * Fetch comments of a post
 * @param {number} postId - ID of the post
 * @returns {Promise<string>} HTML string containing comments
 */
export async function fetchCommentsHtml(postId) {
  try {
    // Get comments from api
    const response = await fetch(`/socialMedia/public/api/posts/${postId}/comments?format=html`);
    // Extract response from json
    const commentsHtml = await response.text();
    // Return comments
    return commentsHtml;
  } catch (error) {
    console.error("Error fetching comments:", error);
    return '<div class="alert alert-info">Failed to retreive comments</div>';
  }
}

/**
 * Submit a new comment to a post
 * @param {number} postId - ID of the post
 * @param {string} commentText - Content of the comment
 * @returns {Promise<string>} HTML of the newly created comment
 * @throws {Error} If the submission fails
 */
export async function submitComment(postId, commentText) {
  // Don't submit empty comments
  if (!commentText.trim()) {
      return;
  }
    
  // Prepare the data
  const data = {
      content: commentText,
      returnFormat: 'html' // Request HTML response
  };
  
  try{
    const response = await fetch(`/socialMedia/public/api/posts/${postId}/comments`, {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });
    if (!response.ok) {
      let contentType = response.headers.get("Content-Type");
      if (contentType.includes("application/json")) {
          const responseJson = await response.json(); // Parse JSON if it's a JSON response
          throw new Error(responseJson['message']);
      }
    }
    const commentHtml = await response.text();
    return commentHtml
  }catch (error){
    console.error("Error submitting comment:", error);
    throw error;
  }
}