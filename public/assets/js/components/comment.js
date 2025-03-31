import {
  fetchCommentsHtml,
  submitComment
} from "../modules/commentCore.js";
import { showFeedbackMessage } from "../modules/feedback.js";

/**
 * Initializes event listeners for comment buttons and forms when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
  // Comment Button
  document.querySelectorAll('.comment-btn').forEach(button => {
    button.addEventListener('click', displayAllComments);
  });

  // Comment Form
  document.querySelectorAll('.comment-form').forEach(button => {
    button.addEventListener('submit', postComment);
  });
});


/**
 * Handle comment form submit event
 * @param {Event} e - The submit event
 */
async function postComment(e) {
  e.preventDefault(); // Prevent standard form submission
  const commentText = this.querySelector('.comment-input').value;
  const postId = this.getAttribute('data-post-id');
  
  try{
    // Attempt to submit comment
    const commentHtml = await submitComment(postId, commentText);
    // Increase comment counter
    increaseCommentCounter(postId);
    // Render the new comment
    renderNewComment(postId, commentHtml);
  }catch(error){
    showFeedbackMessage(error.message, 'danger');
  }
  
  // Clear the input after submission
  this.querySelector('.comment-input').value = '';
};

/**
 * Increase comment counter
 * @param {number} postId - Post id to identify container
 */
function increaseCommentCounter(postId){
  const commentCounter = document.getElementById('comment-counter-'+postId);
  const currentCount = parseInt(commentCounter.textContent, 10);

  // Increase by one
  commentCounter.textContent = currentCount + 1;
}

/**
 * Display new comment
 * @param {number} postId - Post id to identify container
 * @param {string} commentHtml - HTML of the new comment
 */
function renderNewComment(postId, commentHtml){
  const commentsSection = document.getElementById('comments-section-'+postId);
  if (!commentsSection) return;
  // Find the comments container for this post
  const commentsContainer = commentsSection.querySelector('.comment-container');
          
  // If there's currently a "No comments" message, remove it
  const noCommentsAlert = commentsContainer.querySelector('.alert-info');
  if (noCommentsAlert) {
      noCommentsAlert.remove();
  }

  // Insert the new comment at the top of the comments list
  commentsContainer.insertAdjacentHTML('afterbegin', commentHtml);
}

/**
 * Handle comment button click event to show/hide comments
 * @param {Event} e - The click event
 */
async function displayAllComments(e) {
  // Get post id
  const postId = this.getAttribute('data-post-id');
  // Get comment container
  const commentsSection = document.getElementById('comments-section-'+postId);
  if (!commentsSection) return;

  // If comment container is hidden
  if (commentsSection.classList.contains('d-none')) {
    const commentContainer = commentsSection.querySelector('.comment-container')
    // Clear all comments
    commentContainer.innerHTML = '';
    try{
      // Fetch the comment of the post
      const comments = await fetchCommentsHtml(postId);
      commentContainer.innerHTML = comments;
      // Show the comment container
      commentsSection.classList.remove('d-none')
    }catch(error){
      showFeedbackMessage(error.message, 'danger')
    }
  }else{
    // Hide the comment container
    commentsSection.classList.add('d-none')
  }
}