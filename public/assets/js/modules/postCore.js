export async function createPost(formData) {
    try {
        const response = await fetch('/socialMedia/public/api/posts', {
            method: 'POST',
            body: formData
        });
        
        try{
            return await response.json();
        }catch (error) {
            console.error('Error returning response.json():', error);
            throw error
        }
    } catch (error) {
        console.error('Error submitting post:', error);
        throw error;
    }
}