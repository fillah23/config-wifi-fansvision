@extends('layouts.app')

@section('title', 'SNMP Debug - OLT Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-network-wired"></i>
                        SNMP Debug & Data Viewer
                    </h5>
                </div>
                <div class="card-body">
                    <!-- SNMP Connection Test -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-plug"></i>
                                        Test SNMP Connection
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <button id="testSnmpBtn" class="btn btn-primary">
                                        <i class="fas fa-play"></i>
                                        Test Connection
                                    </button>
                                    <div id="connectionResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        Get Full SNMP Info
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <button id="getSnmpInfoBtn" class="btn btn-info">
                                        <i class="fas fa-download"></i>
                                        Get All Data
                                    </button>
                                    <div id="fullInfoResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom OID Query -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-search"></i>
                                        Custom OID Query
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="customOid" class="form-label">OID:</label>
                                            <input type="text" id="customOid" class="form-control" 
                                                   placeholder="1.3.6.1.2.1.1.1.0" value="1.3.6.1.2.1.1.1.0">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="queryType" class="form-label">Type:</label>
                                            <select id="queryType" class="form-control">
                                                <option value="get">Get</option>
                                                <option value="walk">Walk</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">&nbsp;</label><br>
                                            <button id="queryOidBtn" class="btn btn-success">
                                                <i class="fas fa-search"></i>
                                                Query
                                            </button>
                                        </div>
                                    </div>
                                    <div id="customOidResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Available OIDs -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list"></i>
                                        Available OIDs
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <button id="getOidsBtn" class="btn btn-warning">
                                        <i class="fas fa-list"></i>
                                        Show Available OIDs
                                    </button>
                                    <div id="oidsResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">VLAN Profiles</h6>
                                </div>
                                <div class="card-body">
                                    <button id="getVlanProfilesBtn" class="btn btn-secondary btn-sm">
                                        Get VLAN Profiles
                                    </button>
                                    <div id="vlanProfilesResult" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Cards Info</h6>
                                </div>
                                <div class="card-body">
                                    <button id="refreshCardsBtn" class="btn btn-secondary btn-sm">
                                        Refresh Cards
                                    </button>
                                    <div id="cardsResult" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">System Info</h6>
                                </div>
                                <div class="card-body">
                                    <button id="getSystemInfoBtn" class="btn btn-secondary btn-sm">
                                        Get System Info
                                    </button>
                                    <div id="systemInfoResult" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Processing SNMP request...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));

    // Test SNMP Connection
    $('#testSnmpBtn').click(function() {
        loadingModal.show();
        $.ajax({
            url: '{{ route("olt.snmp.test") }}',
            type: 'GET',
            success: function(response) {
                loadingModal.hide();
                if (response.success) {
                    $('#connectionResult').html(`
                        <div class="alert alert-success">
                            <strong>Connection Successful!</strong><br>
                            Response Time: ${response.response_time_ms}ms<br>
                            System: ${response.system_description}
                        </div>
                    `);
                } else {
                    $('#connectionResult').html(`
                        <div class="alert alert-danger">
                            <strong>Connection Failed!</strong><br>
                            Error: ${response.error || response.message}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                loadingModal.hide();
                const response = xhr.responseJSON;
                $('#connectionResult').html(`
                    <div class="alert alert-danger">
                        <strong>Connection Failed!</strong><br>
                        Error: ${response?.error || 'Unknown error'}
                    </div>
                `);
            }
        });
    });

    // Get Full SNMP Info
    $('#getSnmpInfoBtn').click(function() {
        loadingModal.show();
        $.ajax({
            url: '{{ route("olt.snmp.info") }}',
            type: 'GET',
            success: function(response) {
                loadingModal.hide();
                if (response.success) {
                    $('#fullInfoResult').html(`
                        <div class="alert alert-success">
                            <strong>Data Retrieved Successfully!</strong>
                            <pre class="mt-2" style="max-height: 300px; overflow-y: auto; font-size: 12px;">${JSON.stringify(response.data, null, 2)}</pre>
                        </div>
                    `);
                } else {
                    $('#fullInfoResult').html(`
                        <div class="alert alert-danger">
                            <strong>Failed to get data!</strong><br>
                            Error: ${response.error}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                loadingModal.hide();
                $('#fullInfoResult').html(`
                    <div class="alert alert-danger">
                        <strong>Request Failed!</strong><br>
                        Error: ${xhr.responseJSON?.error || 'Unknown error'}
                    </div>
                `);
            }
        });
    });

    // Query Custom OID
    $('#queryOidBtn').click(function() {
        const oid = $('#customOid').val();
        const type = $('#queryType').val();
        
        if (!oid) {
            alert('Please enter an OID');
            return;
        }

        loadingModal.show();
        $.ajax({
            url: '{{ route("olt.snmp.oid") }}',
            type: 'GET',
            data: { oid: oid, type: type },
            success: function(response) {
                loadingModal.hide();
                if (response.success) {
                    $('#customOidResult').html(`
                        <div class="alert alert-success">
                            <strong>Query Successful!</strong><br>
                            OID: ${response.oid}<br>
                            Type: ${response.type}<br>
                            Count: ${response.count}<br>
                            Response Time: ${response.response_time_ms}ms
                            <pre class="mt-2" style="max-height: 200px; overflow-y: auto; font-size: 12px;">${JSON.stringify(response.data, null, 2)}</pre>
                        </div>
                    `);
                } else {
                    $('#customOidResult').html(`
                        <div class="alert alert-danger">
                            <strong>Query Failed!</strong><br>
                            Error: ${response.error}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                loadingModal.hide();
                $('#customOidResult').html(`
                    <div class="alert alert-danger">
                        <strong>Request Failed!</strong><br>
                        Error: ${xhr.responseJSON?.error || 'Unknown error'}
                    </div>
                `);
            }
        });
    });

    // Get Available OIDs
    $('#getOidsBtn').click(function() {
        loadingModal.show();
        $.ajax({
            url: '{{ route("olt.snmp.oids") }}',
            type: 'GET',
            success: function(response) {
                loadingModal.hide();
                if (response.success) {
                    let oidsHtml = '<div class="alert alert-info"><strong>Available OIDs:</strong></div>';
                    
                    for (const [category, oids] of Object.entries(response.oids)) {
                        oidsHtml += `<h6 class="mt-3">${category.toUpperCase()}</h6>`;
                        oidsHtml += '<div class="table-responsive"><table class="table table-sm">';
                        oidsHtml += '<thead><tr><th>Name</th><th>OID</th><th>Action</th></tr></thead><tbody>';
                        
                        for (const [name, oid] of Object.entries(oids)) {
                            oidsHtml += `
                                <tr>
                                    <td>${name}</td>
                                    <td><code>${oid}</code></td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-primary use-oid" data-oid="${oid}">Use</button>
                                    </td>
                                </tr>
                            `;
                        }
                        
                        oidsHtml += '</tbody></table></div>';
                    }
                    
                    $('#oidsResult').html(oidsHtml);
                } else {
                    $('#oidsResult').html(`
                        <div class="alert alert-danger">
                            <strong>Failed to get OIDs!</strong><br>
                            Error: ${response.error}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                loadingModal.hide();
                $('#oidsResult').html(`
                    <div class="alert alert-danger">
                        <strong>Request Failed!</strong><br>
                        Error: ${xhr.responseJSON?.error || 'Unknown error'}
                    </div>
                `);
            }
        });
    });

    // Use OID button click
    $(document).on('click', '.use-oid', function() {
        const oid = $(this).data('oid');
        $('#customOid').val(oid);
        $('html, body').animate({
            scrollTop: $('#customOid').offset().top - 100
        }, 500);
    });

    // Get VLAN Profiles
    $('#getVlanProfilesBtn').click(function() {
        $.ajax({
            url: '{{ route("olt.snmp.vlan-profiles") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#vlanProfilesResult').html(`
                        <div class="alert alert-success">
                            <small>Count: ${response.count}</small>
                            <pre style="font-size: 10px;">${JSON.stringify(response.profiles, null, 2)}</pre>
                        </div>
                    `);
                }
            }
        });
    });

    // Refresh Cards
    $('#refreshCardsBtn').click(function() {
        $.ajax({
            url: '{{ route("olt.snmp.refresh-cards") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#cardsResult').html(`
                        <div class="alert alert-success">
                            <small>Count: ${response.count}</small>
                            <pre style="font-size: 10px;">${JSON.stringify(response.cards, null, 2)}</pre>
                        </div>
                    `);
                }
            }
        });
    });

    // Get System Info (using SNMP info)
    $('#getSystemInfoBtn').click(function() {
        $.ajax({
            url: '{{ route("olt.snmp.info") }}',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.system_info) {
                    $('#systemInfoResult').html(`
                        <div class="alert alert-info">
                            <pre style="font-size: 10px;">${JSON.stringify(response.data.system_info, null, 2)}</pre>
                        </div>
                    `);
                }
            }
        });
    });
});
</script>
@endsection
