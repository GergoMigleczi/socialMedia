export async function blockProfile(profileId) {
    try {
        const response = await fetch('/socialMedia/public/api/profiles/block', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                profileId: profileId
            })
        });
        
        const responseBody = await response.json();
        if (!response.ok) {
            throw new Error(responseBody.message);
        }
        
        return responseBody;
    } catch (error) {
        alert(error);
        console.error('Error:', error);
        throw error;
    }
}

export async function unblockProfile(profileId) {
    try {
        const response = await fetch('/socialMedia/public/api/profiles/unblock', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                profileId: profileId
            })
        });
        
        const responseBody = await response.json();
        if (!response.ok) {
            throw new Error(responseBody.message);
        }
        
        return responseBody;
    } catch (error) {
        alert(error);
        console.error('Error:', error);
        throw error;
    }
}