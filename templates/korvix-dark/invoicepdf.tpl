<?php

/**
 * Korvix branded invoice PDF (overrides the twenty-one parent).
 *
 * WHMCS evaluates this file as plain PHP with $pdf (TCPDF) and the invoice
 * data in scope. Every access is guarded — a fatal here breaks EVERY
 * invoice download, and deleting this file falls back to the stock layout.
 */

$brandRgb = [26, 95, 208];   // Korvix blue
$inkRgb = [34, 40, 49];
$mutedRgb = [120, 128, 145];

$font = isset($pdfFont) ? $pdfFont : 'freesans';
$dim = $pdf->getPageDimensions();
$pageW = $dim['wk'];
$lm = isset($dim['lm']) ? $dim['lm'] : 15;
$rm = isset($dim['rm']) ? $dim['rm'] : 15;
$innerW = $pageW - $lm - $rm;

$s = function ($v) {
    return htmlspecialchars(trim((string) $v), ENT_QUOTES, 'UTF-8');
};

// ---------------------------------------------------------------- header
$logo = '';
foreach (['logo.png', 'logo.jpg'] as $cand) {
    if (defined('ROOTDIR') && file_exists(ROOTDIR . '/assets/img/' . $cand)) {
        $logo = ROOTDIR . '/assets/img/' . $cand;
        break;
    }
}
if ($logo !== '') {
    $pdf->Image($logo, $lm, 16, 42, 0, '', '', '', false, 300);
} else {
    $pdf->SetXY($lm, 16);
    $pdf->SetFont($font, 'B', 22);
    $pdf->SetTextColor($brandRgb[0], $brandRgb[1], $brandRgb[2]);
    $pdf->Cell(90, 10, 'KORVIX', 0, 1, 'L');
}

$pdf->SetXY($lm, 16);
$pdf->SetFont($font, 'B', 12);
$pdf->SetTextColor($inkRgb[0], $inkRgb[1], $inkRgb[2]);
$pdf->Cell($innerW, 5, isset($companyname) ? $companyname : 'Korvix', 0, 1, 'R');
$pdf->SetFont($font, '', 8.5);
$pdf->SetTextColor($mutedRgb[0], $mutedRgb[1], $mutedRgb[2]);
if (isset($companyaddress) && is_array($companyaddress)) {
    foreach ($companyaddress as $line) {
        $line = trim((string) $line);
        if ($line !== '') {
            $pdf->SetX($lm);
            $pdf->Cell($innerW, 4, $line, 0, 1, 'R');
        }
    }
}
if (!empty($taxCode)) {
    $pdf->SetX($lm);
    $pdf->Cell($innerW, 4, (isset($taxIdLabel) && $taxIdLabel !== '' ? $taxIdLabel : 'ABN') . ': ' . trim((string) $taxCode), 0, 1, 'R');
}

// brand rule under the header
$ruleY = max(34, $pdf->GetY() + 4);
$pdf->SetFillColor($brandRgb[0], $brandRgb[1], $brandRgb[2]);
$pdf->Rect($lm, $ruleY, $innerW, 1.1, 'F');

// ------------------------------------------------- title + status stamp
$pdf->SetXY($lm, $ruleY + 6);
$pdf->SetFont($font, 'B', 17);
$pdf->SetTextColor($inkRgb[0], $inkRgb[1], $inkRgb[2]);
$pdf->Cell($innerW / 2, 8, 'Tax Invoice #' . (isset($invoicenum) ? $invoicenum : ''), 0, 0, 'L');

$statusRaw = isset($status) ? (string) $status : '';
$stampText = strtoupper($statusRaw);
$stampRgb = [150, 156, 170];
if (strcasecmp($statusRaw, 'Paid') === 0) {
    $stampRgb = [29, 158, 85];
} elseif (strcasecmp($statusRaw, 'Unpaid') === 0) {
    $overdue = false;
    if (!empty($duedate)) {
        $dueTs = strtotime(str_replace('/', '-', (string) $duedate));
        $overdue = $dueTs !== false && $dueTs < strtotime('today');
    }
    $stampText = $overdue ? 'OVERDUE' : 'DUE';
    $stampRgb = $overdue ? [192, 57, 43] : [199, 124, 17];
} elseif (strcasecmp($statusRaw, 'Cancelled') === 0 || strcasecmp($statusRaw, 'Refunded') === 0) {
    $stampRgb = [120, 128, 145];
}
$pdf->SetFont($font, 'B', 13);
$pdf->SetTextColor($stampRgb[0], $stampRgb[1], $stampRgb[2]);
$pdf->Cell($innerW / 2, 8, $stampText, 0, 1, 'R');

