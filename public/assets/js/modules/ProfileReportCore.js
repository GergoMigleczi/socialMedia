export async function reportProfile(profileId, reason, details = '') {
    try {
        const response = await fetch('/socialMedia/public/api/profiles/report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                profileId: profileId,
                reason: reason,
                details: details
            })
        });
        
        const responseBody = await response.json();
        
        if (!response.ok) {
            throw new Error(responseBody.message);
        }

        return responseBody;
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}
