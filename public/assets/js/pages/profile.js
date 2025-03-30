import {handleFriendAction, isFriend} from '../modules/friendCore.js';
import {blockProfile, unblockProfile}  from '../modules/blockingCore.js';

document.addEventListener('DOMContentLoaded', () => {
    // Friend buttons
    const friendActionContainer = document.getElementById('friend-action-container');
    if (friendActionContainer){
        friendActionContainer.addEventListener('click', handleFriendButtonClick);
    }
    // Block button
    const blockButton = document.getElementById('block-profile-btn');
    if (blockButton) {
        blockButton.addEventListener('click', handleBlockButtonClick);
    }
});

/**
 * Handles the friend button click event
 * @param {Event} event - The click event
 */
async function handleFriendButtonClick(e) {
    const friendActionContainer = document.getElementById('friend-action-container');

    const button = e.target.closest('.friend-action');
    if (!button) return;

    const profileId = button.dataset.profileId;
    const action = button.dataset.action;

    const result = await handleFriendAction(profileId, action);
    console.log(result)
    // Replace the entire container content with new HTML
    if(result.success){
        friendActionContainer.innerHTML = result.friendButtons;
        const chatBtn = friendActionContainer.closest('.profile-action-container').querySelector('.chat-btn');
        if(result.newStatus == 'Friends'){
            if (chatBtn) {
                chatBtn.style.display = 'inline-block';
            }
        }else{
            if (chatBtn) {
                chatBtn.style.display = 'none';
            }
        }
    }
}

/**
 * Handles the block/unblock button click event
 * @param {Event} event - The click event
 */
async function handleBlockButtonClick(event) {
    const button = event.currentTarget;
    const profileId = button.dataset.profileId;
    const isCurrentlyBlocked = button.textContent.trim() === 'Unblock';
    
    try {
        // Disable button during the request
        button.disabled = true;
        
        let response;
        if (isCurrentlyBlocked) {
            // User is currently blocked, so unblock them
            response = await unblockProfile(profileId);
            
            if(response['success']){
                // Update button to show Block
                button.textContent = 'Block';
                button.classList.remove('btn-secondary');
                button.classList.add('btn-danger');

                if(!response['isLoggedInProfileBlocked']){
                    //Show chat and friend buttons
                    if(await isFriend(profileId)){
                        setChatButtonVisibility(this.closest(".profile-action-container"), true);
                    }
                    setFriendButtonVisibility(this.closest(".profile-action-container"), true);
                }
            }else{
                alert(response['message'])
            }
        } else {
            // User is not blocked, so block them
            response = await blockProfile(profileId);
            
            if(response['success']){
                // Update button to show Unblock
                button.textContent = 'Unblock';
                button.classList.remove('btn-danger');
                button.classList.add('btn-secondary');
                
                if(response['friendButton']){
                    const friendActionContainer = document.getElementById('friend-action-container')
                    if(friendActionContainer){
                        friendActionContainer.innerHTML = response['friendButton']
                    }
                }
                //Hide chat and friend buttons
                setChatButtonVisibility(this.closest(".profile-action-container"), false);
                setFriendButtonVisibility(this.closest(".profile-action-container"), false);

            }else{
                alert(response['message'])
            }
        }
        
        // Optional: Display success message
        console.log('Action successful:', response);
    } catch (error) {
        // Handle errors
        console.error('Failed to perform action:', error);
        alert(`Failed to ${isCurrentlyBlocked ? 'unblock' : 'block'} user: ${error.message}`);
    } finally {
        // Re-enable button
        button.disabled = false;
    }
}

function setChatButtonVisibility(container, visibility){
    if(visibility){
        visibility = ''
    }else{
        visibility = 'none'
    }
    // Hide the chat button
    let chatButton = container.querySelector(".chat-btn");
    if (chatButton) chatButton.style.display = visibility;
}

function setFriendButtonVisibility(container, visibility){
    if(visibility){
        visibility = ''
    }else{
        visibility = 'none'
    }
    // Hide the friend action container
    let friendActionButtons = container.querySelectorAll(".friend-action");
    if (friendActionButtons.length > 0) {
        friendActionButtons.forEach(button => {
            button.style.display = visibility;
        });
    }
}