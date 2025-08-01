# SNMP API Documentation - OLT Management

API ini menyediakan akses ke data SNMP dari OLT ZTE C320 untuk monitoring dan debugging.

## Base URL
```
http://your-domain/olt/snmp
```

## Authentication
Semua endpoint memerlukan authentication. User harus login terlebih dahulu.

## Available Endpoints

### 1. Test SNMP Connection
**GET** `/olt/snmp/test`

Test koneksi SNMP ke OLT.

**Response:**
```json
{
    "success": true,
    "message": "SNMP connection successful",
    "config": {
        "host": "10.22.4.254",
        "community": "fmjrw",
        "version": "2c",
        "timeout": 5,
        "retries": 3
    },
    "response_time_ms": 45.67,
    "system_description": "ZTE Zxa10 C320",
    "timestamp": "2025-07-10T10:00:00.000000Z"
}
```

### 2. Get Full SNMP Information
**GET** `/olt/snmp/info`

Mendapatkan informasi lengkap SNMP dari OLT termasuk system info, VLAN profiles, cards, dan interfaces.

**Response:**
```json
{
    "success": true,
    "data": {
        "connection_info": {
            "host": "10.22.4.254",
            "community": "fmjrw",
            "version": "2c",
            "timeout": 5,
            "retries": 3
        },
        "system_info": {
            "sysDescr": "ZTE Zxa10 C320",
            "sysUpTime": "12345678",
            "sysContact": "",
            "sysName": "OLT-C320",
            "sysLocation": ""
        },
        "vlan_profiles": {
            "LOKALGR3": {
                "vlan": 1100,
                "profile": "LOKALGR3",
                "source": "snmp"
            }
        },
        "cards_info": [
            {
                "slot": "1",
                "card": "2",
                "full_id": "1/2",
                "status": "Working",
                "type": "GPON",
                "source": "snmp"
            }
        ],
        "interface_info": {
            "ifNumber": 16,
            "ifDescr": {
                "1": "gpon-olt_1/2/1",
                "2": "gpon-olt_1/2/2"
            }
        }
    },
    "timestamp": "2025-07-10T10:00:00.000000Z"
}
```

### 3. Query Specific OID
**GET** `/olt/snmp/oid`

Query OID tertentu dengan operasi GET atau WALK.

**Parameters:**
- `oid` (required): OID yang akan di-query
- `type` (optional): `get` atau `walk` (default: `get`)

**Example:**
```
GET /olt/snmp/oid?oid=1.3.6.1.2.1.1.1.0&type=get
```

**Response:**
```json
{
    "success": true,
    "oid": "1.3.6.1.2.1.1.1.0",
    "type": "get",
    "data": {
        "1.3.6.1.2.1.1.1.0": {
            "value": "ZTE Zxa10 C320",
            "type": "string"
        }
    },
    "count": 1,
    "response_time_ms": 23.45,
    "timestamp": "2025-07-10T10:00:00.000000Z"
}
```

### 4. Get Available OIDs
**GET** `/olt/snmp/oids`

Mendapatkan daftar OID yang tersedia untuk OLT ZTE C320.

**Response:**
```json
{
    "success": true,
    "oids": {
        "system": {
            "sysDescr": "1.3.6.1.2.1.1.1.0",
            "sysUpTime": "1.3.6.1.2.1.1.3.0",
            "sysName": "1.3.6.1.2.1.1.5.0"
        },
        "interfaces": {
            "ifNumber": "1.3.6.1.2.1.2.1.0",
            "ifDescr": "1.3.6.1.2.1.2.2.1.2",
            "ifOperStatus": "1.3.6.1.2.1.2.2.1.8"
        },
        "olt_specific": {
            "vlan_profile_name": "1.3.6.1.4.1.3902.1012.3.28.1.1.2",
            "vlan_profile_vlan": "1.3.6.1.4.1.3902.1012.3.28.1.1.3"
        },
        "cards": {
            "card_status": "1.3.6.1.4.1.3902.1012.3.1.1.1.1.2",
            "card_type": "1.3.6.1.4.1.3902.1012.3.1.1.1.1.3"
        }
    },
    "total_categories": 4,
    "timestamp": "2025-07-10T10:00:00.000000Z",
    "usage": {
        "get_specific_oid": "/api/snmp/oid?oid=1.3.6.1.2.1.1.1.0&type=get",
        "walk_oid_tree": "/api/snmp/oid?oid=1.3.6.1.2.1.2.2.1.2&type=walk"
    }
}
```

