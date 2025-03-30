import { showFeedbackMessage } from "../modules/feedback.js";
import { createLike, deleteLike } from "../modules/likeCore.js";

// Add event listeners to all like buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', likePost);
    });
});

/**
 * Handles the like/unlike functionality for a post.
 * 
 * @param {Event} e - The click event triggered on the like button.
 * @returns {Promise<void>} - An asynchronous function that updates the like UI and sends requests.
 */
async function likePost(e) {
    const postId = this.dataset.postId;
    const counterLikeIcon = document.querySelector(`#like-counter-${postId}`).previousElementSibling;
    const likeCounter = document.getElementById(`like-counter-${postId}`);
    const isLiked = counterLikeIcon.classList.contains('bi-heart-fill');

    try {
        if (!isLiked) {
            // If not already liked, send a like request
            const liked = await createLike(postId);
            if (liked.hasOwnProperty("success") && liked["success"]) {
                toggleLikeUI(counterLikeIcon, likeCounter, true);
            }
        } else {
            // If already liked, send an unlike request
            const unLiked = await deleteLike(postId);
            if (unLiked.hasOwnProperty("success") && unLiked["success"]) {
                toggleLikeUI(counterLikeIcon, likeCounter, false);
            }
        }
    } catch (error) {
        showFeedbackMessage(error.message || 'Internal server error', 'danger');
    }
};

/**
 * Updates the UI based on the like status.
 * 
 * @param {HTMLElement} counterIcon - The icon element representing the like button.
 * @param {HTMLElement} counter - The counter element displaying the number of likes.
 * @param {boolean} isLiked - Whether the post is being liked (true) or unliked (false).
 */
function toggleLikeUI(counterIcon, counter, isLiked) {
    if (isLiked) {
        // Set to liked state
        counterIcon.classList.remove('bi-heart');
        counterIcon.classList.add('bi-heart-fill', 'text-danger');
        counter.textContent = parseInt(counter.textContent) + 1;
    } else {
        // Set to unliked state
        counterIcon.classList.remove('bi-heart-fill', 'text-danger');
        counterIcon.classList.add('bi-heart');
        counter.textContent = parseInt(counter.textContent) - 1;
    }
}
