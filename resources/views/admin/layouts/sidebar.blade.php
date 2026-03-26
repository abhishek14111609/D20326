<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
<div class="app-brand demo mt-5 mb-5 d-flex align-items-center justify-content-between">
  <!-- Logo + Text -->
  <a href="{{ route('admin.dashboard') }}" class="app-brand-link d-flex flex-column align-items-center w-100">
    <span class="app-brand-logo demo">
      <img src="https://duos.webvibeinfotech.in/public/assets/img/favicon/only_logo1.png" alt="Logo" style="height: 100px;">
    </span>
    <span class="app-brand-text demo menu-text fw-bold mt-1"></span> <!-- Optional text -->
  </a>

  <!-- Menu Toggle Icon (optional on large screens) -->
  <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto position-absolute end-0 me-3">
    <i class="icon-base bx bx-chevron-left"></i>
  </a>
</div>


  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Dashboard -->
    <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active open' : '' }}">
      <a href="{{ route('admin.dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div data-i18n="Dashboard">Dashboard</div>
      </a>
    </li>

    <!-- Main Menu -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Main Menu</span>
    </li>

    <!-- Chats -->
    <!-- <li class="menu-item {{ request()->routeIs('admin.chats*') ? 'active open' : '' }}">
      <a href="{{ route('admin.chats.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-chat"></i>
        <div data-i18n="Chats">Chats</div>
      </a>
    </li> -->
	  
	 <!-- Challenges & Competitions -->
    <li class="menu-item {{ request()->is('admin/challenges*') || request()->is('admin/competitions*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-medal"></i>
        <div data-i18n="Challenges">Challenges & Competitions</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->is('admin/challenges') || request()->is('admin/challenges/index') ? 'active' : '' }}">
          <a href="{{ url('admin/challenges') }}" class="menu-link">
            <div>All Challenges</div>
          </a>
        </li>
        <li class="menu-item {{ request()->is('admin/challenges/create') ? 'active' : '' }}">
          <a href="{{ url('admin/challenges/create') }}" class="menu-link">
            <div>Create New</div>
          </a>
        </li>
        <li class="menu-item {{ request()->is('admin/competitions*') ? 'active' : '' }}">
          <a href="{{ url('admin/competitions') }}" class="menu-link">
            <div>Competitions</div>
          </a>
        </li>
		<li class="menu-item {{ request()->routeIs('admin.competitions.quizzes.*') ? 'active' : '' }}">
			<a href="{{ route('admin.competitions.quizzes.index', ['competition' => 1]) }}" class="menu-link">
				<div>Quiz Management</div>
			</a>
		</li>
      </ul>
    </li>
	
	  
	 <!-- Gifts -->
    <li class="menu-item {{ request()->routeIs('admin.gifts*') ? 'active open' : '' }}">
      <a href="{{ route('admin.gifts.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-gift"></i>
        <div data-i18n="Gifts">Gifts</div>
      </a>
    </li>
	
	 <!-- Leaderboard -->
    <li class="menu-item {{ request()->routeIs('admin.leaderboard*') ? 'active open' : '' }}">
      <a href="{{ route('admin.leaderboard.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-trophy"></i>
        <div data-i18n="Leaderboard">Leaderboard</div>
      </a>
    </li>
	  
    <!-- Memberships -->
    <li class="menu-item {{ request()->routeIs('admin.memberships*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-crown"></i>
        <div data-i18n="Memberships">Memberships</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('admin.memberships.index') ? 'active' : '' }}">
          <a href="{{ route('admin.memberships.index') }}" class="menu-link">
            <div data-i18n="Plans">Plans</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.memberships.user-purchases') ? 'active' : '' }}">
          <a href="{{ route('admin.memberships.user-purchases') }}" class="menu-link">
            <div data-i18n="User Purchases">User Purchases</div>
          </a>
        </li>
      </ul>
    </li>
	  
	 <!-- Payments -->
    <li class="menu-item {{ request()->routeIs('admin.payments*') ? 'active open' : '' }}">
      <a href="{{ route('admin.payments.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-credit-card"></i>
        <div data-i18n="Payments">Payments</div>
      </a>
    </li>

    <!-- Reports -->
    <li class="menu-item {{ request()->routeIs('admin.reports*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-flag"></i>
        <div data-i18n="Reports">Reports</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('admin.reports.users') ? 'active' : '' }}">
          <a href="{{ route('admin.reports.users') }}" class="menu-link">
            <div data-i18n="User Reports">User Reports</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.reports.system') ? 'active' : '' }}">
          <a href="{{ route('admin.reports.system') }}" class="menu-link">
            <div data-i18n="System Reports">System Reports</div>
          </a>
        </li>
      </ul>
    </li>

	  
    <!-- Swipe -->
    <li class="menu-item {{ request()->routeIs('admin.swipes*') ? 'active open' : '' }}">
      <a href="{{ route('admin.swipes.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-swim"></i>
        <div data-i18n="Swipe">Swipe</div>
      </a>
    </li>
	  
	<!-- Users -->
    <li class="menu-item {{ request()->routeIs('admin.users*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-user"></i>
        <div data-i18n="Users">Users</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
          <a href="{{ route('admin.users.index') }}" class="menu-link">
            <div data-i18n="List">List</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Settings -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Settings</span>
    </li>
    
    <li class="menu-item">
      <a href="{{ route('admin.settings.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div data-i18n="Settings">Settings</div>
      </a>
    </li>
	  

    <!-- Account -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bxs-user-account"></i>
        <div data-i18n="Account">Account</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="{{ route('admin.profile') }}" class="menu-link">
            <div data-i18n="Profile">Profile</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="{{ route('admin.account.settings') }}" class="menu-link">
            <div data-i18n="Account Settings">Account Settings</div>
          </a>
        </li>
		  
		<li class="menu-item {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
		 <a href="{{ route('admin.users.create') }}" class="menu-link">
			 <div data-i18n="Add New">Add Admin</div>
		 </a>
	  </li>
      </ul>
    </li>

    <!-- Logout -->
    <li class="menu-item">
      <a href="{{ route('admin.logout') }}" 
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
         class="menu-link">
        <i class="menu-icon tf-icons bx bx-log-out"></i>
        <div data-i18n="Logout">Logout</div>
      </a>
      <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
        @csrf
      </form>
    </li>
  </ul>
</aside>
