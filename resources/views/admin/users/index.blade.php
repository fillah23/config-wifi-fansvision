@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 text-primary mb-1">
                <i class="fas fa-users me-2"></i>User Management
            </h2>
            <p class="text-muted mb-0">Manage system users and permissions</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">All Users</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.users.index', ['verified' => 'true']) }}">Verified Only</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.users.index', ['verified' => 'false']) }}">Pending Verification</a></li>
                </ul>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add New User
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary text-white rounded-circle p-3 me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $users->total() }}</h3>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success text-white rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $users->where('email_verified_at', '!=', null)->count() }}</h3>
                            <small class="text-muted">Verified Users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-warning text-white rounded-circle p-3 me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $users->where('email_verified_at', null)->count() }}</h3>
                            <small class="text-muted">Pending Verification</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info text-white rounded-circle p-3 me-3">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $users->where('created_at', '>=', now()->subDays(30))->count() }}</h3>
                            <small class="text-muted">New This Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Users List
                </h5>
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">{{ $users->count() }} of {{ $users->total() }} users</span>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleView('table')">
                            <i class="fas fa-table"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleView('grid')">
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <!-- Table View -->
                <div id="table-view" class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 16px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">ID: {{ $user->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="d-block">{{ $user->email }}</span>
                                            @if($user->email_verified_at)
                                                <small class="text-success">
                                                    <i class="fas fa-shield-alt me-1"></i>Verified
                                                </small>
                                            @else
                                                <small class="text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Unverified
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-gradient-primary">
                                            <i class="fas fa-user-cog me-1"></i>Administrator
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="d-block">{{ $user->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="d-block">{{ $user->updated_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete User" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Grid View -->
                <div id="grid-view" class="d-none">
                    <div class="row g-4">
                        @foreach($users as $user)
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="user-avatar bg-gradient-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <h6 class="card-title">{{ $user->name }}</h6>
                                        <p class="text-muted mb-2">{{ $user->email }}</p>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success mb-3">
                                                <i class="fas fa-check me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-warning mb-3">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        @endif
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                @if($user->id !== auth()->id())
                                                    <button type="button" class="btn btn-outline-danger btn-sm flex-fill" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <div class="empty-icon bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-2x text-muted"></i>
                        </div>
                        <h5 class="text-muted mb-2">No users found</h5>
                        <p class="text-muted mb-4">Get started by creating your first user account.</p>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add New User
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" class="d-inline">
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

    <style>
        .user-avatar {
            font-weight: 600;
            text-decoration: none;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .empty-state {
            padding: 2rem;
        }

        .empty-icon {
            font-size: 3rem;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .btn-group .btn {
            transition: all 0.3s ease;
        }

        .btn-group .btn:hover {
            transform: translateY(-1px);
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }

        .modal-content {
            border-radius: var(--border-radius);
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #e9ecef;
        }

        .modal-footer {
            border-top: 1px solid #e9ecef;
        }
    </style>

    <script>
        function toggleView(view) {
            const tableView = document.getElementById('table-view');
            const gridView = document.getElementById('grid-view');
            
            if (view === 'table') {
                tableView.classList.remove('d-none');
                gridView.classList.add('d-none');
            } else {
                tableView.classList.add('d-none');
                gridView.classList.remove('d-none');
            }
        }

        function confirmDelete(userId, userName) {
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteForm').action = `/admin/users/${userId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
@endsection
