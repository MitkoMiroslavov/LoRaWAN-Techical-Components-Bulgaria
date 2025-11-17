document.addEventListener("DOMContentLoaded", function () {
    fetch("get_weekly_temp_humidity.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            
            const labels = data.map(entry => entry.date);
            const humidityValues = data.map(entry => parseFloat(entry.avg_humidity));
            
            
            
            const humidityCtx = document.getElementById("humidityChart").getContext("2d");
            new Chart(humidityCtx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Avg Humidity (%)",
                        data: humidityValues,
                        borderColor: "blue",
                        backgroundColor: "rgba(0, 0, 255, 0.2)",
                        pointRadius: 4, // Make the dots larger
                        pointHitRadius: 10, // Increase the hit radius for better hover interaction
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        tooltip: {
                            enabled: true,
                            mode: 'nearest', // Show tooltip for the nearest point
                            intersect: false // Show tooltip even if not directly hovering over the point
                        }
                    }
                }
                
            });
        })
        .catch(error => console.error("Error fetching data:", error));
});
