@extends('layouts.admin')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="h4 font-weight-bold text-gray-800 mb-1">
                <i class="fas fa-user-cog me-2 text-primary"></i>Profile Settings
            </h2>
            <p class="text-muted small mb-0">Manage your account settings and preferences</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Information -->
        <div class="col-lg-6">
            <div class="card glass-card border-0 shadow-lg" data-aos="fade-up">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-primary bg-gradient me-3">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Profile Information</h5>
                            <p class="text-muted small mb-0">Update your profile details</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <!-- Update Password -->
        <div class="col-lg-6">
            <div class="card glass-card border-0 shadow-lg" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-warning bg-gradient me-3">
                            <i class="fas fa-lock text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Update Password</h5>
                            <p class="text-muted small mb-0">Change your account password</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <!-- Delete Account -->
        <div class="col-lg-12">
            <div class="card glass-card border-0 shadow-lg border-danger" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-danger bg-gradient me-3">
                            <i class="fas fa-trash text-white"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 text-danger">Danger Zone</h5>
                            <p class="text-muted small mb-0">Permanently delete your account</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

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

        .btn {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        @media (max-width: 768px) {
            .glass-card {
                border-radius: 15px;
            }
        }
    </style>
@endsection
