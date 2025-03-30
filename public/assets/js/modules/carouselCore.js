/**
 * Initialize all carousels on the page
 * @returns {NodeList} List of initialized carousels
 */
export function initializeAllCarousels() {
    // Get all carousel elements
    const carousels = document.querySelectorAll('.carousel');
    
    // Assign unique IDs and update controls for each carousel
    carousels.forEach((carousel, index) => {
        if (carousel != null) {
            // Create a unique ID for each carousel (if not already set)
            if (!carousel.id) {
                carousel.id = `post-carousel-${index}`;
            }
            console.log(carousel.id)

            // Update the data-bs-target attributes on the control buttons
            const prevButton = carousel.querySelector('.carousel-control-prev');
            const nextButton = carousel.querySelector('.carousel-control-next');

            if (prevButton) {
                prevButton.setAttribute('data-bs-target', `#${carousel.id}`);
            }

            if (nextButton) {
                nextButton.setAttribute('data-bs-target', `#${carousel.id}`);
            }

            // Check if carousel has images and update controls visibility
            updateCarouselControls(carousel);
        }
    });
    
    return carousels;
}

/**
 * Update carousel controls visibility based on number of slides
 * @param {HTMLElement} carousel - The carousel element to update
 */
export function updateCarouselControls(carousel) {
    const carouselItems = carousel.querySelectorAll('.carousel-item');
    const prevButton = carousel.querySelector('.carousel-control-prev');
    const nextButton = carousel.querySelector('.carousel-control-next');
    
    // Hide/show navigation arrows based on number of images
    if (carouselItems.length <= 1) {
        if (prevButton) prevButton.style.display = 'none';
        if (nextButton) nextButton.style.display = 'none';
    } else {
        if (prevButton) prevButton.style.display = 'block';
        if (nextButton) nextButton.style.display = 'block';
    }
    
    // Ensure first item is active if none are
    if (carouselItems.length > 0 && !carousel.querySelector('.carousel-item.active')) {
        carouselItems[0].classList.add('active');
    }
}

/**
 * Add an image to a carousel
 * @param {HTMLElement} carousel - The carousel element
 * @param {string} imageSrc - The image source
 * @param {boolean} showRemoveButton - Whether to show a remove button
 * @param {Function} removeCallback - Callback function for remove button
 * @param {string} fileId - Unique identifier for the associated file
 * @returns {HTMLElement} The created carousel item
 */
export function addImageToCarousel(carousel, imageSrc, showRemoveButton = false, removeCallback = null, fileId = null) {
    const carouselInner = carousel.querySelector('.carousel-inner');
    const currentImageCount = carouselInner.querySelectorAll('.carousel-item').length;
    const isFirstUpload = currentImageCount === 0;
    
    // Create carousel item
    const item = document.createElement('div');
    item.className = 'carousel-item' + (isFirstUpload ? ' active' : '');
    
    // Store the file ID as a data attribute if provided
    if (fileId) {
        item.setAttribute('data-file-id', fileId);
    }
    
    const imageContainer = document.createElement('div');
    imageContainer.className = 'carousel-image-container';
    
    // Add remove button if needed
    if (showRemoveButton) {
        const removeBtn = document.createElement('div');
        removeBtn.className = 'remove-image';
        removeBtn.textContent = '×';
        
        removeBtn.addEventListener('click', function(event) {
            event.stopPropagation();
            if (removeCallback) {
                removeCallback(this.closest('.carousel-item'));
            }
        });
        
        imageContainer.appendChild(removeBtn);
    }
    
    // Create image element
    const img = document.createElement('img');
    img.src = imageSrc;
    img.className = 'd-block';
    img.alt = 'Image Preview';
    
    imageContainer.appendChild(img);
    item.appendChild(imageContainer);
    carouselInner.appendChild(item);
    
    // Update carousel controls
    updateCarouselControls(carousel);
    
    return item;
}

/**
 * Remove an image from a carousel
 * @param {HTMLElement} carousel - The carousel element
 * @param {HTMLElement} slideToRemove - The carousel item to remove
 */
export function removeImageFromCarousel(carousel, slideToRemove) {
    const carouselInner = carousel.querySelector('.carousel-inner');
    
    // Check if this is the active slide
    const isActive = slideToRemove.classList.contains('active');
    
    // Remove the slide
    slideToRemove.remove();
    
    // Get updated count of images
    const remainingSlides = carouselInner.querySelectorAll('.carousel-item');
    
    // If we removed the active slide, activate another one
    if (isActive && remainingSlides.length > 0) {
        remainingSlides[0].classList.add('active');
    }
    
    // Update carousel controls visibility
    updateCarouselControls(carousel);
    
    return remainingSlides.length;
}