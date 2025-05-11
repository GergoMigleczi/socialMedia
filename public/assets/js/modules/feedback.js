/**
 * Displays a feedback message in a notification box.
 *
 * @param {string} message - The message to display.
 * @param {"success" | "danger" | "warning"} [type="success"] - The type of message, which determines the alert style.
 * @param {number} [duration=3000] - The duration (in milliseconds) before the message disappears.
 */
export function showFeedbackMessage(message, type = "success", duration = 3000) {
    const feedbackBox = document.getElementById("feedbackBox");
    const feedbackMessage = document.getElementById("feedbackMessage");

    if (!feedbackBox || !feedbackMessage) return;

    // Map colors to Bootstrap alert classes
    const alertClasses = {
        success: "alert-success",
        danger: "alert-danger",
        warning: "alert-warning"
    };

    // Remove existing classes and set new one
    feedbackBox.classList.remove("alert-success", "alert-danger", "alert-warning", "d-none");
    feedbackBox.classList.add(alertClasses[type] || "alert-success");

    // Set message
    feedbackMessage.textContent = message;

    // Show feedback box
    feedbackBox.classList.add("show");

    // Auto-hide after duration
    setTimeout(() => {
        feedbackBox.classList.remove("show");
        feedbackBox.classList.add("d-none");
    }, duration);
}

/**
 * Hides the feedback message in a notification box.
 *
 */
export function hideFeedbackMessage() {
    const feedbackBox = document.getElementById('feedbackBox');
    console.log('close feeback box');
    if (!feedbackBox) return;

    feedbackBox.classList.remove('show');
    feedbackBox.classList.add('d-none');
}