<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

/**
 * Validates and normalises the qualification hand-off parameters (vt_*)
 * carried from the qualify page into checkout. Shared by the order
 * hand-off endpoint and the cart hook fallback.
 */
class SignupCapture
{
    /**
     * @param array $get typically $_GET
     * @return array<string,string> session payload; empty when no valid LOC ID
     */
    public static function fromRequest(array $get): array
    {
        $locId = strtoupper(trim((string) ($get['vt_locid'] ?? '')));
        if (!preg_match('/^LOC\d{9,15}$/', $locId)) {
            return [];
        }

        $signup = ['Location ID' => $locId];

        $avc = strtoupper(trim((string) ($get['vt_avc'] ?? '')));
        if (preg_match('/^(AVC\d{12}|\d{5})$/', $avc)) {
            $signup['Churn AVC'] = $avc;
            // The customer authorised the transfer on the qualification page.
            $signup['Authority Date'] = date('Y-m-d');
        }

        $ntd = strtoupper(trim((string) ($get['vt_ntd'] ?? '')));
        $port = strtoupper(trim((string) ($get['vt_port'] ?? '')));
        if (preg_match('/^NTD[0-9A-Z]{6,20}$/', $ntd) && preg_match('/^[0-9A-Z][0-9A-Z-]{0,19}$/', $port)) {
            $signup['NTD ID'] = $ntd;
            $signup['UNI-D Port'] = $port;
            // Display-only: the human port name ("UNI-D 1") and whether the
            // auto-select picked it rather than the customer.
            $plabel = trim((string) ($get['vt_portlabel'] ?? ''));
            if (preg_match('/^[0-9A-Za-z][0-9A-Za-z \/-]{0,29}$/', $plabel)) {
                $signup['Port Label'] = $plabel;
            }
            if (($get['vt_auto'] ?? '') === '1') {
                $signup['Port Auto'] = '1';
            }
        }

        // Display-only extras for the cart card (never written to fields —
        // CustomFields only writes its known field names).
        $addr = trim(preg_replace('/[^\PC ]/u', '', strip_tags((string) ($get['vt_addr'] ?? ''))) ?? '');
        if ($addr !== '') {
            $signup['Address'] = mb_substr($addr, 0, 120);
        }
        $tech = trim(preg_replace('/[^\PC ]/u', '', strip_tags((string) ($get['vt_tech'] ?? ''))) ?? '');
        if ($tech !== '') {
            $signup['Technology'] = mb_substr($tech, 0, 60);
        }

        return $signup;
    }
}
