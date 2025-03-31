import { showFeedbackMessage } from '../modules/feedback.js';
import {handleFriendAction} from '../modules/friendCore.js';

/**
 * Initializes event listeners for friend action buttons when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Friend Action Buttons
    document.querySelectorAll('.friend-action').forEach(button => {
        button.addEventListener('click', unfriendButtonClick);
    });
  });

/**
 * Handles the unfriend button click event by removing a friend connection and UI element.
 * @async
 * @param {Event} e - The click event object
 * @returns {Promise<void>} Resolves when the unfriend operation completes
 */
async function unfriendButtonClick(e) {
    // Extract profile ID and action from button's data attributes
    const profileId = this.dataset.profileId;
    const action = this.dataset.action; //Always unfriend

    try {
        // Execute the friend action via API call
        const result = await handleFriendAction(profileId, action);
        
        // Handle successful unfriend action
        if (result.success) {
            // Find and remove the associated friend card from DOM
            const friendCard = this.closest('.friend-card');
            if (friendCard) {
                // Remove the entire friend card element from view
                friendCard.remove();
            }
        } else {
            // Throw error if server indicates unsuccessful operation
            throw new Error(result.message || 'Failed to unfriend user');
        }
    } catch (error) {
        // Display error feedback to user
        showFeedbackMessage(
            error.message || 'An error occurred while unfriending', 
            'danger'
        );
    }
}