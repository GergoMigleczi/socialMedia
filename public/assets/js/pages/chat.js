import {
    sendMessage
} from '../modules/chatCore.js';
import { showFeedbackMessage } from '../modules/feedback.js';

// Handle window resize
window.addEventListener('resize', function() {
  const headerHeight = document.querySelector('header.sticky-top').offsetHeight;
  document.documentElement.style.setProperty('--header-height', headerHeight + 'px');
});

document.addEventListener('DOMContentLoaded', function() {
    // Adjust layout and scroll to bottom
    scrollToBottom();  
    
    const headerHeight = document.querySelector('header.sticky-top').offsetHeight;
    document.documentElement.style.setProperty('--header-height', headerHeight + 'px');
    
    //set focus onto input
    const inputField = document.getElementById('message-input');
    if (inputField) {
        inputField.focus();
    }

    // Init message event handler
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
      messageForm.addEventListener('submit', sendChatMessage);
    }
});

// Function to scroll to the bottom of the chat
function scrollToBottom() {
    const chatMessages = document.getElementById('chat-messages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}
  
/**
 * Handle message form submit event
 * @param {Event} e - The submit event
 */
async function sendChatMessage(e) {
    e.preventDefault(); // Prevent standard form submission
    
    const messageForm = document.getElementById('message-form');
    const chatId = messageForm.getAttribute('data-chat-id');
    const messageInput = document.getElementById('message-input');
    const messageText = messageInput.value;
    
    if (!messageText.trim()) return; // Don't send empty messages
    
    // Disable submit button during submission
    const submitButton = document.getElementById('message-submit-button');
    submitButton.disabled = true;
    
    try {
      const messageHtml = await sendMessage(chatId, messageText);
      renderNewMessage(messageHtml);
      
      // Clear the input after successful submission
      messageInput.value = '';
    } catch (error) {
      console.error('Failed to send message:', error);
      showFeedbackMessage(error.message, 'danger')
    } finally {
      // Re-enable the submit button
      submitButton.disabled = false;
      messageInput.focus(); // Return focus to input for continuous chatting
    }
  }
  
  /**
   * Display new message in chat
   * @param {string} messageHtml - HTML of the new message
   */
  function renderNewMessage(messageHtml) {
    if (!messageHtml) return;
    
    const chatContainer = document.getElementById('chat-messages');
    if (!chatContainer) return;
    
    // If there's currently a "No messages" alert, remove it
    const noMessagesAlert = chatContainer.querySelector('.alert-info');
    if (noMessagesAlert) {
      noMessagesAlert.remove();
    }
    
    // Add the new message to the end of the chat
    chatContainer.insertAdjacentHTML('beforeend', messageHtml);
    
    // Scroll to the bottom to show the new message
    scrollToBottom(chatContainer);
  }