/**
 * Dashboard Chart Analytics (Chart.js Integration)
 */

function initAdminDashboardCharts(deptLabels, deptData, monthlyLabels, monthlyData) {
  // Department-wise Placements Chart (Doughnut)
  const deptCtx = document.getElementById('deptPlacementChart');
  if (deptCtx) {
    new Chart(deptCtx, {
      type: 'doughnut',
      data: {
        labels: deptLabels || ['CSE', 'IT', 'ECE', 'ME', 'CE'],
        datasets: [{
          data: deptData || [140, 110, 75, 40, 20],
          backgroundColor: ['#0d6efd', '#06b6d4', '#8b5cf6', '#f59e0b', '#10b981'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }

  // Monthly Applications & Offers Chart (Bar / Line)
  const monthlyCtx = document.getElementById('monthlyPlacementChart');
  if (monthlyCtx) {
    new Chart(monthlyCtx, {
      type: 'bar',
      data: {
        labels: monthlyLabels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Offers Generated',
          data: monthlyData || [25, 45, 80, 120, 95, 45],
          backgroundColor: 'rgba(13, 110, 253, 0.85)',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }
}
