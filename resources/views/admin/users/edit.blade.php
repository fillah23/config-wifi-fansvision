<x-admin-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-gray-800 mb-0">
            <i class="fas fa-user-edit me-2"></i>Edit User
        </h2>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Users
        </a>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-edit me-2"></i>Edit User: {{ $user->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Leave blank to keep current password. Password must be at least 6 characters long.
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input id="password_confirmation" type="password" class="form-control" 
                                   name="password_confirmation">
                        </div>

                        <!-- User Info -->
                        <div class="mb-3">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>User Information</h6>
                                <ul class="mb-0 small">
                                    <li><strong>Created:</strong> {{ $user->created_at->format('M d, Y \a\t g:i A') }}</li>
                                    <li><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y \a\t g:i A') }}</li>
                                    <li><strong>Email Verified:</strong> 
                                        @if($user->email_verified_at)
                                            <span class="text-success">✓ Yes ({{ $user->email_verified_at->format('M d, Y') }})</span>
                                        @else
                                            <span class="text-warning">✗ No</span>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
