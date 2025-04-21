import {fetchPostStatistics} from '../modules/postCore.js';

/**
 * Fetch post statistics for a specific profile and time period
 * Then display the results in a chart
 */
document.addEventListener('DOMContentLoaded', function() {    
    // Set up event listener for dropdown changes
    document.getElementById('postActivityRange').addEventListener('change', updateChart);
    
    var postActivityChart = null;
    // Initial chart load
    updateChart();


    // Function to display the data in a chart
    function displayPostChart(data, period, unit) {
        console.log(data)

        // If we already have a chart, destroy it
        if (postActivityChart) {
            postActivityChart.destroy();
        }
        
        const datasets = buildPostDatasets(data);
        // Create the chart
        const ctx = document.getElementById('postActivityChart').getContext('2d');
        postActivityChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: unit, // Group data by month
                        },
                        title: {
                            display: true,
                            text: getPeriodLabel(period)
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Posts Count'
                        }
                    }
                }
            }
        });
    }

    // Helper function to get readable period label
    function getPeriodLabel(period) {
        switch(period) {
            case 'week': return 'Last 7 Days';
            case 'month': return 'Last 30 Days';
            case '6months': return 'Last 6 Months';
            case '1year': return 'Last Year';
            case '5years': return 'Last 5 Years';
            default: return period;
        }
    }

    // Function to update chart when selection changes
    async function updateChart() {
        const profileId = document.getElementById('postActivityRange').getAttribute('data-profileId');
        const period = document.getElementById('postActivityRange').value;

        // Fetch data
        const data = await fetchPostStatistics(profileId, period);

        // Display data
        if (data['success']) {
            displayPostChart(JSON.parse(data['postStatistics']), period, data['unit']);
            document.getElementById('total-number-of-posts').innerText = data['total'];
        } else {
            // Handle no data
            if (postActivityChart) {
                postActivityChart.destroy();
                postActivityChart = null;
            }
        }
    }

    function buildPostDatasets(data) {
        const keys = Object.keys(data[0]).filter(key => key !== 'date'); // e.g., ['All Posts', 'public', 'friends', 'private']

        return keys.map(key => ({
            label: convertToTitleCase(key),
            data: data.map(item => ({ x: item.date, y: item[key] })),
            borderColor: getRandomColor(key),
            borderWidth: 2,
            fill: false,
            tension: 0.1
        }));
    }
    
});

function convertToTitleCase(str) {
    if (!str) {
        return ""
    }
    return str.toLowerCase().replace(/\b\w/g, s => s.toUpperCase());
}

// Utility to generate a random RGB color
function getRandomColor(key) {
    const colors = {'all posts': 'rgb(128,128,128)',
        'public': 'rgb(78, 132, 248)',
        'friends': 'rgb(106, 246, 113)',
        'private': 'rgb(241, 109, 109)',
    }
    if(key.toLowerCase() in colors){
        return colors[key.toLowerCase()];
    } 
    const r = Math.floor(100 + Math.random() * 155);
    const g = Math.floor(100 + Math.random() * 155);
    const b = Math.floor(100 + Math.random() * 155);
    return `rgb(${r}, ${g}, ${b})`;
}