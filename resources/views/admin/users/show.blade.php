@extends('layouts.admin')

@section('content')
    <x-slot name="header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <div>
                <h2 class="h4 font-weight-bold text-gray-800 mb-1">
                    <i class="fas fa-user me-2 text-primary"></i>User Profile
                </h2>
                <p class="text-muted small mb-0">Detailed information about user account</p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit User
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Users
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- User Profile Card -->
        <div class="col-lg-8">
            <div class="card glass-card border-0 shadow-lg" data-aos="fade-up">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-primary bg-gradient me-3">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Profile Information</h5>
                            <p class="text-muted small mb-0">Complete user details and account status</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <div class="avatar-large bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-lg" 
                                     style="width: 100px; height: 100px; font-size: 36px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="position-absolute bottom-0 end-0 translate-middle">
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success rounded-pill">
                                            <i class="fas fa-check"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-warning rounded-pill">
                                            <i class="fas fa-clock"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <h4 class="mt-3 mb-1">{{ $user->name }}</h4>
                            <p class="text-muted small">{{ $user->email }}</p>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <label class="form-label text-muted small mb-1">Full Name</label>
                                        <div class="form-control-plaintext fw-medium">{{ $user->name }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <label class="form-label text-muted small mb-1">Email Address</label>
                                        <div class="form-control-plaintext fw-medium">{{ $user->email }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <label class="form-label text-muted small mb-1">User ID</label>
                                        <div class="form-control-plaintext">
                                            <code class="bg-light px-2 py-1 rounded">#{{ $user->id }}</code>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <label class="form-label text-muted small mb-1">Email Status</label>
                                        <div class="form-control-plaintext">
                                            @if($user->email_verified_at)
                                                <span class="badge bg-success bg-gradient">
                                                    <i class="fas fa-check me-1"></i>Verified
                                                </span>
                                            @else
                                                <span class="badge bg-warning bg-gradient">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <label class="form-label text-muted small mb-1">Account Created</label>
                                        <div class="form-control-plaintext">{{ $user->created_at->format('M d, Y \a\t g:i A') }}</div>
                                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-item">
                                        <label class="form-label text-muted small mb-1">Last Updated</label>
                                        <div class="form-control-plaintext">{{ $user->updated_at->format('M d, Y \a\t g:i A') }}</div>
                                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions & Statistics -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card glass-card border-0 shadow-lg mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-success bg-gradient me-3">
                            <i class="fas fa-cogs text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Quick Actions</h5>
                            <p class="text-muted small mb-0">Manage user account</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                        
                        @if($user->id !== auth()->id())
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                <i class="fas fa-trash me-2"></i>Delete User
                            </button>
                        @else
                            <div class="alert alert-info small mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                You cannot delete your own account.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="card glass-card border-0 shadow-lg mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-info bg-gradient me-3">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Account Statistics</h5>
                            <p class="text-muted small mb-0">User activity overview</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="stat-item text-center p-3 bg-light rounded">
                                <div class="stat-icon text-primary mb-2">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                </div>
                                <div class="stat-value h4 mb-1">{{ $user->created_at->diffInDays() }}</div>
                                <div class="stat-label text-muted small">Days Since Registration</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item text-center p-2 bg-light rounded">
                                <div class="stat-value h5 mb-1">{{ $user->created_at->format('M') }}</div>
                                <div class="stat-label text-muted small">Join Month</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item text-center p-2 bg-light rounded">
                                <div class="stat-value h5 mb-1">{{ $user->created_at->format('Y') }}</div>
                                <div class="stat-label text-muted small">Join Year</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Verification Status -->
            <div class="card glass-card border-0 shadow-lg" data-aos="fade-up" data-aos-delay="300">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-warning bg-gradient me-3">
                            <i class="fas fa-envelope text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Email Verification</h5>
                            <p class="text-muted small mb-0">Account verification status</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($user->email_verified_at)
                        <div class="alert alert-success mb-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    <strong>Email Verified</strong>
                                    <small class="d-block text-muted">
                                        {{ $user->email_verified_at->format('M d, Y \a\t g:i A') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div>
                                    <strong>Email Not Verified</strong>
                                    <small class="d-block text-muted">
                                        User needs to verify their email address
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    @if($user->id !== auth()->id())
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger" id="deleteUserModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="icon-circle bg-danger bg-gradient mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-trash text-white fa-lg"></i>
                        </div>
                        <h5>Are you sure you want to delete this user?</h5>
                        <p class="text-muted">This action cannot be undone. All user data will be permanently removed.</p>
                    </div>
                    <div class="alert alert-danger">
                        <strong>User to be deleted:</strong><br>
                        <strong>Name:</strong> {{ $user->name }}<br>
                        <strong>Email:</strong> {{ $user->email }}<br>
                        <strong>ID:</strong> #{{ $user->id }}
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
        }

        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .info-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .stat-item {
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            background: rgba(var(--bs-primary-rgb), 0.1) !important;
            transform: translateY(-2px);
        }

        .avatar-large {
            transition: all 0.3s ease;
        }

        .avatar-large:hover {
            transform: scale(1.05);
        }

        .btn {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-size: 0.75em;
        }

        @media (max-width: 768px) {
            .avatar-large {
                width: 80px !important;
                height: 80px !important;
                font-size: 28px !important;
            }
            
            .glass-card {
                border-radius: 15px;
            }
        }
    </style>
@endsection
