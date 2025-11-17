document.addEventListener("DOMContentLoaded", function () {
    fetch("get_weekly_temp_humidity.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            
            const labels = data.map(entry => entry.date);
            const tempValues = data.map(entry => parseFloat(entry.avg_temp));
            const humidityValues = data.map(entry => parseFloat(entry.avg_humidity));
            
            const tempCtx = document.getElementById("temperatureChart").getContext("2d");
            new Chart(tempCtx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Avg Temperature (°C)",
                        data: tempValues,
                        borderColor: "red",
                        pointRadius: 4, // Make the dots larger
                        pointHitRadius: 10, // Increase the hit radius for better hover interaction
                        backgroundColor: "rgba(255, 0, 0, 0.2)",
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