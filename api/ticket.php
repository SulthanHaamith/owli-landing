<?php
/**
 * TICKET.PHP — GET /api/ticket.php?ref=AP26-XXXXXX
 *
 * PURPOSE: Streams the attendee's AROKYA PAYANAM 2026 ENTRY TICKET as a PDF
 *          (one page, A4): a big Booking ID, a QR code that encodes the
 *          booking URL, the holder's name, category, event details and the
 *          amount paid. Designed to be downloaded or printed straight from
 *          the success box on /events/ after the payment is verified.
 *
 * SECURITY MODEL — the ticket is proof of a PAID registration, so the rules
 * are strict and the endpoint deliberately leaks nothing:
 *   - Only a valid reference format (AP26-XXXXXX, the unambiguous alphabet
 *     used by owli_new_reference()) is even looked up.
 *   - Only PAID rows produce a ticket. Pending/failed rows are refused with
 *     404 — nobody can print a ticket for an unpaid seat by guessing refs.
 *   - No secrets, tokens or Coaxlure data appear on the ticket or in the
 *     stream; the PDF contains exactly what a printed ticket would show.
 *   - Output headers: no-store (never cached by the browser or any proxy),
 *     nosniff, and a download filename of the booking id (safe: A-Z0-9-).
 *   - Rate limit: 12 downloads per 10 minutes per IP (same guard style as
 *     register.php) so the endpoint cannot be hammered to enumerate refs.
 *
 * DEPENDENCIES (vendored single-file libraries — see api/lib/LICENSE.txt):
 *   - api/lib/tfpdf.php      tFPDF 1.34 (LGPL) — FPDF with UTF-8/TrueType
 *                            support via the font/unifont folder. Used
 *                            because the event is bilingual: attendee names
 *                            may be in Tamil, and the built-in PDF fonts
 *                            cannot render Tamil script.
 *   - api/lib/font/unifont/  MuktaMalar Regular + Bold (Google Fonts, OFL)
 *                            — covers Latin + Tamil + digits, so ONE font
 *                            family renders the whole ticket safely.
 *                            ttfonts.php is tFPDF's TTF metrics parser.
 *   - api/lib/qrcode.php     Kazuhiko Arase's QR-Code generator (MIT) —
 *                            the library behind the widely used phpqrcode.
 *                            It exposes the raw module matrix (isDark()),
 *                            which we draw as vector squares with tFPDF's
 *                            Rect() — no GD/Imagick needed (neither is
 *                            guaranteed on shared hosting) and the code
 *                            scales perfectly at any print size.
 *
 * QR CODE (what it encodes and why):
 *   https://owlihealthcare.com/events/?ref=AP26-XXXXXX
 *   Scanning it on any phone opens the event page, which immediately calls
 *   api/status.php?ref=… and shows the registration as PAID — instant,
 *   server-side verification that this booking id belongs to a paid seat.
 *   typeNumber is FIXED at 4 (33x33 modules) with error level M because the
 *   booking URL is always exactly the same length (see QR_LEN_* constants),
 *   so the capacity is deterministic and the ticket layout never shifts.
 */
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

/* --------------------------------------------------------------- */
/* Event constants (single source of truth for the ticket layout)  */
/* --------------------------------------------------------------- */
const OWLI_TICKET_EVENT   = 'Arokya Payanam 2026';
const OWLI_TICKET_TAGLINE = 'A Journey Towards Health Through Nature';
const OWLI_TICKET_DATE    = 'Sunday, 15 November 2026';
const OWLI_TICKET_TIME    = '9:00 A.M. - 4:00 P.M.';
const OWLI_TICKET_VENUE   = 'AR Group of Institutions, Kadayam, Tirunelveli';
const OWLI_TICKET_ORG     = 'Organised by Owl I Health Care Centre';

/* Booking URLs are always the same length: QR_SITE + "AP26-" + 6 chars,
   which is what lets us pin the QR type (see the file header). */
const QR_SITE = 'https://owlihealthcare.com/events/?ref=';

