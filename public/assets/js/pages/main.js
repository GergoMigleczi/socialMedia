import { hideFeedbackMessage } from "../modules/feedback";
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
      // This is triggered when navigating back using browser controls
      window.location.reload(); // to make sure the page doesn't load from cache
    }
});

document.addEventListener('DOMContentLoaded', function() {    
  // Feedback close button event listener
  const closeButton = document.querySelector('#feedbackBox .btn-close');
  if (closeButton) {
      closeButton.addEventListener('click', hideFeedbackMessage);
  }
});
