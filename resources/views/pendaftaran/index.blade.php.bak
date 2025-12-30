@extends('layout.master2')

@section('content')
<div class="page-content d-flex align-items-center justify-content-center">

  <div class="row w-100 mx-0 auth-page">
    <div class="col-md-8 col-xl-6 mx-auto">
      <div class="card">
        <div class="row">
          <div class="col-md-4 pe-md-0">
            <div class="auth-side-wrapper" style="background-image: url({{ asset('assets/images/others/register2.png') }})">

            </div>
          </div>
          <div class="col-md-8 ps-md-0">
            <div class="auth-form-wrapper px-4 py-5">
              <a href="/" class="noble-ui-logo d-block mb-2">UPTD<span> Rumah Susun</span></a>              
              <h5 class="text-muted fw-normal mb-4">Daftar Sekarang <a href="https://docs.google.com/document/d/12vYXRhLUkAftabiTMso2kO8nZd3IQyMw/export?format=docx">Unduh Formulir Pendaftaran</a></h5>
              <form class="forms-sample form-adi" action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                  <label for="nama" class="form-label">Nama Lengkap</label>
                  <input type="text" class="form-control" id="nama" name="nama" placeholder="nama lengkap" required>
                </div>
                <div class="mb-3">
                  <label for="telp_pendaftar" class="form-label">No Telp / WhatsApp</label>
                  <input type="text" class="form-control" id="telp_pendaftar" name="telp_pendaftar" placeholder="No Telp / WhatsApp" required>
                </div>
                <div class="mb-3">
                  <label for="suket" class="form-label">Unggah Formulir Pendaftaran
                  
                  </label>
                  <input type="file" class="form-control" id="suket" name="suket" placeholder="Pilih File" required accept=".pdf">
                </div>
                
                <div>
                    <button type="submit" class="btn btn-success me-2 mb-2 mb-md-0 button-adi">Kirim</button>
                </div>
                <a href="{{ route('auth.login') }}" class="d-block mt-3 text-muted">Sudah punya akun? Log In</a>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection