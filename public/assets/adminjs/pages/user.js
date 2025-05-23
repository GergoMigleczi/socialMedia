import {showFeedbackMessage} from '../../js/modules/feedback.js';
import { blockUser , deleteUser, unblockUser} from '../modules/userCore.js';

// Execute when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {    
    // Block User Event Listener
    const blockUserForm = document.getElementById('blockUserForm');
    if (blockUserForm) {
      blockUserForm.addEventListener('submit', handleBlockFormSubmit);
    }

    // Unblock User Event Listener
    const unblockUserBtn = document.getElementById('unblock-user-btn');
    if (unblockUserBtn) {
        unblockUserBtn.addEventListener('click', handleUnblockButtonClick);
    }

    // Delete User Event Listener
    const deleteUserBtn = document.getElementById('delete-user-btn');
    if (deleteUserBtn) {
        deleteUserBtn.addEventListener('click', handleDeleteUserButtonClick);
    }
    
});

/**
 * Handles the block user form submission
 * @param {Event} e - Form submission event
 */
async function handleBlockFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const blockUntilDate = document.getElementById('blockUntilDate').value;
    const userId = form.getAttribute('data-user-id'); // Assuming user ID is stored somewhere
    
    try {
        // Attemt to block the user
        await blockUser(userId, blockUntilDate);

        // Close modal on success
        const modal = bootstrap.Modal.getInstance(document.getElementById('blockUserModal'));
        modal.hide();
        
        // Refresh the page or update UI
        location.reload();
    } catch (error) {
        showFeedbackMessage(error.message, 'danger');
    }
}

/**
 * Handles the unblock user button click
 * @param {Event} e - Form submission event
 */
async function handleUnblockButtonClick(e) {
    
    const button = e.target;
    const userId = button.getAttribute('data-user-id'); // Assuming user ID is stored somewhere
    
    try {
        // Attemt to block the user
        await unblockUser(userId);
        
        // Refresh the page or update UI
        location.reload();
    } catch (error) {
        showFeedbackMessage(error.message, 'danger');
    }
}

/**
 * Handles the delete user button click
 * @param {Event} e - Form submission event
 */
async function handleDeleteUserButtonClick(e) {
    
    const button = e.target;
    const userId = button.getAttribute('data-user-id'); // Assuming user ID is stored somewhere
    
    try {
        // Attemt to block the user
        await deleteUser(userId);
        
        // Go back to 
        window.location.href = "/socialMedia/Admin/users";
    } catch (error) {
        showFeedbackMessage(error.message, 'danger');
    }
}