@extends('layouts.admin')

@section('content')
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-gray-800 mb-0">
            <i class="fas fa-network-wired me-2"></i>OLT ZTE C320 Management
        </h2>
        <div class="d-flex align-items-center">
            <span class="status-indicator status-online"></span>
            <span class="text-muted">Connected to 10.22.4.254</span>
        </div>
    </x-slot>

    <style>
        .btn-custom {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #0056b3, #007bff);
            color: white;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-online {
            background-color: #28a745;
        }
        .status-offline {
            background-color: #dc3545;
        }
        .loading {
            display: none;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .search-section {
            transition: all 0.3s ease;
        }
        
        .btn-check:checked + .btn-outline-primary {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }
        
        .table-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }
        
        .table-warning td {
            border-color: rgba(255, 193, 7, 0.2);
        }
        
        .table-success {
            background-color: rgba(40, 167, 69, 0.1);
        }
        
        .table-success td {
            border-color: rgba(40, 167, 69, 0.2);
        }
        
        .btn-group {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 6px;
        }
        
        .btn-group .btn {
            border-radius: 0;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
        }
    </style>

    <div class="row">
        <!-- Unconfigured ONUs Panel -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Unconfigured ONUs
                    </h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-custom btn-sm mb-3" onclick="getUnconfiguredOnus()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>
                    <button class="btn btn-outline-secondary btn-sm mb-3 ms-2" onclick="debugRawOutput()">
                        <i class="fas fa-bug me-1"></i>
                        Debug Raw
                    </button>
                    <div class="loading text-center" id="uncfg-loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ONU Index</th>
                                    <th>Port</th>
                                    <th>Serial Number</th>
                                    <th>State</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="unconfigured-onus">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Click refresh to load unconfigured ONUs
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Panel -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>
                        ONU Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <form id="onu-config-form">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="card" class="form-label">Card</label>
                                <select class="form-select" id="card" name="card" required>
                                    <option value="">Select Card</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="loadAvailableCards()">
                                    <i class="fas fa-sync-alt"></i> Auto Detect
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="port" class="form-label">Port</label>
                                <input type="number" class="form-control" id="port" name="port" required>
                                <div class="form-text">
                                    Next ONU ID: <span id="next-onu-id" class="fw-bold text-primary">-</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="description" name="description" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="vlan_profile" class="form-label mb-0">VLAN Profile</label>
                                <div>
                                    <small class="text-muted me-2" id="vlan-source">
                                        @if(isset($vlanProfiles[array_key_first($vlanProfiles)]['source']))
                                            Source: {{ $vlanProfiles[array_key_first($vlanProfiles)]['source'] === 'snmp' ? '🌐 SNMP' : '📝 Local' }}
                                        @endif
                                    </small>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="refreshVlanProfiles()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <select class="form-select" id="vlan_profile" name="vlan_profile" required>
                                <option value="">Select VLAN Profile</option>
                                @foreach($vlanProfiles as $profile => $data)
                                    <option value="{{ $profile }}" data-vlan="{{ $data['vlan'] }}">
                                        {{ $profile }} (VLAN: {{ $data['vlan'] }})
                                        @if(isset($data['source']) && $data['source'] === 'snmp')
                                            🌐
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pppoe_username" class="form-label">PPPoE Username</label>
                                <input type="text" class="form-control" id="pppoe_username" name="pppoe_username" required>
                                <div class="form-text">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Input username sesuai kebutuhan (tidak menggunakan format otomatis)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pppoe_password" class="form-label">PPPoE Password</label>
                                <input type="text" class="form-control" id="pppoe_password" name="pppoe_password" required>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-cog me-2"></i>Configuration Features:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Dual VLAN:</strong> Service VLAN ({profile}) + Default VLAN 100 for ACS</li>
                                <li><strong>TR069/ACS:</strong> Auto-configured for remote management</li>
                                <li><strong>Username:</strong> Manual input (no auto-format)</li>
                                <li><strong>VEIP Mode:</strong> Hybrid mode for advanced features</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-custom btn-lg">
                                <i class="fas fa-save me-2"></i>
                                Configure ONU
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete ONU Panel -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-white text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-trash me-2"></i>
                        Delete ONU Management
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Search Options -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group" role="group" aria-label="Search options">
                                <input type="radio" class="btn-check" name="search-type" id="search-by-port" value="port" checked>
                                <label class="btn btn-outline-primary" for="search-by-port">
                                    <i class="fas fa-ethernet me-1"></i> Cari berdasarkan Card/Port
                                </label>
                                
                                <input type="radio" class="btn-check" name="search-type" id="search-by-sn" value="sn">
                                <label class="btn btn-outline-primary" for="search-by-sn">
                                    <i class="fas fa-barcode me-1"></i> Cari berdasarkan Serial Number
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search by Card/Port -->
                    <div id="search-port-section" class="search-section">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="delete-card" class="form-label">Card</label>
                                <select class="form-select" id="delete-card">
                                    <option value="1">Card 1 </option>
                                    <option value="2">Card 2 </option>
                                    <option value="3">Card 3 </option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="delete-port" class="form-label">Port</label>
                                <input type="number" class="form-control" id="delete-port" placeholder="Masukkan port">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button class="btn btn-outline-secondary" onclick="getConfiguredOnus()">
                                        <i class="fas fa-search"></i> Cek ONU
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search by Serial Number -->
                    <div id="search-sn-section" class="search-section" style="display: none;">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="search-serial" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="search-serial" placeholder="Masukkan Serial Number (contoh: ZTEGC1234567)">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Masukkan serial number lengkap untuk pencarian
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button class="btn btn-outline-secondary" onclick="searchBySerialNumber()">
                                        <i class="fas fa-search"></i> Cari ONU
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="loading text-center" id="configured-loading" style="display: none;">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Card</th>
                                    <th>Port</th>
                                    <th>ONU ID</th>
                                    <th>Type</th>
                                    <th>Serial Number</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="configured-onus">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Pilih card dan masukkan port, lalu klik "Cek ONU" untuk melihat ONU yang terkonfigurasi
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Panel -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Configuration Status
                    </h5>
                </div>
                <div class="card-body">
                    <div id="status-messages">
                        <p class="text-muted mb-0">
                            <i class="fas fa-rocket text-info"></i> 
                            Ready to configure ONUs with optimized speed...
                        </p>
                        <small class="text-muted">
                            <i class="fas fa-bolt"></i> 
                            Fast mode enabled - Reduced timeouts and batch processing
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 id="loadingText">Processing...</h5>
                    <p class="text-muted mb-0" id="loadingSubtext">Please wait while the operation is being performed.</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                    <small class="text-muted">⚡ Optimized for speed</small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Auto-fill name and description
        document.getElementById('name').addEventListener('blur', function() {
            if (this.value && !document.getElementById('description').value) {
                document.getElementById('description').value = this.value;
            }
        });

        // Get port info when port changes
        document.getElementById('port').addEventListener('change', function() {
            if (this.value) {
                getPortInfo(this.value);
            }
        });

        // Auto-detect cards on page load
        function loadAvailableCards() {
            return fetch('/olt/available-cards', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showStatus('Error loading cards: ' + data.error, 'warning');
                    return;
                }
                
                const cardSelect = document.getElementById('card');
                
                
                // Clear existing options
                cardSelect.innerHTML = '<option value="">Select Card</option>';
                
                
                // Add detected cards
                data.cards.forEach(card => {
                    const sourceIcon = getCardSourceIcon(card.source);
                    const option = new Option(`${sourceIcon} Card ${card.card} (${card.full_id}) - ${card.status}`, card.card);
                    cardSelect.add(option);
                    
                    const deleteOption = new Option(`${sourceIcon} Card ${card.card} (${card.full_id}) - ${card.status}`, card.card);
                    
                });
                
                // Select first card as default
                if (data.cards.length > 0) {
                    cardSelect.value = data.cards[0].card;
                    
                }
                
                const sourceText = getCardSourceText(data.source);
                showStatus(`${sourceText} Detected ${data.cards.length} available cards`, 'success');
                
                return data.cards; // Return cards for promise chain
            })
            .catch(error => {
                showStatus('Error loading cards: ' + error.message, 'danger');
                throw error; // Re-throw for promise chain
            });
        }
        
        // Get source icon for card
        function getCardSourceIcon(source) {
            switch(source) {
                case 'onu_data': return '📡';
                case 'snmp': return '🌐';
                case 'telnet': return '📞';
                case 'default': return '📝';
                default: return '❓';
            }
        }
        
        // Get source text for card
        function getCardSourceText(source) {
            switch(source) {
                case 'onu_data': return '📡 ONU Data:';
                case 'snmp': return '🌐 SNMP:';
                case 'telnet': return '📞 Telnet:';
                case 'default': return '📝 Default:';
                default: return '❓ Unknown:';
            }
        }

        // Get port info when card or port changes
        document.getElementById('card').addEventListener('change', function() {
            updatePortInfo();
        });

        // Get port info when port changes
        document.getElementById('port').addEventListener('change', function() {
            updatePortInfo();
        });

        function updatePortInfo() {
            const card = document.getElementById('card').value;
            const port = document.getElementById('port').value;
            
            if (card && port) {
                // Use fast port info endpoint
                fetch('/olt/port-info-fast', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ card: card, port: port })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        showStatus('Error: ' + data.error, 'danger');
                        return;
                    }
                    
                    document.getElementById('next-onu-id').textContent = data.nextOnuId || '-';
                    
                    // Show fast mode indicator
                    if (data.processing_time) {
                        showStatus(`Port info loaded (${data.processing_time})`, 'info');
                    }
                })
                .catch(error => {
                    showStatus('Network error: ' + error.message, 'danger');
                });
            }
        }

        function getConfiguredOnus() {
            const card = document.getElementById('delete-card').value;
            const port = document.getElementById('delete-port').value;
            
            if (!card || !port) {
                showStatus('Silakan pilih card dan masukkan port terlebih dahulu', 'warning');
                return;
            }
            
            const loading = document.getElementById('configured-loading');
            const tbody = document.getElementById('configured-onus');
            
            loading.style.display = 'block';
            tbody.innerHTML = '';

            fetch('/olt/configured-onus', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ card: card, port: port })
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                
                if (data.error) {
                    showStatus('Error: ' + data.error, 'danger');
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
                    return;
                }

                if (data.onus.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Tidak ada ONU yang terkonfigurasi di card/port ini</td></tr>';
                    return;
                }

                tbody.innerHTML = data.onus.map(onu => `
                    <tr>
                        <td>${onu.card}</td>
                        <td>${onu.port}</td>
                        <td>${onu.onu_id}</td>
                        <td>${onu.type}</td>
                        <td><code>${onu.serial_number}</code></td>
                        <td>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="confirmDeleteOnu('${onu.card}', '${onu.port}', '${onu.onu_id}', '${onu.serial_number}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(error => {
                loading.style.display = 'none';
                showStatus('Network error: ' + error.message, 'danger');
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Network error</td></tr>';
            });
        }

        function confirmDeleteOnu(card, port, onuId, serialNumber) {
            if (confirm(`Apakah Anda yakin ingin menghapus ONU ${onuId} (${serialNumber}) dari card ${card} port ${port}?`)) {
                deleteOnu(card, port, onuId, serialNumber);
            }
        }

        function deleteOnu(card, port, onuId, serialNumber) {
            // Show loading modal with timing
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            document.getElementById('loadingText').textContent = 'Deleting ONU...';
            document.getElementById('loadingSubtext').textContent = `Please wait while ONU ${onuId} is being deleted with optimized speed.`;
            loadingModal.show();

            const startTime = Date.now();

            fetch('/olt/delete-onu', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ 
                    card: card,
                    port: port, 
                    onu_id: onuId 
                })
            })
            .then(response => response.json())
            .then(data => {
                const endTime = Date.now();
                const processingTime = ((endTime - startTime) / 1000).toFixed(1);
                
                loadingModal.hide();
                
                if (data.error) {
                    showStatus(`Delete failed: ${data.error} (Time: ${processingTime}s)`, 'danger');
                    return;
                }
                
                if (data.success) {
                    showStatus(
                        `⚡ ONU ${onuId} (${serialNumber}) berhasil dihapus dalam ${processingTime}s dari card ${card} port ${port}`, 
                        'success'
                    );
                    
                    // Refresh configured ONUs list
                    getConfiguredOnus();
                    
                    // Refresh unconfigured ONUs list
                    getUnconfiguredOnus();
                }
            })
            .catch(error => {
                loadingModal.hide();
                showStatus('Network error: ' + error.message, 'danger');
            });
        }

        function debugRawOutput() {
            fetch('/olt/debug-raw', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showStatus('Debug Error: ' + data.error, 'danger');
                    return;
                }
                
                // Create modal to show raw output
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.id = 'debugModal';
                modal.innerHTML = `
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Raw OLT Output Debug</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <small class="text-muted">Raw Length: <code>${data.raw_length}</code></small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Clean Length: <code>${data.clean_length}</code></small>
                                    </div>
                                </div>
                                <h6>Cleaned Output:</h6>
                                <pre class="bg-light p-3 border rounded" style="max-height: 300px; overflow-y: auto;">${data.raw_output}</pre>
                                <h6 class="mt-3">Split Lines (${data.lines.length} lines):</h6>
                                <div style="max-height: 400px; overflow-y: auto;">
                                    <ul class="list-group">
                                        ${data.lines.map((line, index) => `
                                            <li class="list-group-item ${line.includes('gpon-onu') ? 'list-group-item-success' : ''}">
                                                <small class="text-muted">[${index}]</small> 
                                                <code>${line || '(empty line)'}</code>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                const debugModal = new bootstrap.Modal(modal);
                debugModal.show();
                
                // Remove modal when hidden
                modal.addEventListener('hidden.bs.modal', () => {
                    document.body.removeChild(modal);
                });
            })
            .catch(error => {
                showStatus('Debug Network error: ' + error.message, 'danger');
            });
        }

        function getUnconfiguredOnus() {
            const loading = document.getElementById('uncfg-loading');
            const tbody = document.getElementById('unconfigured-onus');
            
            loading.style.display = 'block';
            tbody.innerHTML = '';

            fetch('/olt/unconfigured-onus', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                
                if (data.error) {
                    showStatus('Error: ' + data.error, 'danger');
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading data</td></tr>';
                    return;
                }

                if (data.onus.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No unconfigured ONUs found</td></tr>';
                    return;
                }

                tbody.innerHTML = data.onus.map(onu => `
                    <tr>
                        <td>gpon-onu_${onu.slot}/${onu.card}/${onu.port}:${onu.onu_id}</td>
                        <td>${onu.port}</td>
                        <td><code>${onu.serial_number}</code></td>
                        <td><span class="badge bg-warning">${onu.state}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="selectOnu('${onu.port}', '${onu.serial_number}', '${onu.card}')">
                                <i class="fas fa-arrow-right"></i> Select
                            </button>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(error => {
                loading.style.display = 'none';
                showStatus('Network error: ' + error.message, 'danger');
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Network error</td></tr>';
            });
        }

        // Refresh VLAN profiles from OLT
        function refreshVlanProfiles() {
            const refreshBtn = document.querySelector('button[onclick="refreshVlanProfiles()"]');
            const originalText = refreshBtn.innerHTML;
            
            // Show loading state
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            refreshBtn.disabled = true;
            
            fetch('/olt/refresh-vlan-profiles', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                refreshBtn.innerHTML = originalText;
                refreshBtn.disabled = false;
                
                if (data.error) {
                    showStatus('Error refreshing VLAN profiles: ' + data.error, 'warning');
                    return;
                }
                
                if (data.success) {
                    // Update VLAN profile dropdown
                    updateVlanProfileDropdown(data.profiles);
                    
                    // Update source indicator
                    const sourceText = data.source === 'snmp' ? '🌐 SNMP' : '📝 Local';
                    document.getElementById('vlan-source').textContent = `Source: ${sourceText}`;
                    
                    showStatus(`✅ VLAN profiles refreshed! Found ${data.count} profiles from ${data.source}`, 'success');
                }
            })
            .catch(error => {
                refreshBtn.innerHTML = originalText;
                refreshBtn.disabled = false;
                showStatus('Network error refreshing VLAN profiles: ' + error.message, 'danger');
            });
        }

        function updateVlanProfileDropdown(profiles) {
            const select = document.getElementById('vlan_profile');
            const currentValue = select.value;
            
            // Clear existing options except the first one
            select.innerHTML = '<option value="">Select VLAN Profile</option>';
            
            // Add updated profiles
            Object.entries(profiles).forEach(([profile, data]) => {
                const option = document.createElement('option');
                option.value = profile;
                option.setAttribute('data-vlan', data.vlan);
                option.textContent = `${profile} (VLAN: ${data.vlan})`;
                
                // Add SNMP indicator
                if (data.source === 'snmp') {
                    option.textContent += ' 🌐';
                }
                
                select.appendChild(option);
            });
            
            // Restore selected value if it still exists
            if (currentValue && profiles[currentValue]) {
                select.value = currentValue;
            }
        }

        // Form submission
        document.getElementById('onu-config-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Show loading modal with timing
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            document.getElementById('loadingText').textContent = 'Configuring ONU...';
            document.getElementById('loadingSubtext').textContent = 'Please wait while the configuration is being applied with optimized speed.';
            loadingModal.show();

            const startTime = Date.now();

            fetch('/olt/configure', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                const endTime = Date.now();
                const processingTime = ((endTime - startTime) / 1000).toFixed(1);
                
                loadingModal.hide();
                
                if (data.error) {
                    showStatus(`Configuration failed: ${data.error} (Time: ${processingTime}s)`, 'danger');
                    return;
                }
                
                if (data.success) {
                    showStatus(
                        `🚀 ONU configured successfully in ${processingTime}s! ONU ID: ${data.onu_id}, VLAN: ${data.vlan}`, 
                        'success'
                    );
                    
                    // Reset form
                    this.reset();
                    document.getElementById('next-onu-id').textContent = '-';
                    
                    // Refresh unconfigured ONUs
                    getUnconfiguredOnus();
                }
            })
            .catch(error => {
                loadingModal.hide();
                showStatus('Network error: ' + error.message, 'danger');
            });
        });

        // Select ONU function to fill the configuration form
        function selectOnu(port, serialNumber, card = null) {
            // Fill port and serial number
            document.getElementById('port').value = port;
            document.getElementById('serial_number').value = serialNumber;
            
            // Auto-load available cards first
            loadAvailableCards().then(() => {
                // If card is provided, set it after cards are loaded
                if (card) {
                    document.getElementById('card').value = card;
                    // Update port info to get next ONU ID
                    updatePortInfo();
                } else {
                    // If no card provided but cards are available, use first one
                    const cardSelect = document.getElementById('card');
                    if (cardSelect.options.length > 1) {
                        cardSelect.selectedIndex = 1; // Select first available card (skip "Select Card" option)
                        updatePortInfo();
                    }
                }
            });
            
            // Show success message
            showStatus(`Selected ONU: Port ${port}, Serial ${serialNumber}${card ? ', Card ' + card : ''}`, 'info');
            
            // Scroll to configuration form
            document.getElementById('onu-config-form').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }

        // Show status message function
        function showStatus(message, type = 'info') {
            const statusDiv = document.getElementById('status-messages');
            const alertClass = `alert-${type}`;
            
            // Create alert element
            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fas fa-${getIconForType(type)} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Clear previous messages and add new one
            statusDiv.innerHTML = '';
            statusDiv.appendChild(alert);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }

        // Get icon for status type
        function getIconForType(type) {
            switch(type) {
                case 'success': return 'check-circle';
                case 'danger': return 'exclamation-triangle';
                case 'warning': return 'exclamation-circle';
                case 'info': return 'info-circle';
                default: return 'info-circle';
            }
        }

        // Toggle search sections
        document.querySelectorAll('input[name="search-type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const portSection = document.getElementById('search-port-section');
                const snSection = document.getElementById('search-sn-section');
                
                if (this.value === 'port') {
                    portSection.style.display = 'block';
                    snSection.style.display = 'none';
                } else {
                    portSection.style.display = 'none';
                    snSection.style.display = 'block';
                }
                
                // Clear previous results
                const tbody = document.getElementById('configured-onus');
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Pilih metode pencarian dan masukkan data untuk mencari ONU</td></tr>';
            });
        });

        // Search by Serial Number
        function searchBySerialNumber() {
            const serialNumber = document.getElementById('search-serial').value.trim();
            
            if (!serialNumber) {
                showStatus('Silakan masukkan Serial Number terlebih dahulu', 'warning');
                return;
            }
            
            const loading = document.getElementById('configured-loading');
            const tbody = document.getElementById('configured-onus');
            
            loading.style.display = 'block';
            tbody.innerHTML = '';
            
            // Show fast search status
            showStatus(`� FAST SEARCH: Mencari ONU dengan Serial Number: ${serialNumber}...`, 'info');
            
            // Add real-time progress indicator
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="spinner-border text-primary mb-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="fw-bold">🚀 Fast Search Mode</div>
                        <div class="text-muted">Mencari Serial Number: <code>${serialNumber}</code></div>
                        <div class="text-muted small">Menggunakan prioritas card untuk pencarian cepat...</div>
                    </td>
                </tr>
            `;
            
            const startTime = Date.now();
            console.log('Starting fast search for serial:', serialNumber);

            fetch('/olt/search-onu-by-serial', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ serial_number: serialNumber })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                const endTime = Date.now();
                const searchTime = ((endTime - startTime) / 1000).toFixed(1);
                
                console.log('Fast search completed in', searchTime, 'seconds:', data);
                loading.style.display = 'none';
                
                if (data.error) {
                    showStatus(`❌ Error: ${data.error} (${searchTime}s)`, 'danger');
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error mencari ONU</td></tr>';
                    return;
                }

                if (data.onus.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2 text-muted"></i>
                                <div>⚡ Tidak ada ONU yang ditemukan dengan Serial Number: <code>${serialNumber}</code></div>
                                <small class="text-muted">
                                    Pencarian selesai dalam ${searchTime}s menggunakan fast mode
                                    ${data.cards_searched ? '(Card: ' + data.cards_searched.join(', ') + ')' : ''}
                                </small>
                            </td>
                        </tr>
                    `;
                    showStatus(`⚡ Tidak ada ONU dengan Serial Number: ${serialNumber} (${searchTime}s)`, 'warning');
                    return;
                }

                tbody.innerHTML = data.onus.map(onu => `
                    <tr class="table-success">
                        <td>${onu.card}</td>
                        <td>${onu.port}</td>
                        <td>${onu.onu_id}</td>
                        <td>${onu.type}</td>
                        <td>
                            <code class="text-success fw-bold">${onu.serial_number}</code>
                            <small class="text-muted d-block">Card ${onu.card}, Port ${onu.port}</small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="confirmDeleteOnu('${onu.card}', '${onu.port}', '${onu.onu_id}', '${onu.serial_number}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                `).join('');
                
                showStatus(`🚀 Ditemukan ${data.onus.length} ONU dengan Serial Number: ${serialNumber} dalam ${searchTime}s (Fast Mode)`, 'success');
            })
            .catch(error => {
                const endTime = Date.now();
                const searchTime = ((endTime - startTime) / 1000).toFixed(1);
                
                console.error('Fast search error:', error);
                loading.style.display = 'none';
                showStatus(`❌ Network error: ${error.message} (${searchTime}s)`, 'danger');
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Network error</td></tr>';
            });
        }

        // Add enter key support for serial number search
        document.getElementById('search-serial').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBySerialNumber();
            }
        });

        // Load unconfigured ONUs on page load and auto-detect cards
        document.addEventListener('DOMContentLoaded', function() {
            getUnconfiguredOnus();
            loadAvailableCards();
        });
    </script>
@endsection
