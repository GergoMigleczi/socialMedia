import {  
    addImageToCarousel, 
    removeImageFromCarousel 
} from '../modules/carouselCore.js';

import {
    createPost
} from '../modules/postCore.js';

import{
    getUserLocation,
    getAddress
} from '../modules/location.js';

import { showFeedbackMessage } from '../modules/feedback.js';


// File tracker to maintain state between carousel and form data
let fileTracker = {};

document.addEventListener('DOMContentLoaded', function() {    
    // Set up file upload functionality
    document.getElementById('fileUpload').addEventListener('change', handleFileUpload);

    // Submit post event listener
    document.getElementById('newPostForm').addEventListener('submit', handlePostSubmission);

    // Location
    document.getElementById('current-location-btn').addEventListener('click', handleCurrentLocation);

});

/**
 * Handles file upload events, processes image files, and displays previews in a carousel.
 * @param {Event} e - The file input change event containing selected files.
 * @returns {void}
 */
function handleFileUpload(e) {
    // Get the selected files from the input element
    const files = e.target.files;
    if (files.length === 0) return;  // Exit if no files selected
    
    // Get references to DOM elements for preview display
    const previewContainer = document.getElementById('previewContainer');
    if (!previewContainer) return;  // Exit if container not found
    
    const carousel = document.getElementById('post-carousel-0');
    if (!carousel) return;  // Exit if carousel not found
    
    try {
        // Process each file (limit to 5 files maximum)
        for (let i = 0; i < Math.min(files.length, 5); i++) {
            const file = files[i];
            
            // Skip non-image files (only process images)
            if (!file.type.match('image.*')) continue;
            
            // Generate a unique ID for tracking this file
            const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            // Store file reference in tracker object for later submission
            fileTracker[fileId] = file;
            
            // Create FileReader to process the image file
            const reader = new FileReader();
            
            // Define what happens when file is loaded
            reader.onload = function(e) {
                // Add the image to carousel with removal capability
                const carouselItem = addImageToCarousel(
                    carousel, 
                    e.target.result,  // The image data URL
                    true,             // Show remove button
                    handleImageRemoval, // Removal handler
                    fileId            // Associate with our tracked file
                );
                
                // Show preview container if we have at least one image
                if (carousel.querySelectorAll('.carousel-item').length > 0) {
                    previewContainer.style.display = 'block';
                }
            };
            
            // Read the file as Data URL (triggers onload when done)
            reader.readAsDataURL(file);
        }
    } catch(error) {
        // Handle any errors during file processing
        showFeedbackMessage(error.message, 'danger');
    }

    // Reset file input to allow selecting same files again if needed
    e.target.value = '';
}

/**
 * Handles removal of an image from the carousel and updates the file tracker.
 * @param {HTMLElement} slideToRemove - The carousel slide element to be removed.
 * @returns {void}
 */
function handleImageRemoval(slideToRemove) {
    // Get references to DOM elements
    const carousel = document.getElementById('post-carousel-0');
    const previewContainer = document.getElementById('previewContainer');
    
    // Get the associated file ID from the slide's data attribute
    const fileId = slideToRemove.getAttribute('data-file-id');
    
    // Remove the file from our tracker if it exists
    if (fileId && fileTracker[fileId]) {
        delete fileTracker[fileId];
    }
    
    try {
        // Remove the slide from carousel and get remaining count
        const remainingCount = removeImageFromCarousel(carousel, slideToRemove);
        
        // Hide preview container if no images remain
        if (remainingCount === 0) {
            previewContainer.style.display = 'none';
        }
    } catch(error) {
        // Handle any errors during removal process
        showFeedbackMessage(error.message, 'danger');
    }
}

/**
 * Handles the post submission form, processes the form data, and manages the post creation flow.
 * @param {Event} event - The form submission event object.
 * @returns {Promise<void>} A promise that resolves when the post submission process is complete.
 */
async function handlePostSubmission(event) {
    // Prevent the default form submission behavior
    event.preventDefault();
    
    // Get the form element and prepare FormData object
    const form = document.getElementById('newPostForm');
    const formData = new FormData(form);
    
    // Clean up existing media files that might have been auto-added by the browser
    formData.delete('media[]');
    
    // Add only the files from our file tracker to ensure proper file handling
    Object.values(fileTracker).forEach(file => {
        formData.append('media[]', file);
    });
    
    try {
        // Attempt to create the post by sending the form data to the server
        const result = await createPost(formData);
        
        // Handle successful post creation
        if (result['success']) {
            // Show success feedback to user
            showFeedbackMessage('Post created', 'success');
            
            // Redirect to home page after a short delay (500ms)
            setTimeout(() => {
                window.location.pathname = window.location.pathname.replace("/addPost", "/home");
            }, 500);
        } else {
            // Throw an error if the server responded with a failure
            throw new Error(result['message']);
        }
    } catch(error) {
        // Handle any errors that occurred during the process
        showFeedbackMessage(error.message, 'danger');
    }
}

/**
 * Handles the current location request, updates form fields with location data,
 * and displays any errors to the user.
 * @param {Event} event - The event object from the triggering event.
 */
async function handleCurrentLocation(event) {
    try {
        // Get user's current geolocation position
        let position = await getUserLocation();
        
        // Update latitude field if available
        if (position.coords.latitude) {
            console.log("Latitude:", position.coords.latitude);
            document.getElementById('latitude').value = position.coords.latitude;
        }
        
        // Update longitude field if available
        if (position.coords.longitude) {
            console.log("Longitude:", position.coords.longitude);
            document.getElementById('longitude').value = position.coords.longitude;
        }
        
        // Reverse geocode coordinates to get human-readable address
        let address = await getAddress(
            position.coords.latitude, 
            position.coords.longitude
        );
        
        // Update location field if address was found
        if (address) {
            document.getElementById('location').value = address;
        } else {
            // Show error message if no address was found
            showFeedbackMessage('Current location not found', 'danger');
        }
    } catch (error) {
        // Handle any errors that occurred during the process
        showFeedbackMessage(error.message, 'danger');
        console.error("Error getting location:", error);
    }
}

