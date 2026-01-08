document.querySelectorAll('.card').forEach(card => {
    card.style.cursor = 'pointer';
    card.addEventListener('click', () => {
        const url = card.getAttribute('data-href');
        if (url) {
             window.location.href = url;
        }
    });
});

    // charts.js

function initCharts(mostViewedData, mostViewedLabels, totalDocs) {
    const ctx = document.getElementById('mostViewedPieChart').getContext('2d');

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: mostViewedLabels,
            datasets: [{
                data: mostViewedData,
                backgroundColor: [
                    'rgba(30, 90, 148, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(30, 90, 148, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' views';
                        }
                    }
                }
            }
        }
    });

    const ctx2 = document.getElementById('documentsDonutChart').getContext('2d');

    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['TRBA', 'SFR', 'COPC'],
            datasets: [{
                label: 'Total Documents',
                data: totalDocs,
                backgroundColor: ['#1E5A94', '#28a745', '#ffc107'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#000', font: { size: 14 } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}
