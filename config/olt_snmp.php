<?php

return [
    // OLT SNMP Configuration
    'olt' => [
        'host' => env('OLT_SNMP_HOST', '10.22.4.254'),
        'community' => env('OLT_SNMP_COMMUNITY', 'public'),
        'version' => env('OLT_SNMP_VERSION', '2c'),
        'timeout' => env('OLT_SNMP_TIMEOUT', 5),
        'retries' => env('OLT_SNMP_RETRIES', 3),
    ],

    // ZTE C320 Specific SNMP OIDs
    'oids' => [
        // VLAN Profile OIDs for ZTE C320
        'vlan_profile_name' => '1.3.6.1.4.1.3902.1012.3.28.1.1.2',
        'vlan_profile_vlan' => '1.3.6.1.4.1.3902.1012.3.28.1.1.3',
        
        // Alternative OIDs (uncomment if the above don't work)
        // 'vlan_profile_name' => '1.3.6.1.4.1.3902.1012.3.50.12.1.1.1',
        // 'vlan_profile_vlan' => '1.3.6.1.4.1.3902.1012.3.50.12.1.1.2',
        
        // System Information
        'system_description' => '1.3.6.1.2.1.1.1.0',
        'system_name' => '1.3.6.1.2.1.1.5.0',
        
        // Card Information
        'card_status' => '1.3.6.1.4.1.3902.1012.3.1.1.1.1.1',
        'card_type' => '1.3.6.1.4.1.3902.1012.3.1.1.1.1.2',
    ],

    // Cache settings
    'cache' => [
        'lifetime' => env('OLT_SNMP_CACHE_LIFETIME', 300), // 5 minutes
        'enabled' => env('OLT_SNMP_CACHE_ENABLED', true),
    ],

    // Fallback settings
    'fallback' => [
        'use_hardcoded' => env('OLT_SNMP_FALLBACK_HARDCODED', true),
        'log_failures' => env('OLT_SNMP_LOG_FAILURES', true),
    ],
];
