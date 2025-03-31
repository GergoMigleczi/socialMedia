/**
 * Gets the user's current geographic location using the browser's Geolocation API.
 * @returns {Promise<GeolocationPosition>} A promise that resolves with the GeolocationPosition object if successful,
 *                                         or rejects with an error if geolocation is unsupported or permission is denied.
 */
export function getUserLocation() {
    return new Promise((resolve, reject) => {
        // Check if geolocation is supported by the browser
        if (navigator.geolocation) {
            // Request current position - resolves with position or rejects with error
            navigator.geolocation.getCurrentPosition(resolve, reject);
        } else {
            // Reject if geolocation isn't supported
            reject(new Error("Geolocation is not supported by this browser."));
        }
    });
}

/**
 * Reverse geocodes latitude and longitude coordinates into a human-readable address using OpenStreetMap's Nominatim API.
 * @param {number} lat - The latitude coordinate.
 * @param {number} lon - The longitude coordinate.
 * @returns {Promise<string>} A promise that resolves with the formatted address string if found,
 *                            or an empty string if no address is found.
 * @throws {Error} If the API request fails or if there's a network error.
 */
export async function getAddress(lat, lon) {
    // Construct the API URL with provided coordinates
    let url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;

    try {
        // Make the API request
        const response = await fetch(url);

        // Check if response was successful
        if (!response.ok) {
            throw new Error(response.message);
        }

        // Parse the JSON response
        const data = await response.json();

        // Check if address was found in response
        if (data.display_name) {
            console.log("Formatted Address:", data.display_name);
            return data.display_name;
        } else {
            console.log("Address not found.");
            return '';
        }
    } catch (error) {
        // Handle any errors that occurred during the process
        console.error('Error getting address:', error);
        throw error;
    }
}