// Function to fetch statistics
export async function fetchPostStatistics(profileId, period) {
    try {
        console.log(`/socialMedia/Admin/api/postStatistics?profileId=${profileId}&period=${period}`)
        const response = await fetch(`/socialMedia/Admin/api/postStatistics?profileId=${profileId}&period=${period}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching post statistics:', error);
        return [];
    }
}