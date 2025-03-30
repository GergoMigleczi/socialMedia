import {
  fetchCommentsHtml,
  submitComment
} from "../modules/commentCore.js";

document.querySelectorAll('.comment-btn').forEach(button => {
    button.addEventListener('click', displayAllComments);
});

document.querySelectorAll('.comment-form').forEach(button => {
  button.addEventListener('submit', postComment);
});

/**
 * Handle comment form submit event
 * @param {Event} e - The click event
 */
async function postComment(e) {
  e.preventDefault(); // Prevent standard form submission
  const commentText = this.querySelector('.comment-input').value;
  const postId = this.getAttribute('data-post-id');
  
  const commentHtml = await submitComment(postId, commentText);
  increaseCommentCounter(postId);
  renderNewComment(postId, commentHtml);
  
  // Clear the input after submission
  this.querySelector('.comment-input').value = '';
};

/**
 * Increase comment counter
 * @param {int} postId post id to identify container
 */
function increaseCommentCounter(postId){
  const commentCounter = document.getElementById('comment-counter-'+postId);
  const currentCount = parseInt(commentCounter.textContent, 10);

  // Increase by one
  commentCounter.textContent = currentCount + 1;
}

/**
 * Display new comment
 * @param {int} postId post id to identify container
 * @param {string} commentHtml html of the new comment
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
 * Handle comment button click event
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
    // Fetch the comment of the post
    const comments = await fetchCommentsHtml(postId);
    commentContainer.innerHTML = comments;
    // Show the comment container
    commentsSection.classList.remove('d-none')
  }else{
    // Hide the comment container
    commentsSection.classList.add('d-none')
  }
}

