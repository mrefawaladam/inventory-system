<!-- Chart Section -->
<div class="row">
  <div class="col-lg-12 mb-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-4">Grafik Transaksi (7 Hari Terakhir)</h5>
        <canvas id="transactionChart" height="80"></canvas>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const chartData = @json($chartData);
  
  const ctx = document.getElementById('transactionChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartData.map(item => item.label),
      datasets: [
        {
          label: 'Inbound',
          data: chartData.map(item => item.inbound),
          borderColor: 'rgb(13, 110, 253)',
          backgroundColor: 'rgba(13, 110, 253, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Outbound',
          data: chartData.map(item => item.outbound),
          borderColor: 'rgb(220, 53, 69)',
          backgroundColor: 'rgba(220, 53, 69, 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Transfer',
          data: chartData.map(item => item.transfer),
          borderColor: 'rgb(13, 202, 240)',
          backgroundColor: 'rgba(13, 202, 240, 0.1)',
          tension: 0.4,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          mode: 'index',
          intersect: false,
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
});
</script>
@endpush