// -------------------------------------------- bill-to + invoice details
$topY = $pdf->GetY() + 5;
$pdf->SetXY($lm, $topY);
$pdf->SetFont($font, 'B', 9);
$pdf->SetTextColor($mutedRgb[0], $mutedRgb[1], $mutedRgb[2]);
$pdf->Cell($innerW / 2, 4.5, 'INVOICED TO', 0, 1, 'L');
$pdf->SetFont($font, '', 9);
$pdf->SetTextColor($inkRgb[0], $inkRgb[1], $inkRgb[2]);
if (isset($clientsdetails) && is_array($clientsdetails)) {
    $billLines = [];
    if (!empty($clientsdetails['companyname'])) {
        $billLines[] = $clientsdetails['companyname'];
    }
    $person = trim((string) (isset($clientsdetails['fullname']) ? $clientsdetails['fullname']
        : trim((isset($clientsdetails['firstname']) ? $clientsdetails['firstname'] : '') . ' '
            . (isset($clientsdetails['lastname']) ? $clientsdetails['lastname'] : ''))));
    if ($person !== '') {
        $billLines[] = $person;
    }
    foreach (['address1', 'address2'] as $k) {
        if (!empty($clientsdetails[$k])) {
            $billLines[] = $clientsdetails[$k];
        }
    }
    $cityLine = trim(
        (isset($clientsdetails['city']) ? $clientsdetails['city'] : '') . ' '
        . (isset($clientsdetails['state']) ? $clientsdetails['state'] : '') . ' '
        . (isset($clientsdetails['postcode']) ? $clientsdetails['postcode'] : '')
    );
    if ($cityLine !== '') {
        $billLines[] = $cityLine;
    }
    if (!empty($clientsdetails['country'])) {
        $billLines[] = $clientsdetails['country'];
    }
    foreach ($billLines as $line) {
        $pdf->SetX($lm);
        $pdf->Cell($innerW / 2, 4.2, (string) $line, 0, 1, 'L');
    }
}
$leftEndY = $pdf->GetY();

$pdf->SetXY($lm + $innerW / 2, $topY);
$meta = [];
if (!empty($datecreated)) {
    $meta[] = ['Invoice date', $datecreated];
}
if (!empty($duedate)) {
    $meta[] = ['Due date', $duedate];
}
if (!empty($datepaid) && strcasecmp($statusRaw, 'Paid') === 0) {
    $meta[] = ['Date paid', preg_replace('/ .*$/', '', (string) $datepaid)];
}
if (!empty($paymentmethod)) {
    $meta[] = ['Payment method', $paymentmethod];
}
foreach ($meta as $row) {
    $pdf->SetX($lm + $innerW / 2);
    $pdf->SetFont($font, 'B', 9);
    $pdf->SetTextColor($mutedRgb[0], $mutedRgb[1], $mutedRgb[2]);
    $pdf->Cell(34, 4.5, $row[0], 0, 0, 'L');
    $pdf->SetFont($font, '', 9);
    $pdf->SetTextColor($inkRgb[0], $inkRgb[1], $inkRgb[2]);
    $pdf->Cell($innerW / 2 - 34, 4.5, (string) $row[1], 0, 1, 'L');
}
$pdf->SetY(max($leftEndY, $pdf->GetY()) + 6);

// ------------------------------------------------------------ items table
$tbl = '<table width="100%" cellpadding="6" cellspacing="0" border="0">'
    . '<tr style="background-color:#0b0f1a;color:#ffffff;">'
    . '<td width="78%" style="font-weight:bold;">Description</td>'
    . '<td width="22%" align="right" style="font-weight:bold;">Amount</td></tr>';