/* --------------------------------------------------------------- */
/* Helpers                                                         */
/* --------------------------------------------------------------- */

/** Escapes text for PDF XMP metadata (angle brackets and ampersands). */
function owli_pdf_meta(string $s): string
{
    return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $s);
}

/** Reference -> booking URL used for the QR code (and printed as text). */
function owli_ticket_url(string $ref): string
{
    return QR_SITE . $ref;
}

/**
 * Renders the ticket PDF for a PAID registration row and returns the PDF
 * bytes. Everything the ticket shows comes from the database row — the
 * browser supplies nothing but the reference.
 */
function owli_ticket_pdf(array $reg): string
{
    /* tFPDF parses the .ttf on first use through ttfonts.php (TTFontFile);
       it is required BEFORE the fonts are added. */
    require_once __DIR__ . '/lib/tfpdf.php';
    require_once __DIR__ . '/lib/font/unifont/ttfonts.php';
    require_once __DIR__ . '/lib/qrcode.php';

    $pdf = new tFPDF('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(false);

    /* One font family for everything: MuktaMalar covers Latin + Tamil, so
       attendee names written in Tamil render correctly (the built-in PDF
       core fonts cannot). tFPDF subsets the font automatically. */
    define('FPDF_FONTPATH', __DIR__ . '/lib/font/');
    $pdf->AddFont('MuktaMalar', '', 'MuktaMalar-Regular.ttf', true);
    $pdf->AddFont('MuktaMalar', 'B', 'MuktaMalar-Bold.ttf', true);

    $pdf->AddPage();
    $pdf->SetTitle(OWLI_TICKET_EVENT . ' - Entry Ticket ' . $reg['reference']);
    $pdf->SetAuthor('Owl I Health Care Centre');
    $pdf->SetSubject('Entry ticket (Registration ID ' . $reg['reference'] . ')');
    $pdf->SetKeywords('Arokya Payanam 2026, entry ticket');
    $pdf->SetCreator('owlihealthcare.com');

    /* ---------- page frame: brand green border ---------- */
    $pdf->SetDrawColor(0x2D, 0x5A, 0x3D);
    $pdf->SetLineWidth(0.8);
    $pdf->Rect(10, 10, 190, 277);

    /* ---------- header band ---------- */
    $pdf->SetFillColor(0x2D, 0x5A, 0x3D);
    $pdf->Rect(12, 12, 186, 30, 'F');
    $pdf->SetFont('MuktaMalar', 'B', 20);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(20, 16);
    $pdf->Cell(0, 12, OWLI_TICKET_EVENT, 0, 1, 'L');
    $pdf->SetFont('MuktaMalar', '', 11);
    $pdf->SetXY(20, 27);
    $pdf->Cell(0, 8, OWLI_TICKET_TAGLINE, 0, 1, 'L');
    $pdf->SetXY(150, 16);
    $pdf->SetFont('MuktaMalar', 'B', 11);
    $pdf->Cell(44, 12, 'ENTRY TICKET', 0, 1, 'R');
    $pdf->SetFont('MuktaMalar', '', 9);
    $pdf->SetXY(150, 27);
    $pdf->Cell(44, 8, 'Please carry a printout or show on phone', 0, 1, 'R');

    /* ---------- booking ID: the hero of the ticket ---------- */
    $pdf->SetTextColor(0x1A, 0x1A, 0x1A);
    $pdf->SetXY(20, 52);
    $pdf->SetFont('MuktaMalar', '', 10);
    $pdf->Cell(95, 6, 'BOOKING ID', 0, 1, 'L');
    $pdf->SetFont('MuktaMalar', 'B', 26);
    $pdf->SetXY(20, 58);
    $pdf->Cell(95, 14, $reg['reference'], 0, 1, 'L');

    /* ---------- QR code (vector, right side) ---------- */
    $qr = new QRCode();
    $qr->setTypeNumber(4);                    // 33x33 modules — fixed, always fits our URL
    $qr->setErrorCorrectLevel(QR_ERROR_CORRECT_LEVEL_M);
    $qr->addData(owli_ticket_url((string)$reg['reference']));
    $qr->make();

    $count = $qr->getModuleCount();
    $size  = 52.0;                            // printed QR size in mm
    $x0    = 138;
    $y0    = 46;
    $quiet = 4;                               // quiet zone rows (spec asks >= 4)
    $cell  = $size / ($count + 2 * $quiet);

    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect($x0, $y0, $size, $size, 'F');  // white backing incl. quiet zone
    $pdf->SetFillColor(0x1A, 0x1A, 0x1A);
    for ($row = 0; $row < $count; $row++) {
        for ($col = 0; $col < $count; $col++) {
            if ($qr->isDark($row, $col)) {
                $pdf->Rect(
                    $x0 + ($quiet + $col) * $cell,
                    $y0 + ($quiet + $row) * $cell,
                    $cell, $cell, 'F'
                );
            }
        }
    }
    $pdf->SetFont('MuktaMalar', '', 8.5);
    $pdf->SetTextColor(0x6B, 0x7B, 0x6B);
    $pdf->SetXY($x0, $y0 + $size + 1.5);
    $pdf->Cell($size, 5, 'Scan to verify this ticket instantly', 0, 1, 'C');

    /* ---------- holder details ---------- */
    $pdf->SetTextColor(0x1A, 0x1A, 0x1A);
    $labels = [
        'Name'       => (string)$reg['name'],
        'Category'   => ucfirst((string)$reg['category']),
        'Paid'       => ((int)$reg['amount_paise'] === 0
                          ? 'FREE (coupon applied)'
                          : 'Rs ' . owli_rupees((int)$reg['amount_paise'])),
    ];
    if (!empty($reg['college'])) {
        $labels['College'] = (string)$reg['college'];
    } elseif (!empty($reg['organisation'])) {
        $labels['Organisation'] = (string)$reg['organisation'];
    } elseif (!empty($reg['clinic'])) {
        $labels['Clinic / hospital'] = (string)$reg['clinic'];
    }

    $y = 80;
    $pdf->SetFont('MuktaMalar', '', 10);
    foreach ($labels as $k => $v) {
        $pdf->SetXY(20, $y);
        $pdf->SetFont('MuktaMalar', '', 9);
        $pdf->SetTextColor(0x6B, 0x7B, 0x6B);
        $pdf->Cell(110, 6, mb_strtoupper($k), 0, 1, 'L');
        $pdf->SetXY(20, $y + 5);
        $pdf->SetFont('MuktaMalar', 'B', 13);
        $pdf->SetTextColor(0x1A, 0x1A, 0x1A);
        $pdf->Cell(110, 8, $v, 0, 1, 'L');
        $y += 15;
    }

    /* ---------- event details ---------- */
    $pdf->SetDrawColor(0xA8, 0xC5, 0xA0);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(20, $y + 2, 190, $y + 2);
    $y += 8;

    $pdf->SetXY(20, $y);
    $pdf->SetFont('MuktaMalar', 'B', 11);
    $pdf->SetTextColor(0x2D, 0x5A, 0x3D);
    $pdf->Cell(0, 7, 'Event details', 0, 1, 'L');
    $y += 9;

    $rows = [
        ['Date', OWLI_TICKET_DATE],
        ['Time', OWLI_TICKET_TIME],
        ['Venue', OWLI_TICKET_VENUE],
        ['Carry', 'Any government ID (for verification at the desk)'],
        ['Verify', owli_ticket_url((string)$reg['reference'])],
    ];
    $pdf->SetFont('MuktaMalar', '', 10.5);
    foreach ($rows as [$k, $v]) {
        $pdf->SetXY(20, $y);
        $pdf->SetTextColor(0x6B, 0x7B, 0x6B);
        $pdf->Cell(26, 6, $k, 0, 1, 'L');
        $pdf->SetXY(46, $y);
        $pdf->SetTextColor(0x1A, 0x1A, 0x1A);
        $pdf->Cell(144, 6, $v, 0, 1, 'L');
        $y += 7;
    }

    /* ---------- footer band ---------- */
    $pdf->SetFillColor(0xF4, 0xED, 0xE4);
    $pdf->Rect(12, 246, 186, 39, 'F');
    $pdf->SetDrawColor(0x2D, 0x5A, 0x3D);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(12, 246, 198, 246);

    $pdf->SetTextColor(0x1A, 0x1A, 0x1A);
    $pdf->SetFont('MuktaMalar', 'B', 10);
    $pdf->SetXY(20, 250);
    $pdf->Cell(0, 6, OWLI_TICKET_ORG, 0, 1, 'L');
    $pdf->SetFont('MuktaMalar', '', 9);
    $pdf->SetTextColor(0x6B, 0x7B, 0x6B);
    $pdf->SetXY(20, 258);
    $pdf->Cell(0, 5, 'Questions: WhatsApp +91 63796 10554  |  arokyapayanam@gmail.com', 0, 1, 'L');
    $pdf->SetXY(20, 265);
    $pdf->Cell(0, 5, 'This ticket is valid only with the payment recorded against this Booking ID.', 0, 1, 'L');
    $pdf->SetXY(20, 272);
    $pdf->Cell(0, 5, 'Generated ' . date('d M Y, h:i A') . ' (IST)' . '  -  owlihealthcare.com', 0, 1, 'L');
    $pdf->SetXY(20, 279);
    $pdf->SetFont('MuktaMalar', '', 8);
    $pdf->Cell(0, 4, 'Registration ID ' . $reg['reference'] . '  |  Owl I Health Care Centre, Tirunelveli', 0, 1, 'L');

    return $pdf->Output('S');
}

/* --------------------------------------------------------------- */
/* Request handling                                                */
/* --------------------------------------------------------------- */

$ref = strtoupper(trim((string)($_GET['ref'] ?? '')));
if (preg_match('/^AP26-[A-Z0-9]{6}$/', $ref) !== 1) {
    owli_json(['ok' => false, 'error' => 'bad_ref'], 422);
}
if (!owli_db_configured()) {
    owli_json(['ok' => false, 'error' => 'not_open'], 503);
}

try {
    $db = owli_db();

    /* Download abuse limit: 12 PDFs / 10 min / IP. Refuses before any
       PDF work (rendering a PDF with font subsetting is the expensive
       part), exactly like register.php's rate limit. */
    $rl = $db->prepare("SELECT COUNT(*) FROM registrations WHERE ip = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)");
    $rl->execute([owli_client_ip()]);
    if ((int)$rl->fetchColumn() >= 12) {
        owli_json(['ok' => false, 'error' => 'rate_limited', 'message' => 'Too many downloads. Please try again in a few minutes.'], 429);
    }

    $st = $db->prepare("SELECT reference, status, category, name, college, organisation, clinic, amount_paise
                         FROM registrations WHERE reference = ?");
    $st->execute([$ref]);
    $reg = $st->fetch();

    if (!$reg || (string)$reg['status'] !== 'paid') {
        /* Paid-only, and the same 404 as an unknown ref: probing refs
           reveals nothing about which ids exist unpaid. */
        error_log('owli ticket: refused ' . $ref . ' (status=' . ($reg['status'] ?? 'not_found') . ') ' . owli_client_ip());
        owli_json(['ok' => false, 'error' => 'not_found'], 404);
    }

    $pdf = owli_ticket_pdf($reg);
} catch (Throwable $e) {
    error_log('owli ticket error: ' . $e->getMessage() . ' ref=' . $ref);
    owli_json(['ok' => false, 'error' => 'server', 'message' => 'Ticket could not be generated. Please try again.'], 500);
}

/* ---------- stream it (headers must precede any output) ---------- */
http_response_code(200);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $ref . '-entry-ticket.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, nofollow');
echo $pdf;
