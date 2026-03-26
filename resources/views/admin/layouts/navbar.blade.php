<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="icon-base bx bx-menu icon-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center">
      <div class="nav-item d-flex align-items-center">
        <i class="bx bx-search fs-4 lh-0"></i>
        <div class="input-group input-group-merge">
          <input
            type="text"
            class="form-control border-0 shadow-none"
            placeholder="Search..."
            aria-label="Search..."
            id="navbarSearchInput"
            data-bs-toggle="modal"
            data-bs-target="#searchModal"
            style="width: 150px;"
          >
        </div>
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">
      <!-- Place this tag where you want the button to render. -->
      <!-- <li class="nav-item lh-1 me-4">
        <a
          class="github-button"
          href="https://github.com/themeselection/sneat-bootstrap-html-admin-template-free"
          data-icon="octicon-star"
          data-size="large"
          data-show-count="true"
          aria-label="Star themeselection/sneat-html-admin-template-free on GitHub"
          >Star</a
        >
      </li> -->

      <!-- User -->
      <li class="nav-item dropdown">
        <a
          class="nav-link dropdown-toggle hide-arrow p-0"
          href="javascript:void(0);"
          id="navbarDropdownUser"
          role="button"
          data-bs-toggle="dropdown"
          aria-expanded="false"
          data-bs-auto-close="outside"
          data-bs-offset="0,10">
          <div class="avatar avatar-online">
            <img src="{{ Auth::guard('admin')->user()->avatar ?? 'https://duos.webvibeinfotech.in/public/assets/img/avatars/1.png' }}" alt class="w-px-40 h-auto rounded-circle" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0" aria-labelledby="navbarDropdownUser">
          <li class="dropdown-header">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div class="avatar">
                  <img src="{{ Auth::guard('admin')->user()->avatar ?? 'https://duos.webvibeinfotech.in/public/assets/img/avatars/1.png' }}" alt class="w-px-40 h-auto rounded-circle" />
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ Auth::guard('admin')->user()->name }}</h6>
                <small class="text-muted">
                  @php
                    $roles = Auth::guard('admin')->user()->getRoleNames();
                    echo $roles->isNotEmpty() ? $roles->first() : 'Admin';
                  @endphp
                </small>
              </div>
            </div>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
              <i class="bx bx-home me-2"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.users.index') }}">
              <i class="bx bx-user me-2"></i>
              <span>Users</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.competitions.index') }}">
              <i class="bx bx-trophy me-2"></i>
              <span>Competitions</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.challenges.index') }}">
              <i class="bx bx-award me-2"></i>
              <span>Challenges</span>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.profile') }}">
              <i class="bx bx-user me-2"></i>
              <span>My Profile</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
              <i class="bx bx-cog me-2"></i>
              <span>Settings</span>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item" href="{{ route('admin.logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="bx bx-power-off me-2"></i>
              <span>Logout</span>
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
              @csrf
            </form>
          </li>
        </ul>
      </li>
      <!--/ User -->
    </ul>
  </div>
</nav>

@include('admin.components.search-modal')
