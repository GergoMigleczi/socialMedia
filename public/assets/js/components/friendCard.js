import {handleFriendAction} from '../modules/friendCore.js';

document.querySelectorAll('.friend-action').forEach(button => {
    button.addEventListener('click', unfriendButtonClick);
});

async function unfriendButtonClick(e){
    const profileId = this.dataset.profileId;
    const action = this.dataset.action;

    const result = await handleFriendAction(profileId, action);
    console.log(result)
    
    // If unfriend action is successful, remove the entire friend card
    if(result.success){
        // Find the closest parent .friend-card and remove it
        const friendCard = this.closest('.friend-card');
        if(friendCard){
            friendCard.remove();
        }
    } else {
        alert(result.message)
    }
}