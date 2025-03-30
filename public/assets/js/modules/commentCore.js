/**
 * fetch comments of a post
 * @param {int} postId of post
 * @param {HTMLElement} commentsHtml
 */
export async function fetchCommentsHtml(postId) {
  try {
    // Get comments from api
    //${window.location.origin}
    const response = await fetch(`/socialMedia/public/api/posts/${postId}/comments?format=html`);
    // Extract response from json
    const commentsHtml = await response.text();
    // Return comments
    return commentsHtml;
  } catch (error) {
    alert(error);
    console.error("Error fetching comments:", error);
    return '<div class="alert alert-info">No comments.</div>';
  }
}

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
    const commentHtml = await response.text();
    return commentHtml
  }catch (error){
    alert(error);
    console.error("Error submitting comment:", error);
    return '';
  }
}


