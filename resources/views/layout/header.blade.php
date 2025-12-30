<div class="horizontal-menu">
  <nav class="navbar top-navbar">
    <div class="container">
      <div class="navbar-content">
        <a href="#" class="navbar-brand">
          SiRAJA BALAREA<span>- Sistem Informasi Rusun Jawa Barat Keur Balarea</span>
        </a>
        
        <ul class="navbar-nav">
          
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img class="wd-30 ht-30 rounded-circle" src="{{ asset('assets/images/others/user.png') }}" alt="profile">
            </a>
            <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
              <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                <div class="mb-3">
                  <img class="wd-80 ht-80 rounded-circle" src="{{ asset('assets/images/others/user.png') }}" alt="">
                </div>
                <div class="text-center">
                  <p class="tx-16 fw-bolder">{{ Auth::user()->name }}</p>
                  <p class="tx-12 text-muted">{{ Auth::user()->email }}</p>
                </div>
              </div>
              <ul class="list-unstyled p-1">                
                <li class="dropdown-item py-2">
                  <a href="{{ route('auth.password.form') }}" class="text-body ms-0">
                    <i class="me-2 icon-md" data-feather="repeat"></i>
                    <span>Ubah Password</span>
                  </a>
                </li>
                <li class="dropdown-item py-2">
                    <form action="{{ route('auth.logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-link text-body p-0 m-0">
                        <i class="me-2 icon-md" data-feather="log-out"></i>
                        <span>Log Out</span>
                        </button>
                    </form>
                </li>
              </ul>
            </div>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="horizontal-menu-toggle">
          <i data-feather="menu"></i>					
        </button>
      </div>
    </div>
  </nav>
  <nav class="bottom-navbar">
    <div class="container">
      <ul class="nav page-navigation">
        <li class="nav-item  {{ active_class(['dashboard*']) }}">
          <a class="nav-link" href="{{ route('dashboard.index') }}">
            <i class="link-icon" data-feather="box"></i>
            <span class="menu-title">Dashboard</span>            
          </a>
        </li>
        <li class="nav-item  {{ active_class(['pendaftar*']) }}">
          <a class="nav-link" href="{{ route('pendaftar.index') }}">
            <i class="link-icon" data-feather="user-plus"></i>
            <span class="menu-title">Pendaftar</span>            
          </a>
        </li>

      
     
        <li class="nav-item {{ active_class(['penghuni*']) }}">
          <a class="nav-link" href="{{ route('penghuni.index') }}" >
            <i class="link-icon" data-feather="users"></i>
            <span class="menu-title">Penghuni</span></a>
        </li>

        <li class="nav-item {{ active_class(['kontrak/*']) }}">
          <a href="#" class="nav-link">
            <i class="link-icon" data-feather="file-text"></i>
            <span class="menu-title">Kontrak</span>
            <i class="link-arrow"></i>
          </a>
          <div class="submenu">
            <ul class="submenu-item">
              <li class="nav-item"><a href="{{ route('kontrak.aktif') }}" class="nav-link {{ active_class(['kontrak/aktif']) }}">Aktif</a></li>
              <li class="nav-item"><a href="{{ route('kontrak.non_aktif') }}" class="nav-link {{ active_class(['kontrak/non_aktif']) }}">Non AKtif</a></li>              
            </ul>
          </div>
        </li>



        <li class="nav-item {{ active_class(['pengaturan*']) }}">
          <a class="nav-link" href="{{ route('pengaturan.index') }}" >
            <i class="link-icon" data-feather="tool"></i>
            <span class="menu-title">Pengaturan</span></a>
        </li>
       
      </ul>
    </div>
  </nav>
</div>