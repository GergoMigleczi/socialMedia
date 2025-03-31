import { getPrivateChatId,
    createPrivateChat
} from '../modules/chatCore.js';
import { showFeedbackMessage } from '../modules/feedback.js';

/**
 * Initializes event listeners for chat buttons when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.chat-btn').forEach(button => {
        button.addEventListener('click', handleChatButtonClick);
    });
});

/**
 * Handles chat button clicks to open or create private chats
 * @async
 * @param {Event} event - The click event object
 */
async function handleChatButtonClick(event) {
    // Get the profile ID from the data attribute
    const profileId = this.getAttribute('data-profile-id');

    try{
        // First, try to get existing chat
        const existingChat = await getPrivateChatId(profileId);
        if (existingChat.success && existingChat.chatId) {
            // Redirect to existing chat
            window.location.href = `/socialMedia/public/chats/${existingChat.chatId}`;
            return;
        }

        // If no existing chat, try to create a new one
        const newChat = await createPrivateChat(profileId);
        if (newChat.success && newChat.chatId) {
            // Redirect to new chat
            window.location.href = `/socialMedia/public/chats/${newChat.chatId}`;
        } else {
            // Show error message if chat creation fails
            throw new Error(newChat.message || 'Unable to open chat');
        }
    }catch(error){
        console.error(error)
        showFeedbackMessage(error.message, 'danger')
    }
}