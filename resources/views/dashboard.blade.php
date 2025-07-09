@extends('layouts.admin')

@section('content')
    <div class="dashboard-header mb-4">
        <div class="d-flex align-items-center">
            <div class="avatar-gradient me-3">
                <span class="avatar-text">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            </div>
            <div>
                <h2 class="dashboard-title mb-1">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </h2>
                <div class="dashboard-subtitle">
                    Welcome back, <strong>{{ Auth::user()->name }}</strong>! 
                    <span class="badge bg-success ms-2">
                        <i class="fas fa-circle pulse me-1"></i>Online
                    </span>
                </div>
            </div>
        </div>
        <div class="dashboard-time">
            <i class="far fa-clock me-2"></i>
            <span id="current-time">{{ now()->format('M d, Y - g:i A') }}</span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Enhanced Stats Cards -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-card-body">
                    <div class="stats-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number">
                            <span class="count-up">1</span>
                            <span class="status-dot status-online"></span>
                        </div>
                        <div class="stats-label">OLT Status</div>
                        <div class="stats-sublabel">ZTE C320/C300</div>
                    </div>
                </div>
                <div class="stats-footer">
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: 95%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card stats-card-success">
                <div class="stats-card-body">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number">
                            <span class="count-up">{{ \App\Models\User::count() }}</span>
                            <span class="trend-up">
                                <i class="fas fa-arrow-up"></i>
                            </span>
                        </div>
                        <div class="stats-label">Total Users</div>
                        <div class="stats-sublabel">Active accounts</div>
                    </div>
                </div>
                <div class="stats-footer">
                    <div class="progress-bar-custom">
                        <div class="progress-fill bg-success" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-card-body">
                    <div class="stats-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number">
                            <span class="count-up">8</span>
                            <span class="unit">Cards</span>
                        </div>
                        <div class="stats-label">Available Cards</div>
                        <div class="stats-sublabel">GPON Slots</div>
                    </div>
                </div>
                <div class="stats-footer">
                    <div class="progress-bar-custom">
                        <div class="progress-fill bg-warning" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="stats-card stats-card-info">
                <div class="stats-card-body">
                    <div class="stats-icon">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number">
                            <span class="count-up">99</span>
                            <span class="unit">%</span>
                        </div>
                        <div class="stats-label">Connection</div>
                        <div class="stats-sublabel">SNMP Uptime</div>
                    </div>
                </div>
                <div class="stats-footer">
                    <div class="progress-bar-custom">
                        <div class="progress-fill bg-info" style="width: 99%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Action Cards -->
    <div class="row g-4 mt-2">
        <div class="col-lg-6 col-md-12">
            <div class="modern-card">
                <div class="card-header-modern">
                    <div class="card-title-modern">
                        <div class="icon-wrapper icon-primary">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="action-buttons">
                        <a href="{{ route('olt.index') }}" class="action-btn action-btn-primary">
                            <div class="action-icon">
                                <i class="fas fa-network-wired"></i>
                            </div>
                            <div class="action-content">
                                <h6 class="action-title">OLT Management</h6>
                                <p class="action-subtitle">Configure & monitor ONUs</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.users.index') }}" class="action-btn action-btn-secondary">
                            <div class="action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="action-content">
                                <h6 class="action-title">User Management</h6>
                                <p class="action-subtitle">Manage system users</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>

                        <a href="{{ route('profile.edit') }}" class="action-btn action-btn-info">
                            <div class="action-icon">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="action-content">
                                <h6 class="action-title">Profile Settings</h6>
                                <p class="action-subtitle">Update your profile</p>
                            </div>
                            <div class="action-arrow">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-12">
            <div class="modern-card">
                <div class="card-header-modern">
                    <div class="card-title-modern">
                        <div class="icon-wrapper icon-info">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h5 class="mb-0">System Information</h5>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-globe text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>OLT IP Address</label>
                                <span class="info-value">10.22.4.254</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-key text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label>SNMP Community</label>
                                <span class="info-value">
                                    <code>fmjrw</code>
                                    <span class="badge bg-success ms-2">Active</span>
                                </span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-code-branch text-info"></i>
                            </div>
                            <div class="info-content">
                                <label>SNMP Version</label>
                                <span class="info-value">v2c</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-clock text-success"></i>
                            </div>
                            <div class="info-content">
                                <label>Last Activity</label>
                                <span class="info-value">{{ Auth::user()->updated_at->format('M d, Y \a\t g:i A') }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-user-shield text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label>Your Role</label>
                                <span class="info-value">
                                    <span class="badge bg-gradient-primary">
                                        <i class="fas fa-crown me-1"></i>Administrator
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Recent Activity -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="modern-card">
                <div class="card-header-modern">
                    <div class="card-title-modern">
                        <div class="icon-wrapper icon-success">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="activity-timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon timeline-icon-success">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">User Login</h6>
                                    <span class="timeline-time">{{ now()->format('g:i A') }}</span>
                                </div>
                                <p class="timeline-text">
                                    <strong>{{ Auth::user()->name }}</strong> logged into the system
                                </p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-icon timeline-icon-primary">
                                <i class="fas fa-network-wired"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">System Initialized</h6>
                                    <span class="timeline-time">{{ now()->subMinutes(5)->format('g:i A') }}</span>
                                </div>
                                <p class="timeline-text">
                                    OLT Management system successfully initialized
                                </p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-icon timeline-icon-info">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">Database Ready</h6>
                                    <span class="timeline-time">{{ now()->subMinutes(10)->format('g:i A') }}</span>
                                </div>
                                <p class="timeline-text">
                                    System database is ready for ONU management operations
                                </p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-icon timeline-icon-warning">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">SNMP Connection</h6>
                                    <span class="timeline-time">{{ now()->subMinutes(15)->format('g:i A') }}</span>
                                </div>
                                <p class="timeline-text">
                                    SNMP connection established with ZTE C320/C300 device
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Styles -->
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .avatar-gradient {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .dashboard-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .dashboard-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .dashboard-time {
            color: #6c757d;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: var(--border-radius);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stats-card-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .stats-card-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .stats-card-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .stats-card-info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .stats-card-body {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stats-content {
            flex: 1;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 1rem;
            font-weight: 600;
            opacity: 0.9;
        }

        .stats-sublabel {
            font-size: 0.875rem;
            opacity: 0.7;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse 2s infinite;
        }

        .trend-up {
            font-size: 1rem;
            color: #10b981;
        }

        .unit {
            font-size: 1.2rem;
            opacity: 0.8;
        }

        .stats-footer {
            margin-top: 1rem;
        }

        .progress-bar-custom {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            transition: width 0.3s ease;
        }

        .modern-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            height: 100%;
        }

        .card-header-modern {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title-modern {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icon-wrapper {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .icon-primary {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
        }

        .icon-info {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .icon-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .card-body-modern {
            padding: 1.5rem;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .action-btn:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            color: inherit;
        }

        .action-btn-primary:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .action-btn-secondary:hover {
            background: linear-gradient(135deg, rgba(100, 116, 139, 0.1), rgba(71, 85, 105, 0.1));
        }

        .action-btn-info:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
        }

        .action-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .action-content {
            flex: 1;
        }

        .action-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .action-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }

        .action-arrow {
            color: #6c757d;
            font-size: 1.2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: var(--border-radius);
            border: 1px solid #e2e8f0;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .info-content {
            flex: 1;
        }

        .info-content label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
            display: block;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark-color);
        }

        .activity-timeline {
            position: relative;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            position: relative;
        }

        .activity-item:not(:last-child) {
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            position: relative;
            z-index: 1;
        }

        .activity-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .activity-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .activity-info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .activity-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .activity-content {
            flex: 1;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .activity-description {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .count-up {
            display: inline-block;
            transition: all 0.3s ease;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
        }

        .card-actions .btn {
            transition: all 0.3s ease;
        }

        .card-actions .btn:hover {
            transform: translateY(-2px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .stats-card {
                margin-bottom: 1rem;
            }

            .action-buttons {
                gap: 0.75rem;
            }

            .action-btn {
                padding: 0.75rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .activity-timeline::before {
                left: 20px;
            }

            .activity-icon {
                width: 35px;
                height: 35px;
            }
        }
    </style>

    <script>
        // Count up animation
        function countUp(element, start, end, duration) {
            let startTime = null;
            const step = (timestamp) => {
                if (!startTime) startTime = timestamp;
                const progress = Math.min((timestamp - startTime) / duration, 1);
                const current = Math.floor(progress * (end - start) + start);
                element.textContent = current;
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            };
            requestAnimationFrame(step);
        }

        // Initialize count up animations
        document.addEventListener('DOMContentLoaded', function() {
            const countElements = document.querySelectorAll('.count-up');
            countElements.forEach(element => {
                const target = parseInt(element.textContent);
                countUp(element, 0, target, 1000);
            });
        });

        // Update time every second
        function updateTime() {
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                const now = new Date();
                const options = { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit',
                    second: '2-digit'
                };
                timeElement.textContent = now.toLocaleDateString('en-US', options);
            }
        }

        // Update time every second
        setInterval(updateTime, 1000);

        // Refresh activity function
        function refreshActivity() {
            const button = event.target.closest('button');
            const icon = button.querySelector('i');
            
            icon.classList.add('fa-spin');
            
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                // Here you would typically make an AJAX call to refresh the activity
                console.log('Activity refreshed');
            }, 1000);
        }

        // Add hover effects to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card, .modern-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
@endsection
