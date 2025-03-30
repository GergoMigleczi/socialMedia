import { getPrivateChatId,
    createPrivateChat
}
from '../modules/chatCore.js';

document.querySelectorAll('.chat-btn').forEach(button => {
    button.addEventListener('click', async (event) => {
        // Get the profile ID from the data attribute
        const profileId = button.getAttribute('data-profile-id');

        // First, try to get existing chat
        const existingChat = await getPrivateChatId(profileId);
        console.log(existingChat)
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
            alert(newChat.message || 'Unable to start a chat');
        }
    });
});