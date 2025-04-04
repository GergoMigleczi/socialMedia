// Execute when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {    
    // Add event listener to the search button
    document.getElementById('users-search-form').addEventListener('submit', handleSearchAndSort);

    // Add event listener to the sort selection dropdown
    document.getElementById('users-sort-selection').addEventListener('change', handleSearchAndSort);
});

// Function to handle search and sort
function handleSearchAndSort(e) {
    e.preventDefault()
    // Get the search value from the input
    const searchValue = document.getElementById('users-search-input').value.trim();
    
    // Get the sort value from the select dropdown
    const sortValue = document.getElementById('users-sort-selection').value;
    
    // Construct the URL with the search and sort parameters
    const url = `/socialMedia/admin/users?search=${encodeURIComponent(searchValue)}&sort=${encodeURIComponent(sortValue)}`;
    
    // Redirect to the constructed URL
    window.location.href = url;
}

