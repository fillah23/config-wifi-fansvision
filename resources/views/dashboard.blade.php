<x-admin-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-gray-800 mb-0">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </h2>
        <div class="text-muted">
            Welcome back, {{ Auth::user()->name }}!
        </div>
    </x-slot>

    <div class="row">
        <!-- Quick Stats -->
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">OLT Status</h6>
                            <h4 class="mb-0">Online</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-network-wired fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Users</h6>
                            <h4 class="mb-0">{{ \App\Models\User::count() }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">OLT Model</h6>
                            <h4 class="mb-0">ZTE C320</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-server fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Connection</h6>
                            <h4 class="mb-0">SNMP</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-link fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('olt.index') }}" class="btn btn-primary">
                            <i class="fas fa-network-wired me-2"></i>
                            Go to OLT Management
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-users me-2"></i>
                            Manage Users
                        </a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-info">
                            <i class="fas fa-user-edit me-2"></i>
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>System Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold">OLT IP:</td>
                            <td>10.22.4.254</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">SNMP Community:</td>
                            <td><code>fmjrw</code></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">SNMP Version:</td>
                            <td>v2c</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Last Login:</td>
                            <td>{{ Auth::user()->updated_at->format('M d, Y \a\t g:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Your Role:</td>
                            <td><span class="badge bg-primary">Administrator</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-sign-in-alt text-success me-2"></i>
                                <strong>{{ Auth::user()->name }}</strong> logged in
                            </div>
                            <small class="text-muted">{{ now()->format('M d, Y \a\t g:i A') }}</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-network-wired text-primary me-2"></i>
                                OLT Management system initialized
                            </div>
                            <small class="text-muted">{{ now()->subMinutes(5)->format('M d, Y \a\t g:i A') }}</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-database text-info me-2"></i>
                                System ready for ONU management
                            </div>
                            <small class="text-muted">{{ now()->subMinutes(10)->format('M d, Y \a\t g:i A') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
