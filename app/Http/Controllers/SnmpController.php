<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SnmpController extends Controller
{
public function read(Request $request)
{
    $ip = $request->input('ip', '10.22.4.251');
    $community = $request->input('community', 'fmjro');
    $oid = $request->input('oid', '.1.3.6.1.4.1.2011.5.6.1.1.1.2');
    $port = $request->input('port', '161');

    ini_set('snmp.default_timeout', 2);
    ini_set('snmp.default_retries', 1);
    ini_set('snmp.default_port', $port);

    try {
        $getResult = @snmpget("$ip:$port", $community, $oid);

        if ($getResult !== false) {
            return response()->json([
                'status' => true,
                'mode' => 'get',
                'ip' => $ip,
                'oid' => $oid,
                'result' => $getResult
            ]);
        }

        // Gunakan snmprealwalk agar dapat OID-nya juga
        $walkData = @snmprealwalk("$ip:$port", $community, $oid);

        $formattedResult = [];
        if ($walkData !== false) {
            foreach ($walkData as $oidKey => $value) {
                $explode = explode('.', $oidKey);
                $lastDigit = end($explode);

                $formattedResult[] = [
                    'last_oid_digit' => $lastDigit,
                    'value' => $value,
                ];
            }
        }

        return response()->json([
            'status' => true,
            'mode' => 'walk',
            'ip' => $ip,
            'oid' => $oid,
            'result' => $formattedResult
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'SNMP Error: ' . $e->getMessage()
        ], 500);
    }
}



    public function getUptime(Request $request)
    {
        $ip = $request->input('ip', '10.22.4.254');
        $community = $request->input('community', 'fmjro');
        $oid = '1.3.6.1.2.1.1.3.0'; // sysUpTime OID
        $port = $request->input('port', '161');

        // Ubah port SNMP default jika perlu
        ini_set('snmp.default_port', $port);

        try {
            // SNMP GET untuk uptime
            $result = snmpget($ip, $community, $oid);

            // Parse uptime dari format Timeticks
            $uptimeText = '';
            if (preg_match('/Timeticks: \((\d+)\) (.+)/', $result, $matches)) {
                $timeticks = intval($matches[1]);
                $uptimeText = $matches[2];
                
                // Convert timeticks to seconds (1 timetick = 1/100 second)
                $uptimeSeconds = $timeticks / 100;
                
                // Calculate days, hours, minutes
                $days = floor($uptimeSeconds / 86400);
                $hours = floor(($uptimeSeconds % 86400) / 3600);
                $minutes = floor(($uptimeSeconds % 3600) / 60);
                $seconds = $uptimeSeconds % 60;
                
                $formattedUptime = sprintf('%d days, %02d:%02d:%02d', $days, $hours, $minutes, $seconds);
            } else {
                $formattedUptime = $result;
            }

            return response()->json([
                'status' => true,
                'ip' => $ip,
                'oid' => $oid,
                'result' => $result,
                'uptime_formatted' => $formattedUptime,
                'uptime_text' => $uptimeText
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'SNMP Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
