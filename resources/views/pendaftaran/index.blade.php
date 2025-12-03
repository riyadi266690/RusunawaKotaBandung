@extends('layout.master2')

@section('content')
<style>
  .card-lokasi-container > .col-md-4:nth-child(n+4) {
    /* Terapkan margin-top yang diinginkan */
    margin-top: 1.5rem !important; /* Nilai 1.5rem setara dengan mt-4 di Bootstrap */
}
</style>

<div class="row">
  <div class="col-md-12">
    <h2 class="text-center mb-3 mt-4">Pilih Lokasi Rusunawa Anda</h2>
    <p class="text-muted text-center mb-4 pb-2">Temukan lokasi Rusunawa yang sesuai dengan minat dan kebutuhan anda.</p>
    <div class="container">  
      <div class="row card-lokasi-container">
        
        @forEach($lokasi as $lok)
        <div class="col-md-4 stretch-card grid-margin grid-margin-md-0">
          <div class="card">
            <div class="card-body">
              <h4 class="text-center mt-3 mb-4">{{ $lok->nama_lokasi }}</h4>
              <i data-feather="home" class="text-success icon-xxl d-block mx-auto my-3"></i>
              <p class="text-muted text-center mb-0">mulai dari</p>
              <h1 class="text-center">{{ $lok->mulai_dari }}</h1>
              <p class="text-muted text-center mb-4 fw-light">per bulan</p>
              <h5 class="text-success text-center mb-4">Tersedia {{ $lok->unit_available_count }} Unit</h5>
              <table class="mx-auto">
                <tr>
                  <td><i data-feather="check" class="icon-md text-primary me-2"></i></td>
                  <td><p>Area Terbuka Hijau</p></td>
                </tr>
                <tr>
                  <td><i data-feather="check" class="icon-md text-primary me-2"></i></td>
                  <td><p>Aula</p></td>
                </tr>
                <tr>
                  <td><i data-feather="check" class="icon-md text-primary me-2"></i></td>
                  <td><p>Sarana Olah Raga</p></td>
                </tr>
                <tr>
                  <td><i data-feather="check" class="icon-md text-primary me-2"></i></td>
                  <td><p>Keamanan 24Jam</p></td>
                </tr>
                <tr>
                  <td><i data-feather="check" class="icon-md text-primary me-2"></i></td>
                  <td><p>Bebas Biaya Pendaftaran</p></td>
                </tr>
              </table>
              <div class="d-grid">
                <button 
                    type="button" 
                    class="btn btn-success mt-4 btn-daftar-lokasi" 
                    data-bs-toggle="modal" 
                    data-bs-target="#registrationModal" 
                    data-lokasi-id="{{ $lok->id }}"
                    data-lokasi-nama="{{ $lok->nama_lokasi }}"
                    data-link-formulir="{{ $lok->link_formulir }}"> {{-- BARIS BARU --}}
                    Daftar Sekarang
                </button>
              </div>
            </div>
          </div>
        </div>
        @endforEach

      </div>
    </div>
  </div>
</div>
<!------------ MODAL FORM PENDAFTARAN -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="registrationModalLabel">Form Pendaftaran Lokasi: <span id="lokasiNamaDisplay"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form class="forms-sample form-adi" action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            @csrf
            
            {{-- INPUT TERSEMBUNYI UNTUK LOKASI_ID --}}
            <input type="hidden" id="lokasi_id_input" name="lokasi_id" required>
            
            <div class="mb-3">
              <label for="nama" class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" id="nama" name="nama" placeholder="nama lengkap" required>
            </div>
            <div class="mb-3">
              <label for="telp_pendaftar" class="form-label">No Telp / WhatsApp</label>
              <input type="text" class="form-control" id="telp_pendaftar" name="telp_pendaftar" placeholder="No Telp / WhatsApp" required>
            </div>
            <div class="mb-3">
              <label for="suket" class="form-label">Unggah Formulir Pendaftaran (Hanya PDF)</label>
              <input type="file" class="form-control" id="suket" name="suket" placeholder="Pilih File" required accept=".pdf">
            </div>
            
            <h5 class="text-muted fw-normal mb-4">
                Unduh Formulir Pendaftaran: 
                <a href="#" id="linkFormulirDownload" target="_blank"> 
                    Unduh Formulir
                </a>
            </h5>
            
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Kirim Pendaftaran</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var registrationModal = document.getElementById('registrationModal');
        
        if (registrationModal) {
            registrationModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                
                // Ambil nilai dari atribut data tombol
                var lokasiId = button.getAttribute('data-lokasi-id');
                var lokasiNama = button.getAttribute('data-lokasi-nama');
                var linkFormulir = button.getAttribute('data-link-formulir'); // BARIS BARU

                // Dapatkan elemen di dalam modal
                var modalInput = registrationModal.querySelector('#lokasi_id_input');
                var modalTitleDisplay = registrationModal.querySelector('#lokasiNamaDisplay');
                var linkDownload = registrationModal.querySelector('#linkFormulirDownload'); // BARIS BARU

                // Isi ID Lokasi
                if (modalInput) {
                    modalInput.value = lokasiId;
                }
                
                // Isi Judul
                if (modalTitleDisplay) {
                    modalTitleDisplay.textContent = lokasiNama;
                }

                // ISI LINK FORMULIR BARU
                if (linkDownload) {
                    linkDownload.href = linkFormulir || '#'; // Set link, jika null set ke #
                }
            });
        }
    });
</script>
@endsection