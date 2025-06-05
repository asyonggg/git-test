// Sidebar toggle functionality
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const logoContainer = document.getElementById('logoContainer');
const menuTexts = document.querySelectorAll('.menu-text');
const userInfo = document.getElementById('userInfo');
const quickActionsHeader = document.getElementById('quickActionsHeader');

let sidebarCollapsed = false;

sidebarToggle.addEventListener('click', function() {
    sidebarCollapsed = !sidebarCollapsed;
    
    if (sidebarCollapsed) {
        sidebar.classList.remove('sidebar-expanded');
        sidebar.classList.add('sidebar-collapsed');
        
        // Hide text elements
        menuTexts.forEach(text => {
            text.style.opacity = '0';
            setTimeout(() => {
                text.style.display = 'none';
            }, 150);
        });
        
        // Hide logo
        logoContainer.style.opacity = '0';
        setTimeout(() => {
            logoContainer.style.display = 'none';
        }, 150);
        
    } else {
        sidebar.classList.remove('sidebar-collapsed');
        sidebar.classList.add('sidebar-expanded');
        
        // Show text elements
        setTimeout(() => {
            menuTexts.forEach(text => {
                text.style.display = 'block';
                setTimeout(() => {
                    text.style.opacity = '1';
                }, 50);
            });
            
            // Show logo
            logoContainer.style.display = 'flex';
            setTimeout(() => {
                logoContainer.style.opacity = '1';
            }, 50);
        }, 150);
    }
});

// Charts initialization
function createCharts() {
    // Sentiment Pie Chart
    const sentimentCtx = document.getElementById('sentimentChart').getContext('2d');
    new Chart(sentimentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                data: [68, 20, 12],
                backgroundColor: ['#10b981', '#9ca3af', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '70%'
        }
    });
    
    // Satisfaction Trends Line Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Satisfaction Score',
                data: [4.1, 4.3, 4.0, 4.5, 4.2, 4.4, 4.2],
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#f59e0b',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 3.5,
                    max: 5,
                    grid: {
                        color: '#f3f4f6'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', createCharts);

// Handle window resize
window.addEventListener('resize', function() {
    Chart.instances.forEach(chart => {
        chart.resize();
    });
});