export async function handleFriendAction(profileId, action) {
    try {
        const response = await fetch('/socialMedia/public/api/friends', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                profileId: profileId,
                action: action
            })
        });
        
        const responseBody = await response.json()
        console.log(responseBody)
        if (!response.ok) {
            throw new Error(responseBody.message);
        }
        
        return responseBody
    } catch (error) {
        alert(error);
        console.error('Error:', error);
    }
}

export async function isFriend(profileId) {
    try {
        const response = await fetch(`/socialMedia/public/api/friends/${profileId}/isFriend`);
        
        const responseBody = await response.json()
        if (!response.ok) {
            throw new Error(responseBody.message);
        }

        if(responseBody['success']){
            return responseBody['isFriend']
        }else{
            alert(response['message'])
            return false
        }
    } catch (error) {
        alert(error);
        console.error('Error:', error);
    }
}