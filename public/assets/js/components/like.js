import { createLike, deleteLike } from "../modules/likeCore.js";

document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', likePost);
});

// Select all like buttons
async function likePost(e) {
    const postId = this.dataset.postId;
    const counterLikeIcon = document.querySelector(`#like-counter-${postId}`).previousElementSibling;
        
    const likeCounter = document.getElementById(`like-counter-${postId}`);
    const isLiked = counterLikeIcon.classList.contains('bi-heart-fill');

    if(!isLiked){
        //was not already liked -> like post
        const liked = await createLike(postId);
        console.log(liked)
        if(liked.hasOwnProperty("success") && liked["success"]){
            toggleLikeUI(counterLikeIcon, likeCounter, true);
        }
    }else{
        //was already liked -> unlike post
        const unLiked = await deleteLike(postId);
        console.log(unLiked)
        if(unLiked.hasOwnProperty("success") && unLiked["success"]){
            // Get icon in the counter area
            // Toggle like UI
            toggleLikeUI(counterLikeIcon, likeCounter, false);
        }
    }
};

// Helper function to toggle UI based on like status
function toggleLikeUI( counterIcon, counter, isLiked) {
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