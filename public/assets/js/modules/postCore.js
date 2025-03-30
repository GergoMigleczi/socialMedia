export async function sendPostRequest(formData) {
    console.log(formData)

    try {
        const response = await fetch('/socialMedia/public/api/posts', {
            method: 'POST',
            body: formData
        });
        
        try{
            return await response.json();
        }catch (error) {
            console.error('Error returning respponse.json():', error);
            return { success: false, message: error.message };
        }
    } catch (error) {
        alert(error);
        console.error('Error submitting post:', error);
        return { success: false, message: error.message };
    }
}