@extends('layouts.admin')

@section('content')
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 text-primary mb-1">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                </h2>
                <p class="text-muted mb-0">Create a new user account for the system</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Users
            </a>
        </div>
    </x-slot>

    <div class="row justify-content-center" data-aos="fade-up">
        <div class="col-lg-8 col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>Create New User Account
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
                        @csrf

                        <div class="row g-4">
                            <!-- User Avatar Preview -->
                            <div class="col-12 text-center">
                                <div class="user-avatar-preview bg-gradient-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;" id="avatarPreview">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h6 class="text-muted">User Profile Preview</h6>
                            </div>

                            <!-- Name -->
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Full Name
                                </label>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" required autofocus 
                                       placeholder="Enter full name" oninput="updateAvatarPreview()">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Enter the user's full name
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address
                                </label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required
                                       placeholder="Enter email address">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    This will be used for login
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                           name="password" required placeholder="Enter password" oninput="checkPasswordStrength()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="passwordToggle"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="password-strength mt-2" id="passwordStrength"></div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Password must be at least 8 characters long
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Confirm Password
                                </label>
                                <div class="input-group">
                                    <input id="password_confirmation" type="password" class="form-control" 
                                           name="password_confirmation" required placeholder="Confirm password"
                                           oninput="checkPasswordMatch()">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="passwordConfirmToggle"></i>
                                    </button>
                                </div>
                                <div id="passwordMatch" class="mt-2"></div>
                            </div>

                            <!-- Role Selection -->
                            <div class="col-md-6">
                                <label for="role" class="form-label">
                                    <i class="fas fa-user-cog me-1"></i>User Role
                                </label>
                                <select id="role" class="form-select" name="role">
                                    <option value="admin">Administrator</option>
                                    <option value="operator">Operator</option>
                                    <option value="viewer">Viewer</option>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Select the user's access level
                                </div>
                            </div>

                            <!-- Email Verification -->
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-envelope-check me-1"></i>Email Verification
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_verified" name="email_verified" checked>
                                    <label class="form-check-label" for="email_verified">
                                        Mark email as verified
                                    </label>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    User can login immediately if checked
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Important:</strong> The user will receive login credentials via email after account creation.
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="col-12">
                                <hr class="my-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save me-1"></i>Create User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .user-avatar-preview {
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .password-strength {
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .password-strength::after {
            content: '';
            height: 100%;
            display: block;
            transition: all 0.3s ease;
        }

        .password-weak::after {
            width: 33%;
            background-color: #dc3545;
        }

        .password-medium::after {
            width: 66%;
            background-color: #ffc107;
        }

        .password-strong::after {
            width: 100%;
            background-color: #28a745;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .input-group .btn {
            transition: all 0.3s ease;
        }

        .input-group .btn:hover {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: var(--border-radius);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .card-header {
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
        }
    </style>

    <script>
        function updateAvatarPreview() {
            const nameInput = document.getElementById('name');
            const avatarPreview = document.getElementById('avatarPreview');
            
            if (nameInput.value.trim()) {
                avatarPreview.innerHTML = nameInput.value.trim().charAt(0).toUpperCase();
            } else {
                avatarPreview.innerHTML = '<i class="fas fa-user"></i>';
            }
        }

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId + 'Toggle');
            
            if (field.type === 'password') {
                field.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthMeter = document.getElementById('passwordStrength');
            
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthMeter.className = 'password-strength mt-2';
            
            if (strength <= 2) {
                strengthMeter.classList.add('password-weak');
            } else if (strength <= 4) {
                strengthMeter.classList.add('password-medium');
            } else {
                strengthMeter.classList.add('password-strong');
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword) {
                if (password === confirmPassword) {
                    matchDiv.innerHTML = '<small class="text-success"><i class="fas fa-check me-1"></i>Passwords match</small>';
                } else {
                    matchDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times me-1"></i>Passwords do not match</small>';
                }
            } else {
                matchDiv.innerHTML = '';
            }
        }

        // Form validation
        document.getElementById('createUserForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating User...';
            submitBtn.disabled = true;
        });
    </script>
@endsection
