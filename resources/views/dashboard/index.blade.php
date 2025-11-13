@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <h4 class="mb-3 mb-md-0">Welcome to Dashboard</h4>
  </div>
  
</div>
<div class="row">
  <div class="col-12 col-xl-12 stretch-card">
    <div class="row flex-grow-1">
      <!-- Menggunakan perulangan untuk menampilkan setiap lokasi -->
      @foreach($dashboardData as $data)
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-baseline">
              <!-- Nama Lokasi dari database -->
              <h6 class="card-title mb-0">{{ $data->lokasi_nama }}</h6>              
            </div>
            <div class="row">
              <div class="col-6 col-md-12 col-xl-12">
                <!-- Total unit terhuni dari database -->
                <h3 class="mb-5">{{ $data->total_terkontrak }} / {{ $data->total_unit }}</h3>
                <div class="d-flex align-items-baseline">
                  <!-- Persentase terhuni dari database -->
                  <p class="text-success">
                    <span>{{ $data->persentase_terhuni }}%</span>
                    <i class="icon-sm mb-1"></i>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
      
    </div>
  </div>
</div> <!-- row -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Perkiraan Penerimaan Sewa</h6>
                <p class="text-muted mb-3">Grafik ini menampilkan proyeksi pendapatan dari semua kontrak aktif.</p>
                <div class="chartjs-wrapper">
                    <canvas id="forecastChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('plugin-scripts')
  <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/apexcharts/apexcharts.min.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('custom-scripts')
  <script src="{{ asset('assets/js/dashboard.js') }}"></script>
  <script>
    // Ambil data prakiraan dari controller
    var forecastData = @json($forecastData);

    // Dapatkan konteks canvas
    var ctx = document.getElementById('forecastChart').getContext('2d');

    // Buat grafik
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: forecastData.labels,
            datasets: [{
                label: 'Perkiraan Penerimaan (Rp)',
                data: forecastData.data,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Pendapatan (Rp)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Bulan'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush