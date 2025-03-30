export function getUserLocation() {
    return new Promise((resolve, reject) => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(resolve, reject);
        } else {
            reject(new Error("Geolocation is not supported by this browser."));
        }
    });
}

export async function getAddress(lat, lon) {
    let url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;

    try{
        const response = await fetch(url);

        if(!response.ok){
            throw new Error(response.message)
        }

        const data = await response.json()

        if (data.display_name) {
            console.log("Formatted Address:", data.display_name);
            return data.display_name;
        } else {
            console.log("Address not found.");
            return '';
        }
    }catch (error) {
      alert(error.message);
      console.error('Error getting address:', error);
    } 
}
