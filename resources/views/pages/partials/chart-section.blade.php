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
    type: 'bar',
    data: {
      labels: chartData.map(item => item.label),
      datasets: [
        {
          label: 'Inbound',
          data: chartData.map(item => item.inbound),
          backgroundColor: 'rgba(13, 110, 253, 0.8)',
          borderColor: 'rgb(13, 110, 253)',
          borderWidth: 1
        },
        {
          label: 'Outbound',
          data: chartData.map(item => item.outbound),
          backgroundColor: 'rgba(220, 53, 69, 0.8)',
          borderColor: 'rgb(220, 53, 69)',
          borderWidth: 1
        },
        {
          label: 'Transfer',
          data: chartData.map(item => item.transfer),
          backgroundColor: 'rgba(13, 202, 240, 0.8)',
          borderColor: 'rgb(13, 202, 240)',
          borderWidth: 1
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