### 5. Get VLAN Profiles
**GET** `/olt/snmp/vlan-profiles`

Mendapatkan daftar VLAN profiles dari OLT.

**Response:**
```json
{
    "success": true,
    "profiles": {
        "LOKALGR3": {
            "vlan": 1100,
            "profile": "LOKALGR3",
            "source": "snmp",
            "index": 1
        },
        "PPPoE": {
            "vlan": 414,
            "profile": "PPPoE",
            "source": "snmp",
            "index": 2
        }
    },
    "count": 2,
    "cached": false,
    "cache_expiry": null
}
```

### 6. Refresh Cards Information
**POST** `/olt/snmp/refresh-cards`

Refresh informasi cards dari OLT dan update cache.

**Response:**
```json
{
    "success": true,
    "message": "Cards refreshed successfully",
    "cards": [
        {
            "slot": "1",
            "card": "2",
            "full_id": "1/2",
            "status": "Working",
            "type": "GPON",
            "source": "snmp"
        }
    ],
    "count": 1,
    "source": "snmp"
}
```

## Web Interface

Selain API, tersedia juga web interface untuk debugging SNMP:

**URL:** `/olt/snmp-debug`

Interface ini menyediakan:
- Test koneksi SNMP
- Query OID custom
- Lihat OID yang tersedia
- Quick actions untuk VLAN profiles, cards, dan system info

## Error Handling

Semua endpoint menggunakan format error yang konsisten:

```json
{
    "success": false,
    "error": "Error message here",
    "timestamp": "2025-07-10T10:00:00.000000Z"
}
```

## Common OIDs for ZTE C320

### System Information
- **System Description:** `1.3.6.1.2.1.1.1.0`
- **System Name:** `1.3.6.1.2.1.1.5.0`
- **System Uptime:** `1.3.6.1.2.1.1.3.0`

### Interface Information
- **Interface Number:** `1.3.6.1.2.1.2.1.0`
- **Interface Description:** `1.3.6.1.2.1.2.2.1.2` (walk)
- **Interface Status:** `1.3.6.1.2.1.2.2.1.8` (walk)

### ZTE Specific OIDs
- **VLAN Profile Names:** `1.3.6.1.4.1.3902.1012.3.28.1.1.2` (walk)
- **VLAN Profile VLANs:** `1.3.6.1.4.1.3902.1012.3.28.1.1.3` (walk)
- **Card Status:** `1.3.6.1.4.1.3902.1012.3.1.1.1.1.2` (walk)
- **Card Type:** `1.3.6.1.4.1.3902.1012.3.1.1.1.1.3` (walk)

## Configuration

SNMP settings dapat dikonfigurasi di `.env`:

```env
OLT_SNMP_HOST=10.22.4.254
OLT_SNMP_COMMUNITY=fmjrw
OLT_SNMP_VERSION=2c
OLT_SNMP_TIMEOUT=5
OLT_SNMP_RETRIES=3
OLT_SNMP_CACHE_LIFETIME=300
```

## Notes

- Semua response time dalam milliseconds
- Cache lifetime default 5 menit (300 detik)
- SNMP version yang didukung: 1, 2c, 3
- Fallback ke hardcoded values jika SNMP gagal
- Logging otomatis untuk troubleshooting