$rowIndex = 0;
if (isset($invoiceitems) && is_array($invoiceitems)) {
    foreach ($invoiceitems as $item) {
        $bg = ($rowIndex++ % 2 === 0) ? '#ffffff' : '#f4f6fa';
        $tbl .= '<tr style="background-color:' . $bg . ';color:#222831;">'
            . '<td width="78%">' . nl2br($s(isset($item['description']) ? $item['description'] : '')) . '</td>'
            . '<td width="22%" align="right">' . $s(isset($item['amount']) ? $item['amount'] : '') . '</td></tr>';
    }
}
$totalRow = function ($label, $value, $bold = false, $color = '#222831') use ($s) {
    $w = $bold ? 'font-weight:bold;' : '';
    return '<tr style="background-color:#ffffff;">'
        . '<td width="78%" align="right" style="' . $w . 'color:#787f91;">' . $s($label) . '</td>'
        . '<td width="22%" align="right" style="' . $w . 'color:' . $color . ';">' . $s($value) . '</td></tr>';
};
$tbl .= '<tr><td colspan="2" style="border-bottom:1px solid #d9deea;font-size:2px;">&nbsp;</td></tr>';
if (isset($subtotal)) {
    $tbl .= $totalRow('Subtotal', $subtotal);
}
if (!empty($taxrate) && (float) $taxrate > 0 && isset($tax)) {
    $tbl .= $totalRow((isset($taxname) && $taxname !== '' ? $taxname : 'GST') . ' @ ' . (float) $taxrate . '%', $tax);
}
if (!empty($taxrate2) && (float) $taxrate2 > 0 && isset($tax2)) {
    $tbl .= $totalRow((isset($taxname2) && $taxname2 !== '' ? $taxname2 : 'Tax 2') . ' @ ' . (float) $taxrate2 . '%', $tax2);
}
if (isset($credit) && (string) $credit !== '' && (string) $credit !== '0.00') {
    $tbl .= $totalRow('Credit', $credit);
}
if (isset($total)) {
    $tbl .= $totalRow('Total', $total, true, '#1a5fd0');
}
$tbl .= '</table>';
$pdf->SetFont($font, '', 9);
$pdf->writeHTML($tbl, true, false, false, false, '');

// ------------------------------------------------------------ transactions
if (isset($transactions) && is_array($transactions) && count($transactions) > 0) {
    $pdf->Ln(4);
    $pdf->SetFont($font, 'B', 10);
    $pdf->SetTextColor($inkRgb[0], $inkRgb[1], $inkRgb[2]);
    $pdf->Cell($innerW, 6, 'Payments', 0, 1, 'L');
    $ttbl = '<table width="100%" cellpadding="5" cellspacing="0" border="0">'
        . '<tr style="background-color:#eef1f7;color:#222831;font-weight:bold;">'
        . '<td width="25%">Date</td><td width="30%">Method</td>'
        . '<td width="25%">Transaction ID</td><td width="20%" align="right">Amount</td></tr>';
    foreach ($transactions as $trans) {
        if (!is_array($trans)) {
            continue;
        }
        $ttbl .= '<tr style="color:#222831;">'
            . '<td width="25%">' . $s(isset($trans['date']) ? $trans['date'] : '') . '</td>'
            . '<td width="30%">' . $s(isset($trans['gateway']) ? $trans['gateway'] : '') . '</td>'
            . '<td width="25%">' . $s(isset($trans['transid']) ? $trans['transid'] : '') . '</td>'
            . '<td width="20%" align="right">' . $s(isset($trans['amount']) ? $trans['amount'] : '') . '</td></tr>';
    }
    $ttbl .= '</table>';
    $pdf->SetFont($font, '', 9);
    $pdf->writeHTML($ttbl, true, false, false, false, '');
}

// ------------------------------------------------------------------ notes
if (!empty($notes)) {
    $pdf->Ln(4);
    $pdf->SetFont($font, '', 8.5);
    $pdf->SetTextColor($mutedRgb[0], $mutedRgb[1], $mutedRgb[2]);
    $pdf->MultiCell($innerW, 4, 'Notes: ' . $notes, 0, 'L');
}

// ----------------------------------------------------------------- footer
$pdf->SetY(-26);
$pdf->SetFillColor($brandRgb[0], $brandRgb[1], $brandRgb[2]);
$pdf->Rect($lm, $pdf->GetY(), $innerW, 0.6, 'F');
$pdf->Ln(2.5);
$pdf->SetFont($font, '', 8);
$pdf->SetTextColor($mutedRgb[0], $mutedRgb[1], $mutedRgb[2]);
$pdf->Cell($innerW, 4, 'Thanks for connecting with Korvix — fast, local NBN with real local support.', 0, 1, 'C');
$pdf->Cell($innerW, 4, '03 4130 5012  ·  korvix.co  ·  Terms: korvix.co/terms', 0, 1, 'C');
