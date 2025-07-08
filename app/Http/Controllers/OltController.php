<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OltController extends Controller
{
    

    // SNMP Configuration for OLT - now using config file
    private function getSnmpConfig()
    {
        return config('olt_snmp.olt', [
            'host' => '10.22.4.254',
            'community' => 'fmjrw',
            'version' => '2c',
            'timeout' => 5,
            'retries' => 3
        ]);
    }

    private function getSnmpOids()
    {
        return config('olt_snmp.oids', [
            'vlan_profile_name' => '1.3.6.1.4.1.3902.1012.3.28.1.1.2',
            'vlan_profile_vlan' => '1.3.6.1.4.1.3902.1012.3.28.1.1.3',
        ]);
    }

    // Cache for VLAN profiles to avoid frequent SNMP queries
    private $vlanProfilesCache = null;
    private $cacheExpiry = null;
    
    // Cache for card information
    private $cardsCache = null;
    private $cardsCacheExpiry = null;

    public function index()
    {
        // Get VLAN profiles from OLT via SNMP (with fallback to hardcoded)
        $vlanProfiles = $this->getVlanProfilesFromSnmp();
        
        return view('olt.index', [
            'vlanProfiles' => $vlanProfiles
        ]);
    }

    public function getUnconfiguredOnus(Request $request)
    {
        try {
            $connection = $this->connectToOlt();
            if (!$connection) {
                return response()->json(['error' => 'Tidak dapat terhubung ke OLT'], 500);
            }

            $result = $this->executeCommand($connection, 'show gpon onu uncfg');
            fclose($connection);

            // Clean and validate the output
            $cleanResult = $this->sanitizeOutput($result);

            // Log the cleaned output for debugging
            Log::info('Cleaned OLT Output length: ' . strlen($cleanResult));
            Log::info('Cleaned OLT Output: ' . $cleanResult);

            $onus = $this->parseUnconfiguredOnus($cleanResult);
            
            // Log parsed results
            Log::info('Parsed ONUs count: ' . count($onus));
            Log::info('Parsed ONUs data: ', $onus);
            
            return response()->json(['onus' => $onus]);
        } catch (\Exception $e) {
            Log::error('Error getting unconfigured ONUs: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getPortInfo(Request $request)
    {
        $port = $request->input('port');
        $card = $request->input('card', 2); // Default to card 2 if not specified
        
        try {
            $connection = $this->connectToOlt();
            if (!$connection) {
                return response()->json(['error' => 'Tidak dapat terhubung ke OLT'], 500);
            }

            $result = $this->executeCommand($connection, "show run interface gpon-olt_1/{$card}/{$port}");
            fclose($connection);

            $nextOnuId = $this->getNextOnuId($result);
            return response()->json([
                'nextOnuId' => $nextOnuId,
                'card' => $card
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting port info: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    // Fast methods for operations that don't need output parsing
    public function getPortInfoFast(Request $request)
    {
        $port = $request->input('port');
        $card = $request->input('card', 2);
        
        try {
            $connection = $this->connectToOlt();
            if (!$connection) {
                return response()->json(['error' => 'Tidak dapat terhubung ke OLT'], 500);
            }

            // Use faster command execution for port info
            $result = $this->executeCommand($connection, "show run interface gpon-olt_1/{$card}/{$port}", true);
            fclose($connection);

            $nextOnuId = $this->getNextOnuId($result);
            return response()->json([
                'nextOnuId' => $nextOnuId,
                'card' => $card,
                'processing_time' => 'Fast mode'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting port info (fast): ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function configureOnu(Request $request)
    {
        $request->validate([
            'port' => 'required|integer',
            'card' => 'required|integer',
            'serial_number' => 'required|string',
            'name' => 'required|string',
            'description' => 'required|string',
            'vlan_profile' => 'required|string',
            'pppoe_username' => 'required|string',
            'pppoe_password' => 'required|string',
        ]);

        try {
            $connection = $this->connectToOlt();
            if (!$connection) {
                return response()->json(['error' => 'Tidak dapat terhubung ke OLT'], 500);
            }

            // Get next ONU ID
            $portInfo = $this->executeCommand($connection, "show run interface gpon-olt_1/{$request->card}/{$request->port}");
            $onuId = $this->getNextOnuId($portInfo);

            if ($onuId === null) {
                return response()->json(['error' => 'Tidak dapat menentukan ONU ID berikutnya'], 500);
            }

            // Get VLAN info from SNMP profiles
            $vlanProfiles = $this->getVlanProfilesFromSnmp();
            $vlanInfo = $vlanProfiles[$request->vlan_profile] ?? null;
            
            if (!$vlanInfo) {
                return response()->json(['error' => 'VLAN profile tidak ditemukan: ' . $request->vlan_profile], 400);
            }
            
            // Configure ONU using fast method
            $this->configureOnuSteps($connection, $request, $onuId, $vlanInfo);
            
            fclose($connection);

            Log::info("ONU configuration completed successfully in optimized time");

            return response()->json([
                'success' => true,
                'message' => 'ONU berhasil dikonfigurasi dengan cepat',
                'onu_id' => $onuId,
                'card' => $request->card,
                'port' => $request->port,
                'vlan' => $vlanInfo['vlan'],
                'processing_time' => 'Optimized for speed'
            ]);

        } catch (\Exception $e) {
            Log::error('Error configuring ONU: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function debugRawOutput(Request $request)
    {
        try {
            Log::info('Starting debug connection to OLT');
            
            $connection = $this->connectToOlt();
            if (!$connection) {
                return response()->json(['error' => 'Tidak dapat terhubung ke OLT'], 500);
            }

            Log::info('Connected successfully, executing command');
            
            $result = $this->executeCommand($connection, 'show gpon onu uncfg');
            fclose($connection);

            Log::info('Command executed, result length: ' . strlen($result));

            // Clean the output for JSON response
            $cleanResult = $this->sanitizeOutput($result);

            return response()->json([
                'raw_output' => $cleanResult,
                'lines' => explode("\n", $cleanResult),
                'raw_length' => strlen($result),
                'clean_length' => strlen($cleanResult),
                'connection_success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Debug raw output error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function deleteOnu(Request $request)
    {
        $request->validate([
            'port' => 'required|integer',
            'card' => 'required|integer',
            'onu_id' => 'required|integer',
        ]);

        try {
            $connection = $this->connectToOlt();
            if (!$connection) {
                return response()->json(['error' => 'Tidak dapat terhubung ke OLT'], 500);
            }

            // Execute delete ONU commands using fast method
            $this->executeDeleteOnuSteps($connection, $request->card, $request->port, $request->onu_id);
            
            fclose($connection);

            Log::info("Fast ONU deletion completed successfully");

            return response()->json([
                'success' => true,
                'message' => "ONU {$request->onu_id} berhasil dihapus dengan cepat dari card {$request->card} port {$request->port}",
                'card' => $request->card,
                'port' => $request->port,
                'onu_id' => $request->onu_id,
                'processing_time' => 'Optimized for speed'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting ONU: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getConfiguredOnus(Request $request)
    {
        $port = $request->input('port');
        $card = $request->input('card', 3); // Default to card 2
        
        try {
            $connection = $this->connectToOlt();
            if (!$connection) {
                return response()->json(['error' => 'Tidak dapat terhubung ke OLT'], 500);
            }

            $result = $this->executeCommand($connection, "show run interface gpon-olt_1/{$card}/{$port}");
            fclose($connection);

            $configuredOnus = $this->parseConfiguredOnus($result, $card, $port);
            return response()->json(['onus' => $configuredOnus]);
        } catch (\Exception $e) {
            Log::error('Error getting configured ONUs: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getAvailableCards(Request $request)
    {
        try {
            // Get cards from unconfigured ONUs
            $cards = $this->getCardsFromOnuData();
            
            // If no cards found from ONU data, use fallback methods
            if (empty($cards)) {
                Log::info('No cards found from ONU data, trying SNMP and telnet fallback');
                
                // Try SNMP first
                $cards = $this->getCardsFromSnmp();
                
                // If SNMP fails, fallback to telnet
                if (empty($cards)) {
                    Log::warning('SNMP card detection failed, falling back to telnet');
                    $cards = $this->getCardsFromTelnet();
                }
            }
            
            return response()->json([
                'cards' => $cards,
                'source' => empty($cards) ? 'none' : (count($cards) > 0 && isset($cards[0]['source']) ? $cards[0]['source'] : 'onu_data'),
                'cached' => false
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available cards: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getPortInfoWithCard(Request $request)
    {
        $card = $request->input('card', 2); // Default to card 2
        $port = $request->input('port');
        
        try {
            $connection = $this->connectToOlt();
            if (!$connection) {
                return response()->json(['error' => 'Tidak dapat terhubung ke OLT'], 500);
            }

            $result = $this->executeCommand($connection, "show run interface gpon-olt_1/{$card}/{$port}");
            fclose($connection);

            $nextOnuId = $this->getNextOnuId($result);
            return response()->json([
                'nextOnuId' => $nextOnuId,
                'card' => $card,
                'port' => $port
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting port info with card: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get VLAN profiles from OLT via SNMP
     */
    public function getVlanProfilesFromSnmp()
    {
        try {
            // Check cache first
            if ($this->vlanProfilesCache && $this->cacheExpiry && time() < $this->cacheExpiry) {
                Log::info('Using cached VLAN profiles');
                return $this->vlanProfilesCache;
            }

            Log::info('Fetching VLAN profiles from OLT via SNMP');

            $snmpConfig = $this->getSnmpConfig();
            $oids = $this->getSnmpOids();

            $snmp = new \FreeDSx\Snmp\SnmpClient([
                'host' => $snmpConfig['host'],
                'community' => $snmpConfig['community'],
                'version' => $snmpConfig['version'],
                'timeout' => $snmpConfig['timeout'],
                'retries' => $snmpConfig['retries']
            ]);

            $vlanProfiles = [];

            // Walk through VLAN profile names
            try {
                $profileNames = $snmp->walk($oids['vlan_profile_name']);
                $profileVlans = $snmp->walk($oids['vlan_profile_vlan']);

                foreach ($profileNames as $oid => $name) {
                    $profileIndex = $this->extractIndexFromOid($oid);
                    $profileName = (string) $name->getValue();
                    
                    // Find corresponding VLAN ID
                    $vlanId = null;
                    foreach ($profileVlans as $vlanOid => $vlan) {
                        if ($this->extractIndexFromOid($vlanOid) === $profileIndex) {
                            $vlanId = (int) $vlan->getValue();
                            break;
                        }
                    }

                    if ($profileName && $vlanId) {
                        $vlanProfiles[$profileName] = [
                            'vlan' => $vlanId,
                            'profile' => $profileName,
                            'source' => 'snmp',
                            'index' => $profileIndex
                        ];
                    }
                }

                Log::info('Successfully fetched ' . count($vlanProfiles) . ' VLAN profiles via SNMP');

            } catch (\Exception $e) {
                Log::warning('SNMP walk failed, trying individual gets: ' . $e->getMessage());
                
                // Fallback: try to get specific known profiles
                $vlanProfiles = $this->getKnownVlanProfilesViaSnmp($snmp);
            }

            // If SNMP fails, fall back to hardcoded profiles
            if (empty($vlanProfiles)) {
                Log::warning('SNMP failed, using hardcoded VLAN profiles');
                $vlanProfiles = $this->getHardcodedVlanProfiles();
            }

            // Cache the results
            $this->vlanProfilesCache = $vlanProfiles;
            $this->cacheExpiry = time() + config('olt_snmp.cache.lifetime', 300);

            return $vlanProfiles;

        } catch (\Exception $e) {
            Log::error('Error fetching VLAN profiles via SNMP: ' . $e->getMessage());
            Log::info('Falling back to hardcoded VLAN profiles');
            return $this->getHardcodedVlanProfiles();
        }
    }

    /**
     * Try to get known VLAN profiles via individual SNMP gets
     */
    private function getKnownVlanProfilesViaSnmp($snmp)
    {
        $vlanProfiles = [];
        $oids = $this->getSnmpOids();
        $knownProfiles = [
            'LOKALGR3', 'Kantor', 'Reseler', 'PPPoE', 'PPPoE3', 
            'PPPoE-Reseler', 'PPPoE-Local', 'PPPoE-Kantor-FS'
        ];

        foreach ($knownProfiles as $index => $profileName) {
            try {
                // Try to get VLAN ID for this profile
                $vlanOid = $oids['vlan_profile_vlan'] . "." . ($index + 1);
                $result = $snmp->get($vlanOid);
                
                if ($result) {
                    $vlanId = (int) $result->getValue();
                    $vlanProfiles[$profileName] = [
                        'vlan' => $vlanId,
                        'profile' => $profileName,
                        'source' => 'snmp_individual',
                        'index' => $index + 1
                    ];
                }
            } catch (\Exception $e) {
                Log::debug("Failed to get VLAN for profile {$profileName}: " . $e->getMessage());
            }
        }

        return $vlanProfiles;
    }

    /**
     * Get hardcoded VLAN profiles as fallback
     */
    private function getHardcodedVlanProfiles()
    {
        return [
            'LOKALGR3' => ['vlan' => 1100, 'profile' => 'LOKALGR3', 'source' => 'hardcoded'],
            'Kantor' => ['vlan' => 515, 'profile' => 'Kantor', 'source' => 'hardcoded'],
            'Reseler' => ['vlan' => 414, 'profile' => 'Reseler', 'source' => 'hardcoded'],
            'PPPoE' => ['vlan' => 414, 'profile' => 'PPPoE', 'source' => 'hardcoded'],
            'PPPoE3' => ['vlan' => 1700, 'profile' => 'PPPoE3', 'source' => 'hardcoded'],
            'PPPoE-Reseler' => ['vlan' => 800, 'profile' => 'PPPoE-Reseler', 'source' => 'hardcoded'],
            'PPPoE-Local' => ['vlan' => 448, 'profile' => 'PPPoE-Local', 'source' => 'hardcoded'],
            'PPPoE-Kantor-FS' => ['vlan' => 449, 'profile' => 'PPPoE-Kantor-FS', 'source' => 'hardcoded'],
            'PPPoE-444' => ['vlan' => 444, 'profile' => 'PPPoE-444', 'source' => 'hardcoded'],
            'PPPoE-445' => ['vlan' => 445, 'profile' => 'PPPoE-445', 'source' => 'hardcoded'],
            'PPPoE-446' => ['vlan' => 446, 'profile' => 'PPPoE-446', 'source' => 'hardcoded'],
            'PPPoE-447' => ['vlan' => 447, 'profile' => 'PPPoE-447', 'source' => 'hardcoded'],
            'PPPoE-448' => ['vlan' => 448, 'profile' => 'PPPoE-448', 'source' => 'hardcoded'],
            'PPPoE-449' => ['vlan' => 449, 'profile' => 'PPPoE-449', 'source' => 'hardcoded'],
            'PPPoE-332' => ['vlan' => 332, 'profile' => 'PPPoE-332', 'source' => 'hardcoded'],
            'PPPoE-333' => ['vlan' => 333, 'profile' => 'PPPoE-333', 'source' => 'hardcoded'],
            'Local-Kantor' => ['vlan' => 802, 'profile' => 'Local-Kantor', 'source' => 'hardcoded'],
            'PPPoE-TNL' => ['vlan' => 1891, 'profile' => 'PPPoE-TNL', 'source' => 'hardcoded'],
            'griya456' => ['vlan' => 456, 'profile' => 'griya456', 'source' => 'hardcoded'],
            'griya-456' => ['vlan' => 456, 'profile' => 'griya-456', 'source' => 'hardcoded'],
            '108tes' => ['vlan' => 108, 'profile' => '108tes', 'source' => 'hardcoded'],
            'Temambung' => ['vlan' => 595, 'profile' => 'Temambung', 'source' => 'hardcoded'],
        ];
    }

    /**
     * Extract index from SNMP OID
     */
    private function extractIndexFromOid($oid)
    {
        $parts = explode('.', $oid);
        return end($parts);
    }

    /**
     * Get cards information from OLT via SNMP
     */
    private function getCardsFromSnmp()
    {
        try {
            Log::info('Fetching cards from OLT via SNMP');

            $snmpConfig = $this->getSnmpConfig();
            $oids = $this->getSnmpOids();

            $snmp = new \FreeDSx\Snmp\SnmpClient([
                'host' => $snmpConfig['host'],
                'community' => $snmpConfig['community'],
                'version' => $snmpConfig['version'],
                'timeout' => $snmpConfig['timeout'],
                'retries' => $snmpConfig['retries']
            ]);

            $cards = [];

            // Try to walk card status first
            try {
                $cardStatuses = $snmp->walk($oids['card_status']);
                $cardTypes = [];
                
                // Try to get card types
                try {
                    $cardTypes = $snmp->walk($oids['card_type']);
                } catch (\Exception $e) {
                    Log::debug('Could not get card types via SNMP: ' . $e->getMessage());
                }

                foreach ($cardStatuses as $oid => $status) {
                    $cardIndex = $this->extractCardIndexFromOid($oid);
                    $statusValue = (int) $status->getValue();
                    
                    // Parse card index (usually in format slot.card)
                    $cardParts = explode('.', $cardIndex);
                    if (count($cardParts) >= 2) {
                        $slot = $cardParts[0];
                        $card = $cardParts[1];
                        
                        // Find corresponding card type
                        $cardType = 'Unknown';
                        foreach ($cardTypes as $typeOid => $type) {
                            if ($this->extractCardIndexFromOid($typeOid) === $cardIndex) {
                                $cardType = (string) $type->getValue();
                                break;
                            }
                        }

                        // Only include working/active cards (status 1 or 2)
                        if ($statusValue === 1 || $statusValue === 2) {
                            $cards[] = [
                                'slot' => $slot,
                                'card' => $card,
                                'full_id' => "{$slot}/{$card}",
                                'status' => $this->getCardStatusText($statusValue),
                                'type' => $cardType,
                                'source' => 'snmp'
                            ];
                        }
                    }
                }

                Log::info('Successfully fetched ' . count($cards) . ' cards via SNMP');

            } catch (\Exception $e) {
                Log::warning('SNMP card walk failed, trying alternative OIDs: ' . $e->getMessage());
                
                // Try alternative card detection
                $cards = $this->getCardsFromSnmpAlternative($snmp);
            }

            return $cards;

        } catch (\Exception $e) {
            Log::error('Error fetching cards via SNMP: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Alternative SNMP card detection method
     */
    private function getCardsFromSnmpAlternative($snmp)
    {
        $cards = [];
        $oids = $this->getSnmpOids();
        
        try {
            // Try GPON card specific OID
            if (isset($oids['gpon_card_status'])) {
                $gponCards = $snmp->walk($oids['gpon_card_status']);
                
                foreach ($gponCards as $oid => $status) {
                    $cardIndex = $this->extractCardIndexFromOid($oid);
                    $statusValue = (int) $status->getValue();
                    
                    if ($statusValue === 1) { // Active
                        $cards[] = [
                            'slot' => '1',
                            'card' => $cardIndex,
                            'full_id' => "1/{$cardIndex}",
                            'status' => 'Working',
                            'type' => 'GPON',
                            'source' => 'snmp_alt'
                        ];
                    }
                }
            }
            
            // If still no cards, try individual gets for known slots
            if (empty($cards)) {
                $knownSlots = ['1.2', '1.3', '1.4', '1.5', '1.6', '1.7', '1.8'];
                foreach ($knownSlots as $slotCard) {
                    try {
                        $result = $snmp->get($oids['card_status'] . '.' . $slotCard);
                        if ($result && (int) $result->getValue() === 1) {
                            $parts = explode('.', $slotCard);
                            $cards[] = [
                                'slot' => $parts[0],
                                'card' => $parts[1],
                                'full_id' => implode('/', $parts),
                                'status' => 'Working',
                                'type' => 'GPON',
                                'source' => 'snmp_individual'
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::debug("Failed to get card {$slotCard}: " . $e->getMessage());
                    }
                }
            }

        } catch (\Exception $e) {
            Log::debug('Alternative SNMP card detection failed: ' . $e->getMessage());
        }

        return $cards;
    }

    /**
     * Get cards from telnet as fallback
     */
    private function getCardsFromTelnet()
    {
        try {
            $connection = $this->connectToOlt();
            if (!$connection) {
                Log::warning('Cannot connect to OLT for telnet card detection');
                return $this->getDefaultCards();
            }

            // Get available cards using show card command
            $result = $this->executeCommand($connection, 'show card');
            fclose($connection);

            $cards = $this->parseAvailableCards($result);
            
            // Mark as telnet source
            foreach ($cards as &$card) {
                $card['source'] = 'telnet';
            }
            
            return $cards;
        } catch (\Exception $e) {
            Log::error('Error getting cards via telnet: ' . $e->getMessage());
            return $this->getDefaultCards();
        }
    }

    /**
     * Get default cards when all detection methods fail
     */
    private function getDefaultCards()
    {
        return [
            [
                'slot' => '1',
                'card' => '2',
                'full_id' => '1/2',
                'status' => 'Default',
                'type' => 'GPON',
                'source' => 'default'
            ]
        ];
    }

    /**
     * Extract card index from SNMP OID
     */
    private function extractCardIndexFromOid($oid)
    {
        $parts = explode('.', $oid);
        // Get last two parts for slot.card format
        if (count($parts) >= 2) {
            return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }
        return end($parts);
    }

    /**
     * Convert card status number to text
     */
    private function getCardStatusText($status)
    {
        switch ($status) {
            case 1: return 'Working';
            case 2: return 'Active';
            case 3: return 'Standby';
            case 4: return 'Failed';
            case 5: return 'Offline';
            default: return 'Unknown';
        }
    }

    /**
     * Refresh VLAN profiles from OLT
     */
    public function refreshVlanProfiles(Request $request)
    {
        try {
            // Clear cache
            $this->vlanProfilesCache = null;
            $this->cacheExpiry = null;

            // Get fresh profiles
            $profiles = $this->getVlanProfilesFromSnmp();

            return response()->json([
                'success' => true,
                'message' => 'VLAN profiles refreshed successfully',
                'profiles' => $profiles,
                'count' => count($profiles),
                'source' => isset($profiles[array_key_first($profiles)]['source']) ? 
                           $profiles[array_key_first($profiles)]['source'] : 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Error refreshing VLAN profiles: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to refresh VLAN profiles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get VLAN profiles API endpoint
     */
    public function getVlanProfiles(Request $request)
    {
        try {
            $profiles = $this->getVlanProfilesFromSnmp();

            return response()->json([
                'success' => true,
                'profiles' => $profiles,
                'count' => count($profiles),
                'cached' => $this->vlanProfilesCache !== null,
                'cache_expiry' => $this->cacheExpiry
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting VLAN profiles: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to get VLAN profiles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh cards from OLT
     */
    public function refreshCards(Request $request)
    {
        try {
            // Clear cache
            $this->cardsCache = null;
            $this->cardsCacheExpiry = null;

            // Get fresh cards
            $cards = $this->getCardsFromSnmp();
            
            // If SNMP fails, try telnet
            if (empty($cards)) {
                $cards = $this->getCardsFromTelnet();
            }

            // Cache the results
            if (!empty($cards)) {
                $this->cardsCache = $cards;
                $this->cardsCacheExpiry = time() + config('olt_snmp.cache.lifetime', 300);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cards refreshed successfully',
                'cards' => $cards,
                'count' => count($cards),
                'source' => isset($cards[0]['source']) ? $cards[0]['source'] : 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Error refreshing cards: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to refresh cards: ' . $e->getMessage()
            ], 500);
        }
    }

    private function connectToOlt()
    {
        $connection = fsockopen('10.22.4.254', 23, $errno, $errstr, 5); // Reduced timeout to 5 seconds
        if (!$connection) {
            throw new \Exception("Tidak dapat terhubung ke OLT: $errstr ($errno)");
        }

        // Set socket options for faster response
        stream_set_timeout($connection, 3); // Reduced timeout
        stream_set_blocking($connection, true);

        // Faster login sequence
        $this->readUntilPrompt($connection, 'Username:', 3);
        
        // Send username
        fwrite($connection, "gmdp\r\n");
        fflush($connection);
        
        // Wait for password prompt
        $this->readUntilPrompt($connection, 'Password:', 2);
        
        // Send password
        fwrite($connection, "2020\r\n");
        fflush($connection);
        
        // Wait for command prompt (#)
        $this->readUntilPrompt($connection, '#', 3);

        return $connection;
    }

    private function executeCommand($connection, $command, $waitForPrompt = true)
    {
        // Send command
        fwrite($connection, $command . "\r\n");
        fflush($connection);
        
        if (!$waitForPrompt) {
            // For fast sequential commands, just wait briefly
            usleep(100000); // 0.1 seconds
            return '';
        }
        
        // Reduced initial wait time
        usleep(200000); // 0.2 seconds (reduced from 0.5)
        
        $result = '';
        $timeout = 8; // Reduced timeout to 8 seconds
        $start = time();
        $lastActivity = time();
        
        while (time() - $start < $timeout) {
            $char = fgetc($connection);
            if ($char === false) {
                // Check if we've been idle too long
                if (time() - $lastActivity > 2) {
                    break;
                }
                usleep(50000); // Reduced to 0.05 second
                continue;
            }
            
            $result .= $char;
            $lastActivity = time();
            
            // Optimized prompt detection
            if (preg_match('/[#>]\s*$/', $result) ||
                preg_match('/OLT-[A-Z-]+[#>]\s*$/', $result)) {
                break;
            }
            
            // Handle "More" prompt faster
            if (strpos($result, '----More----') !== false || strpos($result, '--More--') !== false) {
                fwrite($connection, " ");
                fflush($connection);
            }
        }
        
        // Clean the result
        $result = $this->cleanTelnetOutput($result);
        
        return $result;
    }

    private function executeCommandFast($connection, $command)
    {
        // Ultra-fast command execution without extensive logging
        fwrite($connection, $command . "\r\n");
        fflush($connection);
        usleep(30000); // Only 0.03 seconds
        return '';
    }

    private function parseUnconfiguredOnus($output)
    {
        $onus = [];
        $lines = explode("\n", $output);
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            
            // Skip empty lines, headers, and command prompt
            if (empty($line) || 
                strpos($line, 'OnuIndex') !== false || 
                strpos($line, '---') !== false ||
                strpos($line, '=') !== false ||
                strpos($line, '#') !== false ||
                strpos($line, 'State') !== false) {
                continue;
            }
            
            // Log each line being processed
            Log::info("Processing line {$lineNum}: '{$line}'");
            
            // Simple pattern matching for: gpon-onu_1/2/5:1 ZTGEC70802E5 unknown
            if (preg_match('/^gpon-onu_(\d+)\/(\d+)\/(\d+):(\d+)\s+(\w+)(\s+(\w+))?/', $line, $matches)) {
                $onu = [
                    'slot' => $matches[1],
                    'card' => $matches[2], 
                    'port' => $matches[3],
                    'onu_id' => $matches[4],
                    'serial_number' => $matches[5],
                    'state' => isset($matches[7]) && !empty($matches[7]) ? $matches[7] : 'unknown',
                    'raw_line' => $line
                ];
                
                Log::info("Parsed ONU: ", $onu);
                $onus[] = $onu;
            } else {
                Log::info("Line did not match pattern: '{$line}'");
            }
        }
        
        return $onus;
    }

    private function executeDeleteOnuSteps($connection, $card, $port, $onuId)
    {
        Log::info("Starting fast ONU deletion {$onuId} from card {$card} port {$port}");
        
        // Fast batch delete commands
        $this->executeCommand($connection, 'conf t', false);
        $this->executeCommand($connection, "interface gpon-olt_1/{$card}/{$port}", false);
        $this->executeCommand($connection, "no onu {$onuId}", false);
        $this->executeCommand($connection, 'exit', false);
        $this->executeCommand($connection, 'end', false);
        
        // Only wait for final save command
        $this->executeCommand($connection, 'write');
        
        Log::info("Fast ONU deletion completed for ONU {$onuId} from card {$card} port {$port}");
    }

    private function fastDeleteOnuSteps($connection, $card, $port, $onuId)
    {
        Log::info("Ultra-fast ONU deletion {$onuId} from card {$card} port {$port}");
        
        // Ultra-fast batch delete
        $deleteCommands = [
            'conf t',
            "interface gpon-olt_1/{$card}/{$port}",
            "no onu {$onuId}",
            'exit',
            'end',
            'write'
        ];

        $this->executeBatchCommands($connection, $deleteCommands);
        
        Log::info("Ultra-fast ONU deletion completed for ONU {$onuId}");
    }

    private function parseConfiguredOnus($output, $card, $port)
    {
        $onus = [];
        $lines = explode("\n", $output);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Parse line like: onu 33 type ALL-GPON sn ZTGEC70802E5
            if (preg_match('/^\s*onu\s+(\d+)\s+type\s+(\S+)\s+sn\s+(\w+)/', $line, $matches)) {
                $onus[] = [
                    'card' => $card,
                    'port' => $port,
                    'onu_id' => $matches[1],
                    'type' => $matches[2],
                    'serial_number' => $matches[3],
                    'raw_line' => $line
                ];
            }
        }
        
        return $onus;
    }

    private function getNextOnuId($output)
    {
        $usedIds = [];
        $lines = explode("\n", $output);
        
        foreach ($lines as $line) {
            if (preg_match('/onu\s+(\d+)/', $line, $matches)) {
                $usedIds[] = (int)$matches[1];
            }
        }
        
        // Find next available ID starting from 1
        for ($i = 1; $i <= 128; $i++) {
            if (!in_array($i, $usedIds)) {
                return $i;
            }
        }
        
        return null;
    }

    private function configureOnuSteps($connection, $request, $onuId, $vlanInfo)
    {
        $card = $request->card;
        $port = $request->port;
        $serialNumber = $request->serial_number;
        $name = $request->name;
        $description = $request->description;
        $vlan = $vlanInfo['vlan'];
        $vlanProfile = $vlanInfo['profile'];
        $pppoeUsername = $request->pppoe_username;
        $pppoePassword = $request->pppoe_password;

        Log::info("Starting fast ONU configuration for ONU {$onuId} on card {$card} port {$port}");

        // Step 1: Configure ONU (optimized with batch commands)
        $this->executeCommand($connection, 'conf t', false);
        $this->executeCommand($connection, "interface gpon-olt_1/{$card}/{$port}", false);
        $this->executeCommand($connection, "onu {$onuId} type ALL-GPON sn {$serialNumber}", false);
        $this->executeCommand($connection, 'exit');

        // Step 2: Configure ONU interface (batch mode)
        $this->executeCommand($connection, 'conf t', false);
        $this->executeCommand($connection, "interface gpon-onu_1/{$card}/{$port}:{$onuId}", false);
        $this->executeCommand($connection, "name {$name}", false);
        $this->executeCommand($connection, "description {$description}", false);
        $this->executeCommand($connection, 'tcont 1 name PPPoE profile 1000MBPS', false);
        $this->executeCommand($connection, 'gemport 1 name PPPoE tcont 1', false);
        $this->executeCommand($connection, 'gemport 1 traffic-limit upstream UP1000MBPS downstream DW1000MBPS', false);
        $this->executeCommand($connection, "service-port 1 vport 1 user-vlan {$vlan} vlan {$vlan}", false);
        $this->executeCommand($connection, 'port-identification format DSL-FORUM-PON sport 1', false);
        $this->executeCommand($connection, 'pppoe-intermediate-agent enable sport 1', false);
        $this->executeCommand($connection, 'exit');

        // Step 3: Configure ONU management (batch mode)
        $this->executeCommand($connection, "pon-onu-mng gpon-onu_1/{$card}/{$port}:{$onuId}", false);
        $this->executeCommand($connection, "service PPPoE gemport 1 vlan {$vlan}", false);
        $this->executeCommand($connection, "wan-ip 1 mode pppoe username {$pppoeUsername} password {$pppoePassword} vlan-profile {$vlanProfile} host 1", false);
        $this->executeCommand($connection, 'wan-ip 1 ping-response enable traceroute-response enable', false);
        $this->executeCommand($connection, 'security-mgmt 1 state enable mode forward protocol web', false);
        $this->executeCommand($connection, 'end', false);
        
        // Only wait for final command
        $this->executeCommand($connection, 'write');
        
        Log::info("Fast ONU configuration completed for ONU {$onuId}");
    }

    private function fastConfigureOnuSteps($connection, $request, $onuId, $vlanInfo)
    {
        $card = $request->card;
        $port = $request->port;
        $serialNumber = $request->serial_number;
        $name = $request->name;
        $description = $request->description;
        $vlan = $vlanInfo['vlan'];
        $vlanProfile = $vlanInfo['profile'];
        $pppoeUsername = $request->pppoe_username;
        $pppoePassword = $request->pppoe_password;

        Log::info("Starting ultra-fast ONU configuration for ONU {$onuId} on card {$card} port {$port}");

        // Send all commands in one batch with minimal waiting
        $batchCommands = [
            'conf t',
            "interface gpon-olt_1/{$card}/{$port}",
            "onu {$onuId} type ALL-GPON sn {$serialNumber}",
            'exit',
            'conf t',
            "interface gpon-onu_1/{$card}/{$port}:{$onuId}",
            "name {$name}",
            "description {$description}",
            'tcont 1 name PPPoE profile 1000MBPS',
            'gemport 1 name PPPoE tcont 1',
            'gemport 1 traffic-limit upstream UP1000MBPS downstream DW1000MBPS',
            "service-port 1 vport 1 user-vlan {$vlan} vlan {$vlan}",
            'port-identification format DSL-FORUM-PON sport 1',
            'pppoe-intermediate-agent enable sport 1',
            'exit',
            "pon-onu-mng gpon-onu_1/{$card}/{$port}:{$onuId}",
            "service PPPoE gemport 1 vlan {$vlan}",
            "wan-ip 1 mode pppoe username {$pppoeUsername} password {$pppoePassword} vlan-profile {$vlanProfile} host 1",
            'wan-ip 1 ping-response enable traceroute-response enable',
            'security-mgmt 1 state enable mode forward protocol web',
            'end',
            'write'
        ];

        // Execute all commands rapidly
        foreach ($batchCommands as $index => $command) {
            $isLastCommand = ($index === count($batchCommands) - 1);
            $this->executeCommand($connection, $command, $isLastCommand);
        }
        
        Log::info("Ultra-fast ONU configuration completed for ONU {$onuId}");
    }

    private function executeBatchCommands($connection, $commands)
    {
        // Send all commands with minimal delay
        foreach ($commands as $command) {
            fwrite($connection, $command . "\r\n");
            fflush($connection);
            usleep(50000); // Only 0.05 second delay between commands
        }
        
        // Wait for all commands to complete
        usleep(2000000); // 2 seconds total wait
        
        // Read any remaining output
        $result = '';
        $timeout = 3;
        $start = time();
        
        while (time() - $start < $timeout) {
            $char = fgetc($connection);
            if ($char === false) {
                break;
            }
            $result .= $char;
            
            if (preg_match('/[#>]\s*$/', $result)) {
                break;
            }
        }
        
        return $result;
    }

    private function cleanTelnetOutput($data)
    {
        // Remove telnet control characters and ANSI escape sequences
        $data = preg_replace('/\x1b\[[0-9;]*[A-Za-z]/', '', $data); // Remove ANSI escape sequences
        $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data); // Remove control chars except \t, \n, \r
        
        // Remove backspace sequences
        $data = preg_replace('/.\x08/', '', $data);
        
        // Convert to UTF-8 and handle encoding issues
        if (!mb_check_encoding($data, 'UTF-8')) {
            $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
        }
        
        // Remove any remaining invalid UTF-8 sequences
        $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        
        return $data;
    }

    private function sanitizeOutput($data)
    {
        // Remove any non-printable characters except newlines and tabs
        $data = preg_replace('/[^\x20-\x7E\x0A\x0D\x09]/', '', $data);
        
        // Ensure valid UTF-8
        if (!mb_check_encoding($data, 'UTF-8')) {
            $data = mb_convert_encoding($data, 'UTF-8', 'auto');
        }
        
        return $data;
    }

    private function readUntilPrompt($connection, $prompt, $timeout = 5)
    {
        $result = '';
        $start = time();
        
        while (time() - $start < $timeout) {
            $char = fgetc($connection);
            if ($char === false) {
                usleep(50000); // Reduced to 0.05 second
                continue;
            }
            
            $result .= $char;
            
            // Check if we've received the expected prompt
            if (strpos($result, $prompt) !== false) {
                break;
            }
            
            // Early detection for common prompts
            if ($prompt === '#' && preg_match('/[#>]\s*$/', $result)) {
                break;
            }
        }
        
        return $result;
    }

    private function parseAvailableCards($output)
    {
        $cards = [];
        $lines = explode("\n", $output);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Parse card info - format may vary, looking for card numbers
            // Example: 1/2  GTGO  Normal  Working
            if (preg_match('/^(\d+)\/(\d+)\s+\w+\s+\w+\s+Working/i', $line, $matches)) {
                $slot = $matches[1];
                $card = $matches[2];
                
                $cards[] = [
                    'slot' => $slot,
                    'card' => $card,
                    'full_id' => "{$slot}/{$card}",
                    'status' => 'Working'
                ];
            }
            // Alternative format: looking for GPON cards specifically
            elseif (preg_match('/^(\d+)\/(\d+).*GPON.*Working/i', $line, $matches)) {
                $slot = $matches[1];
                $card = $matches[2];
                
                $cards[] = [
                    'slot' => $slot,
                    'card' => $card,
                    'full_id' => "{$slot}/{$card}",
                    'status' => 'Working'
                ];
            }
        }
        
        // If no cards found, return default card 2
        if (empty($cards)) {
            $cards[] = [
                'slot' => '1',
                'card' => '2',
                'full_id' => '1/2',
                'status' => 'Default'
            ];
        }
        
        return $cards;
    }

    /**
     * Get available cards from ONU data
     */
    private function getCardsFromOnuData()
    {
        try {
            Log::info('Getting cards from ONU data');
            
            $connection = $this->connectToOlt();
            if (!$connection) {
                Log::warning('Cannot connect to OLT for ONU data');
                return [];
            }

            $result = $this->executeCommand($connection, 'show gpon onu uncfg');
            fclose($connection);

            // Clean and parse the output
            $cleanResult = $this->sanitizeOutput($result);
            $onus = $this->parseUnconfiguredOnus($cleanResult);
            
            // Extract unique cards from ONU data
            $cardSet = [];
            foreach ($onus as $onu) {
                if (isset($onu['card']) && isset($onu['slot'])) {
                    $cardKey = $onu['slot'] . '/' . $onu['card'];
                    $cardSet[$cardKey] = [
                        'slot' => $onu['slot'],
                        'card' => $onu['card'],
                        'full_id' => $cardKey,
                        'status' => 'Active (has ONUs)',
                        'source' => 'onu_data'
                    ];
                }
            }
            
            $cards = array_values($cardSet);
            
            // If no cards found from ONU data, add default card 3
            if (empty($cards)) {
                Log::info('No cards found from ONU data, adding default card 3');
                $cards[] = [
                    'slot' => '1',
                    'card' => '3',
                    'full_id' => '1/3',
                    'status' => 'Default',
                    'source' => 'default'
                ];
            }
            
            Log::info('Found ' . count($cards) . ' cards from ONU data: ' . implode(', ', array_column($cards, 'full_id')));
            
            return $cards;
            
        } catch (\Exception $e) {
            Log::error('Error getting cards from ONU data: ' . $e->getMessage());
            return [];
        }
    }
}
