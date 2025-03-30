import {  
    addImageToCarousel, 
    removeImageFromCarousel 
} from '../modules/carouselCore.js';

import {
    sendPostRequest
} from '../modules/postCore.js';

import{
    getUserLocation,
    getAddress
} from '../modules/location.js';

// File tracker to maintain state between carousel and form data
let fileTracker = {};

document.addEventListener('DOMContentLoaded', function() {    
    // Set up file upload functionality
    setupFileUploadHandling();

    // Submit post event listener
    document.getElementById('newPostForm').addEventListener('submit', handlePostSubmission);

    // Location
    document.getElementById('current-location-btn').addEventListener('click', handleCurrentLocation);

});

/**
 * Set up file upload event listeners and handling
 */
function setupFileUploadHandling() {
    const fileUploadInput = document.getElementById('fileUpload');
    if (!fileUploadInput) return;
    
    fileUploadInput.addEventListener('change', handleFileUpload);
}

/**
 * Handle file upload event
 * @param {Event} e - The change event
 */
function handleFileUpload(e) {
    const files = e.target.files;
    if (files.length === 0) return;
    
    const previewContainer = document.getElementById('previewContainer');
    if (!previewContainer) return;
    
    const carousel = document.getElementById('post-carousel-0');
    if (!carousel) return;
    
    // Process each file
    for (let i = 0; i < Math.min(files.length, 5); i++) {
        const file = files[i];
        
        // Check if file is an image
        if (!file.type.match('image.*')) continue;
        
        // Generate a unique ID for this file
        const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        
        // Store the file in our tracker
        fileTracker[fileId] = file;
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Add image to carousel with remove button and file ID
            const carouselItem = addImageToCarousel(
                carousel, 
                e.target.result, 
                true, 
                handleImageRemoval,
                fileId  // Pass the file ID to link the carousel item to the file
            );
            
            // Show the preview container now that we have images
            if (carousel.querySelectorAll('.carousel-item').length > 0) {
                previewContainer.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(file);
    }
    
    // Clear the file input to allow re-uploading the same file if needed
    e.target.value = '';
}

/**
 * Handle image removal
 * @param {HTMLElement} slideToRemove - The carousel slide to remove
 */
function handleImageRemoval(slideToRemove) {
    const carousel = document.getElementById('post-carousel-0');
    const previewContainer = document.getElementById('previewContainer');
    
    // Get the file ID from the slide before removing it
    const fileId = slideToRemove.getAttribute('data-file-id');
    
    // Remove the file from our tracker if we have an ID
    if (fileId && fileTracker[fileId]) {
        delete fileTracker[fileId];
    }
    
    // Remove the image and get count of remaining slides
    const remainingCount = removeImageFromCarousel(carousel, slideToRemove);
    
    // Hide the container if no images left
    if (remainingCount === 0) {
        previewContainer.style.display = 'none';
    }
}

async function handlePostSubmission(event) {
    event.preventDefault();
    
    const form = document.getElementById('newPostForm');
    const formData = new FormData(form);
    
    // Remove any existing media files that might have been auto-added
    formData.delete('media[]');
    
    // Add only the files from our file tracker to the form data
    Object.values(fileTracker).forEach(file => {
        formData.append('media[]', file);
    });
    
    const result = await sendPostRequest(formData);
    if(result['success']){
        window.location.pathname = window.location.pathname.replace("/addPost", "/home");
    }
}

async function handleCurrentLocation(event) {
    try {
        let position = await getUserLocation();
        if(position.coords.latitude){
            console.log("Latitude:", position.coords.latitude);
            document.getElementById('latitude').value = position.coords.latitude
        }
        if(position.coords.longitude){
            console.log("Longitude:", position.coords.longitude);
            document.getElementById('longitude').value = position.coords.longitude
        }
        let address = await getAddress(position.coords.latitude, position.coords.longitude);
        if(address){
            document.getElementById('location').value = address
        }else{
            alert('Current location not found')
        }
    } catch (error) {
        alert(error.message)
        console.error("Error getting location:", error);
        return null;
    }
}

