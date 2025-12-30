@extends('layout.master2')

@section('content')
<div class="page-content d-flex align-items-center justify-content-center">

  <div class="row w-100 mx-0 auth-page">
    <div class="col-md-8 col-xl-6 mx-auto">
      <div class="card">
        <div class="row">
          <div class="col-md-4 pe-md-0">
            <div class="auth-side-wrapper" style="background-image: url({{ asset('assets/images/others/siraja.png') }})">

            </div>
          </div>
          <div class="col-md-8 ps-md-0">
            <div class="auth-form-wrapper px-4 py-5">
              <a href="#" class="noble-ui-logo d-block mb-2">SiRAJA<span> BALAREA</span></a>
              <h5 class="text-muted fw-normal mb-4">Hunian Nyaman dan Berkelanjutan</h5>
              <form class="forms-sample form-adi" method="POST" action="{{ route('auth.authenticate') }}">
              @csrf
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>

                <div>
                    <button type="submit" class="btn btn-success me-2 mb-2 mb-md-0 button-adi">Masuk</button>
                </div>
                <a href="{{ route('pendaftaran.index') }}" class="d-block mt-3 text-muted">Belum Punya Akun? Daftar disini</a>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
                
@endsection