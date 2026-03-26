@extends('admin.layouts.app')

@section('title', 'Admin Profile')

@section('content')
<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Admin /</span> Profile
  </h4>

  <div class="row">
    <!-- User Sidebar -->
    <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
      <!-- User Card -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="user-avatar-section">
            <div class="d-flex align-items-center flex-column">
              <div class="user-avatar-wrapper">
                <div class="avatar avatar-xl">
                  <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($admin->name, 0, 1) }}</span>
                </div>
              </div>
              <div class="user-info text-center mt-3">
                <h4 class="mb-1">{{ $admin->name }}</h4>
                <span class="badge bg-label-{{ $admin->role == 'super_admin' ? 'danger' : ($admin->role == 'admin' ? 'success' : 'info' ) }} mb-1">
                  {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                </span>
                <div class="d-flex align-items-center justify-content-center mt-2">
                  <i class='bx bx-envelope me-1'></i>
                  <span class="text-muted">{{ $admin->email }}</span>
                </div>
                @if($admin->phone)
                <div class="d-flex align-items-center justify-content-center">
                  <i class='bx bx-phone me-1'></i>
                  <span class="text-muted">{{ $admin->phone }}</span>
                </div>
                @endif
              </div>
            </div>
          </div>
          
          <div class="d-flex justify-content-around my-4 pt-3">
            <div class="d-flex align-items-center">
              <div class="avatar">
                <div class="avatar-initial bg-label-primary rounded">
                  <i class='bx bx-calendar'></i>
                </div>
              </div>
              <div class="ms-3">
                <div class="text-muted">Joined</div>
                <h6 class="mb-0">{{ $admin->created_at->format('M Y') }}</h6>
              </div>
            </div>
            <div class="d-flex align-items-center">
              <div class="avatar">
                <div class="avatar-initial bg-label-{{ $admin->status == 'active' ? 'success' : 'danger' }} rounded">
                  <i class='bx bx-{{ $admin->status == 'active' ? 'check' : 'x' }}'></i>
                </div>
              </div>
              <div class="ms-3">
                <div class="text-muted">Status</div>
                <h6 class="mb-0">{{ ucfirst($admin->status) }}</h6>
              </div>
            </div>
          </div>
          
          <div class="d-grid gap-2 mt-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfile">
              <i class='bx bx-edit-alt me-1'></i> Edit Profile
            </button>
            <button class="btn btn-label-secondary" data-bs-toggle="modal" data-bs-target="#changePassword">
              <i class='bx bx-lock-alt me-1'></i> Change Password
            </button>
            <form method="POST" action="{{ route('admin.logout') }}">
              @csrf
              <button type="submit" class="btn btn-label-danger">
                <i class='bx bx-log-out me-1'></i> Logout
              </button>
            </form>
          </div>
        </div>
      </div>
      <!-- /User Card -->
    </div>
    <!--/ User Sidebar -->
    
    <!-- User Content -->
    <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
      <!-- Activity Timeline -->
      <div class="card mb-4">
        <h5 class="card-header">Activity Timeline</h5>
        <div class="card-body">
          <ul class="timeline">
            @if($admin->last_login_at)
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-primary"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">Last Login</h6>
                  <small class="text-muted">{{ $admin->last_login_at->diffForHumans() }}</small>
                </div>
                <p class="mb-2">Successfully logged in from IP: {{ $admin->last_login_ip ?? 'Unknown' }}</p>
                <div class="d-flex">
                  <span class="badge bg-label-primary me-2">Login</span>
                  <span class="text-muted">{{ $admin->last_login_at->format('M d, Y H:i:s') }}</span>
                </div>
              </div>
            </li>
            @endif
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-success"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">Profile Updated</h6>
                  <small class="text-muted">{{ $admin->updated_at->diffForHumans() }}</small>
                </div>
                <p class="mb-0">Your profile was last updated</p>
              </div>
            </li>
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-info"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">Account Created</h6>
                  <small class="text-muted">{{ $admin->created_at->diffForHumans() }}</small>
                </div>
                <p class="mb-0">Your account was created</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <!-- /Activity Timeline -->
      
      <!-- Account Details -->
      <div class="card">
        <h5 class="card-header">Account Details</h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <div class="form-control">{{ $admin->email }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone</label>
              <div class="form-control">{{ $admin->phone ?? 'Not provided' }}</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Role</label>
              <div class="form-control">
                <span class="badge bg-label-{{ $admin->role == 'super_admin' ? 'danger' : ($admin->role == 'admin' ? 'success' : 'info') }}">
                  {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                </span>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <div class="form-control">
                <span class="badge bg-label-{{ $admin->status == 'active' ? 'success' : 'danger' }}">
                  {{ ucfirst($admin->status) }}
                </span>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Last Login</label>
              <div class="form-control">
                {{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y H:i:s') : 'Never' }}
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Last Login IP</label>
              <div class="form-control">
                {{ $admin->last_login_ip ?? 'N/A' }}
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /Account Details -->
    </div>
    <!--/ User Content -->
  </div>
</div>
<!-- / Content -->

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfile" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Edit User Information</h3>
          <p class="text-muted">Updating user details will receive a privacy audit.</p>
        </div>
        <form id="editUserForm" class="row g-3" onsubmit="return false">
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserFirstName">First Name</label>
            <input type="text" id="modalEditUserFirstName" name="modalEditUserFirstName" class="form-control" placeholder="John" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserLastName">Last Name</label>
            <input type="text" id="modalEditUserLastName" name="modalEditUserLastName" class="form-control" placeholder="Doe" />
          </div>
          <div class="col-12">
            <label class="form-label" for="modalEditUserName">Username</label>
            <input type="text" id="modalEditUserName" name="modalEditUserName" class="form-control" placeholder="john.doe.007" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserEmail">Email</label>
            <input type="text" id="modalEditUserEmail" name="modalEditUserEmail" class="form-control" placeholder="example@domain.com" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserStatus">Status</label>
            <select id="modalEditUserStatus" name="modalEditUserStatus" class="form-select" aria-label="Default select example">
              <option selected>Status</option>
              <option value="1">Active</option>
              <option value="2">Inactive</option>
              <option value="3">Suspended</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditTaxID">Tax ID</label>
            <input type="text" id="modalEditTaxID" name="modalEditTaxID" class="form-control modal-edit-tax-id" placeholder="123 456 7890" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserPhone">Phone Number</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text">+1</span>
              <input type="text" id="modalEditUserPhone" name="modalEditUserPhone" class="form-control phone-number-mask" placeholder="202 555 0111" />
            </div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserLanguage">Language</label>
            <select id="modalEditUserLanguage" name="modalEditUserLanguage" class="select2 form-select" multiple>
              <option value="">Select</option>
              <option value="english" selected>English</option>
              <option value="spanish">Spanish</option>
              <option value="french">French</option>
              <option value="german">German</option>
              <option value="dutch">Dutch</option>
              <option value="hebrew">Hebrew</option>
              <option value="sanskrit">Sanskrit</option>
              <option value="hindi">Hindi</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserCountry">Country</label>
            <select id="modalEditUserCountry" name="modalEditUserCountry" class="select2 form-select" data-allow-clear="true">
              <option value="">Select</option>
              <option value="Australia">Australia</option>
              <option value="Bangladesh">Bangladesh</option>
              <option value="Belarus">Belarus</option>
              <option value="Brazil">Brazil</option>
              <option value="Canada">Canada</option>
              <option value="China">China</option>
              <option value="France">France</option>
              <option value="Germany">Germany</option>
              <option value="India">India</option>
              <option value="Indonesia">Indonesia</option>
              <option value="Israel">Israel</option>
              <option value="Italy">Italy</option>
              <option value="Japan">Japan</option>
              <option value="Korea">Korea, Republic of</option>
              <option value="Mexico">Mexico</option>
              <option value="Philippines">Philippines</option>
              <option value="Russia">Russian Federation</option>
              <option value="South Africa">South Africa</option>
              <option value="Thailand">Thailand</option>
              <option value="Turkey">Turkey</option>
              <option value="Ukraine">Ukraine</option>
              <option value="United Arab Emirates">United Arab Emirates</option>
              <option value="United Kingdom">United Kingdom</option>
              <option value="United States">United States</option>
            </select>
          </div>
          <div class="col-12">
            <label class="switch">
              <input type="checkbox" class="switch-input">
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="switch-label">Use as a billing address?</span>
            </label>
          </div>
          <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit Profile Modal -->

<!-- Change Password Modal -->
<div class="modal fade" id="changePassword" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Change Password</h3>
          <p class="text-muted">Your new password must be different from previously used passwords</p>
        </div>
        <form id="changePasswordForm" onsubmit="return false">
          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="currentPassword">Current Password</label>
            <div class="input-group input-group-merge">
              <input
                type="password"
                id="currentPassword"
                class="form-control"
                name="currentPassword"
                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                aria-describedby="currentPassword"
              />
              <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
          </div>
          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="newPassword">New Password</label>
            <div class="input-group input-group-merge">
              <input
                type="password"
                id="newPassword"
                class="form-control"
                name="newPassword"
                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                aria-describedby="newPassword"
              />
              <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
          </div>
          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="confirmPassword">Confirm New Password</label>
            <div class="input-group input-group-merge">
              <input
                type="password"
                id="confirmPassword"
                class="form-control"
                name="confirmPassword"
                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                aria-describedby="confirmPassword"
              />
              <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
          </div>
          <div class="col-12 mb-4">
            <h6>Password Requirements:</h6>
            <ul class="ps-3 mb-0">
              <li class="mb-1">Minimum 8 characters long - the more, the better</li>
              <li class="mb-1">At least one lowercase character</li>
              <li>At least one number, symbol, or whitespace character</li>
            </ul>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Update Password</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Change Password Modal -->

@push('scripts')
<script>
  // Add any custom scripts here
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize any plugins or add event listeners
  });
</script>
@endpush
@endsection
