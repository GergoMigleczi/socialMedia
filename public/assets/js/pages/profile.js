import {handleFriendAction, isFriend} from '../modules/friendCore.js';
import {blockProfile, unblockProfile}  from '../modules/blockingCore.js';
import { reportProfile } from '../modules/ProfileReportCore.js';
import { showFeedbackMessage } from '../modules/feedback.js';

/**
 * Profile Interactions Module
 * 
 * This script handles various user interactions on profile pages including:
 * - Friend requests and friendship management
 * - Profile blocking and unblocking
 * - Profile reporting functionality
 * 
 * It attaches event listeners to the appropriate UI elements and manages
 * the API calls and UI updates that result from user actions.
 */

/**
 * Initialize all event listeners when the DOM content is fully loaded
 * Attaches handlers to friend buttons, block buttons, and report forms
 */
document.addEventListener('DOMContentLoaded', () => {
    // Set up friend action buttons (add, accept, remove, etc.)
    const friendActionContainer = document.getElementById('friend-action-container');
    if (friendActionContainer){
        friendActionContainer.addEventListener('click', handleFriendButtonClick);
    }
    
    // Set up block/unblock profile button
    const blockButton = document.getElementById('block-profile-btn');
    if (blockButton) {
        blockButton.addEventListener('click', handleBlockButtonClick);
    }

    // Set up report profile form submission
    const reportForm = document.getElementById('reportUserForm');
    if(reportForm){
        reportForm.addEventListener('submit', handleReportFormSubmit);
    }
});

/**
 * Handles friend action button clicks (add friend, accept request, unfriend, etc.)
 * Uses event delegation to handle clicks within the friend action container
 * 
 * @param {Event} e - The click event
 */
async function handleFriendButtonClick(e) {
    // Get the container that holds all friend action buttons
    const friendActionContainer = document.getElementById('friend-action-container');

    // Find the closest button element with class 'friend-action' to handle event delegation
    const button = e.target.closest('.friend-action');
    if (!button) return; // Exit if click wasn't on a friend action button

    // Extract the profile ID and action type from data attributes
    const profileId = button.dataset.profileId;
    const action = button.dataset.action;
    
    try {
        // Call the API to perform the friend action
        const result = await handleFriendAction(profileId, action);
    
        // If successful, update the UI with the new friend buttons HTML
        if(result['success']){
            // Replace the entire friend button container with updated HTML from server
            friendActionContainer.innerHTML = result.friendButtons;
            
            // Find the chat button in the parent container
            const chatBtn = friendActionContainer.closest('.profile-action-container').querySelector('.chat-btn');
            
            // Show/hide chat button based on friendship status
            if(result.newStatus == 'Friends'){
                // Show chat button if users are now friends
                if (chatBtn) {
                    chatBtn.style.display = '';
                }
            } else {
                // Hide chat button if users are not friends
                if (chatBtn) {
                    chatBtn.style.display = 'none';
                }
            }
        } else {
            throw new Error(result['message']);
        }
    } catch(error) {
        // Display error message to user
        showFeedbackMessage(error.message, 'danger');
    }
}

/**
 * Handles block/unblock button clicks
 * Toggles between blocking and unblocking a profile
 * 
 * @param {Event} event - The click event
 */
async function handleBlockButtonClick(event) {
    const button = event.currentTarget;
    const profileId = button.dataset.profileId;
    
    // Determine current block status from button text
    const isCurrentlyBlocked = button.textContent.trim() === 'Unblock';
    
    try {
        // Disable button to prevent multiple clicks during processing
        button.disabled = true;
        
        let response;
        if (isCurrentlyBlocked) {
            // User is currently blocked, so unblock them
            response = await unblockProfile(profileId);
            
            if(response['success']){
                // Update button appearance to show "Block"
                button.textContent = 'Block';
                button.classList.remove('btn-secondary');
                button.classList.add('btn-danger');

                // If the other user hasn't blocked the current user
                if(!response['isLoggedInProfileBlocked']){
                    // Show chat button if users are friends
                    if(await isFriend(profileId)){
                        setChatButtonVisibility(button.closest(".profile-action-container"), true);
                    }
                    // Show friend buttons
                    setFriendButtonVisibility(button.closest(".profile-action-container"), true);
                }
            } else {
                throw new Error(response['message']);
            }
        } else {
            // User is not blocked, so block them
            response = await blockProfile(profileId);
            
            if(response['success']){
                // Update button appearance to show "Unblock"
                button.textContent = 'Unblock';
                button.classList.remove('btn-danger');
                button.classList.add('btn-secondary');
                
                // Update friend button if provided in the response
                if(response['friendButton']){
                    const friendActionContainer = document.getElementById('friend-action-container');
                    if(friendActionContainer){
                        friendActionContainer.innerHTML = response['friendButton'];
                    }
                }
                
                // Hide chat and friend buttons when blocking a user
                setChatButtonVisibility(button.closest(".profile-action-container"), false);
                setFriendButtonVisibility(button.closest(".profile-action-container"), false);
            } else {
                throw new Error(response['message']);
            }
        }
        
        // Log success for debugging
        console.log('Action successful:', response);
    } catch (error) {
        // Handle and display errors
        console.error('Failed to perform action:', error);
        showFeedbackMessage(`Failed to ${isCurrentlyBlocked ? 'unblock' : 'block'} user: ${error.message}`, 'danger');
    } finally {
        // Re-enable button when operation completes (success or error)
        button.disabled = false;
    }
}

/**
 * Toggle visibility of the chat button
 * 
 * @param {HTMLElement} container - The parent container element
 * @param {boolean} visibility - Whether the button should be visible
 */
function setChatButtonVisibility(container, visibility){
    // Convert boolean to CSS display value
    if(visibility){
        visibility = '';  // Default display value
    } else {
        visibility = 'none';  // Hidden
    }
    
    // Find and update chat button visibility
    let chatButton = container.querySelector(".chat-btn");
    if (chatButton) chatButton.style.display = visibility;
}

/**
 * Toggle visibility of all friend action buttons
 * 
 * @param {HTMLElement} container - The parent container element
 * @param {boolean} visibility - Whether the buttons should be visible
 */
function setFriendButtonVisibility(container, visibility){
    // Convert boolean to CSS display value
    if(visibility){
        visibility = '';  // Default display value
    } else {
        visibility = 'none';  // Hidden
    }
    
    // Find and update all friend action buttons visibility
    let friendActionButtons = container.querySelectorAll(".friend-action");
    if (friendActionButtons.length > 0) {
        friendActionButtons.forEach(button => {
            button.style.display = visibility;
        });
    }
}

/**
 * Handle submission of the report profile form
 * 
 * @param {Event} event - The form submission event
 */
async function handleReportFormSubmit(event) {
    event.preventDefault(); // Prevent default form submission behavior

    // Get form input values
    const profileId = document.getElementById('profileId').value;
    const reason = document.getElementById('reportReason').value;
    const details = document.getElementById('reportDetails').value;

    try {
        // Submit the report to the server
        const result = await reportProfile(profileId, reason, details);
        
        if(result['success']){
            // Close the report modal on successful submission
            const reportUserModal = bootstrap.Modal.getInstance(document.getElementById('reportUserModal'));
            reportUserModal.hide();
            
            // Show success message to user
            showFeedbackMessage('Profile has been reported', 'success');
        } else {
            throw new Error(result['message']);
        }
    } catch (error) {
        // Display error message if report submission fails
        showFeedbackMessage('Failed to submit report. ' + error.message, 'danger');
    }
}