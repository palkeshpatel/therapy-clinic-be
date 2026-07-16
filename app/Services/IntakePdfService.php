<?php

namespace App\Services;

use App\Models\PatientIntake;

class IntakePdfService
{
    private function formatNameAge(?string $jsonOrString): string
    {
        if (!$jsonOrString) return '';
        $decoded = json_decode($jsonOrString, true);
        if (is_array($decoded)) {
            $name = $decoded['name'] ?? '';
            $age = $decoded['age'] ?? '';
            if ($name && $age) return $this->e($name . ' (' . $age . ' yrs)');
            if ($name) return $this->e($name);
            if ($age) return $this->e($age . ' yrs');
            return '';
        }
        return $this->e($jsonOrString);
    }
    private PatientIntake $intake;
    private string $logoData = '';

    // Brand colors
    const PRIMARY      = '#1a3c5e';   // Deep navy
    const PRIMARY_LIGHT = '#2563a8';  // Medium blue
    const ACCENT       = '#0ea5e9';   // Sky blue
    const SUCCESS      = '#16a34a';   // Green
    const DANGER       = '#dc2626';   // Red
    const WARNING      = '#d97706';   // Amber
    const PURPLE       = '#7c3aed';   // Purple (for pedigree)
    const BG_HEADER    = '#0f2744';   // Very dark blue for header
    const BG_SECTION   = '#dbeafe';   // Light blue bg for section titles
    const BG_ALT       = '#f0f7ff';   // Very light blue for alt rows
    const BG_WHITE     = '#ffffff';
    const TEXT_DARK    = '#0f172a';
    const TEXT_MID     = '#334155';
    const TEXT_LIGHT   = '#64748b';
    const BORDER       = '#bfdbfe';   // Blue-tinted border

    public function __construct(PatientIntake $intake, string $logoData = '')
    {
        $this->intake = $intake;
        $this->logoData = $logoData;
    }

    /* ── Helpers ───────────────────────────────────────────── */

    private function e(mixed $val, string $fallback = 'N/A'): string
    {
        if ($val === null || $val === '') return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
        return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
    }

    private function ucf(mixed $val, string $fallback = 'N/A'): string
    {
        if ($val === null || $val === '') return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
        return htmlspecialchars(ucfirst((string) $val), ENT_QUOTES, 'UTF-8');
    }

    private function arr(mixed $val): array
    {
        if (is_array($val)) return $val;
        if (is_string($val) && $val !== '') {
            $d = json_decode($val, true);
            return is_array($d) ? $d : [];
        }
        return [];
    }

    private function fmtDate(mixed $date): string
    {
        if (!$date) return 'N/A';
        try {
            if ($date instanceof \Carbon\Carbon) return $date->format('d M Y');
            return date('d M Y', strtotime((string) $date));
        } catch (\Throwable $e) {
            return 'N/A';
        }
    }

    private function badge(mixed $val, string $fallback = 'N/A'): string
    {
        $text = ($val === null || $val === '') ? $fallback : ucfirst((string)$val);
        $lower = strtolower($text);

        if (in_array($lower, ['yes', 'present', 'normal', 'adequate', 'good', 'independent', 'completed'])) {
            $bg = '#dcfce7'; $color = '#15803d';
        } elseif (in_array($lower, ['no', 'absent', 'none', 'n/a'])) {
            $bg = '#f1f5f9'; $color = '#475569';
        } elseif (in_array($lower, ['delay', 'delayed', 'poor', 'impaired', 'absent/delay'])) {
            $bg = '#fee2e2'; $color = '#dc2626';
        } elseif (in_array($lower, ['partial', 'emerging', 'moderate', 'with assistance', 'with support'])) {
            $bg = '#fef9c3'; $color = '#92400e';
        } else {
            $bg = '#eff6ff'; $color = '#1d4ed8';
        }

        return '<span style="display:inline-block;background:' . $bg . ';color:' . $color . ';font-weight:bold;font-size:8px;padding:1px 5px;border-radius:3px;">'
            . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    /* ── CSS ───────────────────────────────────────────────── */

    private function styles(): string
    {
        return '<style>
            * { box-sizing: border-box; }
            body {
                font-family: DejaVu Sans, Arial, sans-serif;
                font-size: 9.5px;
                color: ' . self::TEXT_DARK . ';
                margin: 0; padding: 0;
                background: #fff;
                line-height: 1.5;
            }

            /* ── Header ── */
            .pdf-header {
                background: ' . self::BG_HEADER . ';
                color: #fff;
                padding: 28px 22px 28px 22px;
                margin-bottom: 0;
            }
            .clinic-name {
                font-size: 16px;
                font-weight: bold;
                color: #ffffff;
                margin: 0 0 2px 0;
                letter-spacing: 0.3px;
            }
            .clinic-tagline {
                font-size: 9px;
                color: #93c5fd;
                margin: 0;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }
            .doc-title-bar {
                background: ' . self::PRIMARY_LIGHT . ';
                color: #fff;
                text-align: left;
                font-size: 12px;
                font-weight: bold;
                padding: 7px 22px;
                letter-spacing: 1px;
                text-transform: uppercase;
                margin-bottom: 14px;
            }
            .patient-meta-bar {
                background: ' . self::ACCENT . ';
                color: #fff;
                padding: 6px 22px;
                font-size: 9px;
                font-weight: bold;
                letter-spacing: 0.3px;
                margin-bottom: 14px;
            }
            .patient-meta-bar span { margin-right: 24px; }

            /* ── Section Title ── */
            table.section-title-tbl {
                width: 100%;
                border-collapse: collapse;
                margin: 16px 0 8px 0;
                page-break-after: avoid;
                page-break-inside: avoid;
                border-radius: 3px;
                overflow: hidden;
            }
            table.section-title-tbl td.sec-num-cell {
                background: ' . self::ACCENT . ';
                color: #fff;
                width: 26px;
                text-align: center;
                font-size: 10px;
                font-weight: bold;
                padding: 6px 5px;
                vertical-align: middle;
            }
            table.section-title-tbl td.sec-text-cell {
                background: ' . self::PRIMARY . ';
                color: #ffffff;
                font-size: 10.5px;
                font-weight: bold;
                padding: 6px 10px;
                letter-spacing: 0.4px;
                vertical-align: middle;
            }

            /* ── Sub Title ── */
            .sub-title {
                font-size: 9px;
                font-weight: bold;
                color: ' . self::PRIMARY_LIGHT . ';
                text-transform: uppercase;
                letter-spacing: 0.6px;
                border-bottom: 1.5px solid ' . self::ACCENT . ';
                padding-bottom: 3px;
                margin: 10px 0 6px 0;
            }

            /* ── Info Table ── */
            table.info-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 6px;
                page-break-inside: avoid;
            }
            table.info-table tr:nth-child(even) td { background: ' . self::BG_ALT . '; }
            table.info-table td {
                padding: 4px 8px;
                vertical-align: top;
                border: 1px solid ' . self::BORDER . ';
            }
            table.info-table td.label {
                font-weight: bold;
                color: ' . self::PRIMARY . ';
                width: 22%;
                background: #eff6ff;
                font-size: 8.5px;
            }
            table.info-table td.value {
                color: ' . self::TEXT_DARK . ';
                font-size: 9px;
            }

            /* ── Grid Table ── */
            table.grid-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 8px;
                page-break-inside: avoid;
            }
            table.grid-table thead tr th {
                background: ' . self::PRIMARY . ';
                color: #ffffff;
                font-size: 8.5px;
                font-weight: bold;
                padding: 5px 8px;
                border: 1px solid ' . self::PRIMARY_LIGHT . ';
                text-align: left;
            }
            table.grid-table tbody tr:nth-child(even) td { background: ' . self::BG_ALT . '; }
            table.grid-table tbody tr:hover td { background: #dbeafe; }
            table.grid-table tbody tr td {
                border: 1px solid ' . self::BORDER . ';
                padding: 4px 8px;
                font-size: 8.5px;
                vertical-align: middle;
            }

            /* ── Remarks ── */
            .remarks-block {
                background: #fffbeb;
                border-left: 4px solid ' . self::WARNING . ';
                border: 1px solid #fde68a;
                padding: 6px 10px;
                margin: 6px 0 10px 0;
                border-radius: 3px;
                font-size: 8.5px;
                color: #78350f;
            }

            /* ── Pedigree ── */
            .pedigree-box {
                border: 1px solid ' . self::BORDER . ';
                border-radius: 6px;
                background: ' . self::BG_ALT . ';
                padding: 12px;
                margin-bottom: 10px;
                page-break-inside: avoid;
                text-align: center;
            }

            /* ── Summary Cards ── */
            .card-row { width: 100%; margin-bottom: 10px; }
            .card-row td {
                width: 25%;
                padding: 8px;
                background: ' . self::BG_HEADER . ';
                color: #fff;
                border-radius: 4px;
                text-align: center;
                border: 3px solid #fff;
            }
            .card-row td .card-val {
                font-size: 16px; font-weight: bold; color: ' . self::ACCENT . ';
            }
            .card-row td .card-lbl {
                font-size: 7.5px; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.5px;
            }

            /* ── Page break ── */
            .page-break { page-break-after: always; }

            /* ── Footer ── */
            .pdf-footer {
                margin-top: 30px;
                border-top: 2px solid ' . self::PRIMARY . ';
                padding-top: 8px;
                font-size: 7.5px;
                color: ' . self::TEXT_LIGHT . ';
                text-align: center;
            }

            /* ── Signature Row ── */
            table.sig-table {
                width: 100%;
                margin-top: 24px;
                page-break-inside: avoid;
            }
            table.sig-table td { padding: 8px 12px; }
            .sig-line {
                border-bottom: 1.5px solid ' . self::PRIMARY . ';
                width: 80%;
                display: inline-block;
                margin-top: 30px;
            }
            .sig-label {
                font-size: 8px;
                color: ' . self::TEXT_LIGHT . ';
                text-transform: uppercase;
                letter-spacing: 0.4px;
                margin-top: 4px;
            }

            /* ── Status dot ── */
            .dot-green { color: ' . self::SUCCESS . '; font-weight: bold; }
            .dot-red   { color: ' . self::DANGER  . '; font-weight: bold; }
            .dot-amber { color: ' . self::WARNING . '; font-weight: bold; }
        </style>';
    }

    /* ── Header ───────────────────────────────────────────── */

    private function header(): string
    {
        $i = $this->intake;
        $name  = $this->e($i->child_name ?? 'Patient');
        $dob   = $this->fmtDate($i->dob);
        $age   = $i->age ? $this->e($i->age) . ' yrs' : 'N/A';
        $gender = $this->ucf($i->gender);
        $date  = $this->fmtDate($i->date_of_assessment);
        $status = strtoupper($i->status ?? 'DRAFT');
        $statusBg = ($i->status === 'completed') ? '#16a34a' : '#d97706';

        $logoHtml = '';
        if ($this->logoData) {
            $logoHtml = '<td style="vertical-align:middle;width:170px;padding-right:20px;">
                <img src="' . $this->logoData . '" style="height:150px;width:150px;display:block;" />
            </td>';
        }

        return '
        <div class="pdf-header">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    ' . $logoHtml . '
                    <td style="vertical-align:middle;">
                        <p class="clinic-name">Helping Hands Child Development &amp; Education Centre</p>
                        <p class="clinic-tagline">Clinical Assessment Suite &bull; Patient Intake Record</p>
                    </td>
                    <td style="vertical-align:middle;text-align:right;width:1;">
                       
                    </td>
                </tr>
            </table>
        </div>
        <div class="doc-title-bar">
           Record ID &nbsp;&nbsp;    
          #INK-' . str_pad($i->id ?? 0, 4, '0', STR_PAD_LEFT) . '
        </div>  
        <div class="patient-meta-bar">
            <span>&#128100; ' . $name . '</span>
            <span>&#128197; DOB: ' . $dob . '</span>
            <span>&#9889; Age: ' . $age . '</span>
            <span>&#9792;&#9794; Gender: ' . $gender . '</span>
            <span>&#128203; Assessment: ' . $date . '</span>
        </div>';
    }

    /* ── Section Title helper ──────────────────────────────── */
    private function sectionTitle(int $num, string $title): string
    {
        // $title is already safe HTML (may contain &amp; entities) — do NOT double-encode
        return '<table class="section-title-tbl"><tr>'
            . '<td class="sec-num-cell">' . $num . '</td>'
            . '<td class="sec-text-cell">' . $title . '</td>'
            . '</tr></table>';
    }

    /* ── Section 1 ─────────────────────────────────────────── */
    private function section1(): string
    {
        $i = $this->intake;
        $prevTherapy = $this->arr($i->previous_therapy);
        $prevText = count($prevTherapy) > 0 ? $this->e(implode(', ', $prevTherapy)) : '<em style="color:#64748b;">None Documented</em>';

        return $this->sectionTitle(1, 'Demographic Details') . '
        <table class="info-table">
            <tr>
                <td class="label">Child Name</td>
                <td class="value" colspan="3" style="font-weight:bold;font-size:10px;color:' . self::PRIMARY . ';">' . $this->e($i->child_name) . '</td>
            </tr>
            <tr>
                <td class="label">Date of Birth</td>
                <td class="value">' . $this->fmtDate($i->dob) . '</td>
                <td class="label">Age / Gender</td>
                <td class="value">' . ($i->age ? $this->e($i->age) . ' Years' : 'N/A') . ' &nbsp;/&nbsp; ' . $this->ucf($i->gender) . '</td>
            </tr>
            <tr>
                <td class="label">Father\'s Name</td>
                <td class="value">' . $this->e($i->father_name) . '</td>
                <td class="label">Occ. / Phone</td>
                <td class="value">' . $this->e($i->father_occupation) . ' &nbsp;/&nbsp; ' . $this->e($i->father_phone) . '</td>
            </tr>
            <tr>
                <td class="label">Mother\'s Name</td>
                <td class="value">' . $this->e($i->mother_name) . '</td>
                <td class="label">Occ. / Phone</td>
                <td class="value">' . $this->e($i->mother_occupation) . ' &nbsp;/&nbsp; ' . $this->e($i->mother_phone) . '</td>
            </tr>
            <tr>
                <td class="label">Email Address</td>
                <td class="value">' . $this->e($i->email) . '</td>
                <td class="label">School &amp; Grade</td>
                <td class="value">' . $this->e($i->school_grade) . '</td>
            </tr>
            <tr>
                <td class="label">Informant</td>
                <td class="value">' . $this->e($i->informant) . '</td>
                <td class="label">Assessment Date</td>
                <td class="value">' . $this->fmtDate($i->date_of_assessment) . '</td>
            </tr>
            <tr>
                <td class="label">Home Address</td>
                <td class="value" colspan="3">' . $this->e($i->address) . '</td>
            </tr>
            <tr>
                <td class="label">Referral By</td>
                <td class="value" colspan="3">' . $this->e($i->referral_by) . '</td>
            </tr>
            <tr>
                <td class="label">Previous Therapy</td>
                <td class="value" colspan="3">' . $prevText . '</td>
            </tr>
            <tr>
                <td class="label">Chief Complaint</td>
                <td class="value" colspan="3" style="font-style:italic;color:' . self::PRIMARY . ';">' . $this->e($i->chief_complaint, 'None') . '</td>
            </tr>
        </table>';
    }

    /* ── Section 2 ─────────────────────────────────────────── */
    private function section2(): string
    {
        $i = $this->intake;
        $pregHistory = $this->arr($i->natal_pregnancy_history);
        $pregText    = count($pregHistory) > 0 ? $this->e(implode(', ', $pregHistory)) : 'Normal Pregnancy';

        $conditions = [];
        if ($i->perinatal_anxiety)             $conditions[] = 'Anxiety';
        if ($i->perinatal_depression)          $conditions[] = 'Depression';
        if ($i->perinatal_social_withdrawal)   $conditions[] = 'Social Withdrawal';
        if ($i->perinatal_eating_difficulties) $conditions[] = 'Eating Difficulties';
        if ($i->perinatal_sleeping)            $conditions[] = 'Sleeping Issues';
        $condText = count($conditions) > 0 ? implode(', ', array_map(fn($c) => '<span style="background:#fee2e2;color:#dc2626;padding:1px 4px;border-radius:2px;font-size:8px;">' . htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . '</span>', $conditions)) : '<span class="dot-green">&#10003; No maternal tensions reported</span>';

        $perinatalOther = $i->perinatal_other ? '<tr><td class="label">Other Notes</td><td class="value" colspan="3">' . $this->e($i->perinatal_other) . '</td></tr>' : '';
        $childRemark    = $i->child_remark    ? '<tr><td class="label">Child Remarks</td><td class="value" colspan="3">' . $this->e($i->child_remark) . '</td></tr>' : '';

        return $this->sectionTitle(2, 'Personal &amp; Birth History') . '

        <div class="sub-title">Mother\'s Natal History</div>
        <table class="info-table">
            <tr>
                <td class="label">Maternal Age at Delivery</td>
                <td class="value">' . ($i->natal_mother_age ? $this->e($i->natal_mother_age) . ' Years' : 'N/A') . '</td>
                <td class="label">Gestation Term</td>
                <td class="value">' . $this->ucf($i->natal_gestation) . '</td>
            </tr>
            <tr>
                <td class="label">Mother Name &amp; Age</td>
                <td class="value">' . $this->formatNameAge($i->natal_mother_name_age) . '</td>
                <td class="label">Father Name &amp; Age</td>
                <td class="value">' . $this->formatNameAge($i->natal_father_name_age) . '</td>
            </tr>
            <tr>
                <td class="label">Delivery Place / Type</td>
                <td class="value">' . $this->e($i->natal_place_delivery) . ' / ' . $this->ucf($i->natal_delivery_type) . '</td>
                <td class="label">Vaccination History</td>
                <td class="value">' . $this->e($i->natal_vaccination_history) . '</td>
            </tr>
            <tr>
                <td class="label">Pregnancy History</td>
                <td class="value" colspan="3">' . $pregText . '</td>
            </tr>
        </table>

        <div class="sub-title">Perinatal &amp; Postnatal History</div>
        <table class="info-table">
            <tr>
                <td class="label">Perinatal Condition</td>
                <td class="value">' . $this->e($i->perinatal_medical_condition, 'Normal') . '</td>
                <td class="label">Medication</td>
                <td class="value">' . $this->e($i->perinatal_medication, 'None') . '</td>
            </tr>
            <tr>
                <td class="label">Maternal Tensions</td>
                <td class="value" colspan="3">' . $condText . '</td>
            </tr>
            ' . $perinatalOther . '
            <tr>
                <td class="label">Postnatal Complication</td>
                <td class="value">' . $this->e($i->postnatal_complication, 'No complications') . '</td>
                <td class="label">Postnatal Concerns</td>
                <td class="value">' . $this->e($i->postnatal_concerns, 'Good') . '</td>
            </tr>
        </table>

        <div class="sub-title">Child Birth History</div>
        <table class="info-table">
            <tr>
                <td class="label">Birth Weight</td>
                <td class="value">' . $this->e($i->child_birth_weight) . '</td>
                <td class="label">NICU Admission</td>
                <td class="value">' . $this->badge($i->child_nicu_admission, 'No') . '</td>
            </tr>
            <tr>
                <td class="label">Delay Cry</td>
                <td class="value">' . $this->badge($i->child_birth_cry, 'Present') . '</td>
                <td class="label">Neonatal Jaundice</td>
                <td class="value">' . $this->badge($i->child_jaundice, 'Absent') . '</td>
            </tr>
            <tr>
                <td class="label">Child Convulsions</td>
                <td class="value">' . $this->badge($i->child_convulsions, 'Absent') . '</td>
                <td class="label">Birth Asphyxia</td>
                <td class="value">' . $this->badge($i->child_birth_asphyxia, 'Absent') . '</td>
            </tr>
            <tr>
                <td class="label">Congenital Anomaly</td>
                <td class="value" colspan="3">' . $this->e($i->child_congenital_anomaly, 'None') . '</td>
            </tr>
            ' . $childRemark . '
        </table>';
    }

    /* ── Section 3 ─────────────────────────────────────────── */
    private function section3(): string
    {
        $i = $this->intake;
        $medRemark = $i->med_remark ? '<tr><td class="label">Medical Remarks</td><td class="value" colspan="3">' . $this->e($i->med_remark) . '</td></tr>' : '';
        return $this->sectionTitle(3, 'Past Medical &amp; Surgical History') . '
        <table class="info-table">
            <tr>
                <td class="label">Prev. Hospitalization</td>
                <td class="value">' . $this->badge($i->med_prev_hospitalization, 'No') . '</td>
                <td class="label">Prev. Infection</td>
                <td class="value">' . $this->badge($i->med_prev_infection, 'No') . '</td>
            </tr>
            <tr>
                <td class="label">Seizure History</td>
                <td class="value">' . $this->badge($i->med_seizure_history, 'No') . '</td>
                <td class="label">Medication History</td>
                <td class="value">' . $this->badge($i->med_medication_history, 'No') . '</td>
            </tr>
            <tr>
                <td class="label">Surgical History</td>
                <td class="value">' . $this->badge($i->med_surgical_history, 'No') . '</td>
                <td class="label">Blood Transfusion</td>
                <td class="value">' . $this->badge($i->med_blood_transfusion, 'No') . '</td>
            </tr>
            ' . $medRemark . '
        </table>';
    }

    /* ── Section 4 ─────────────────────────────────────────── */
    private function section4(): string
    {
        $i = $this->intake;
        return $this->sectionTitle(4, 'Current Medical History') . '
        <table class="info-table">
            <tr>
                <td class="label">Current Conditions</td>
                <td class="value" colspan="3">' . $this->e($i->current_medical_condition, 'None') . '</td>
            </tr>
            <tr>
                <td class="label">Current Medication</td>
                <td class="value" colspan="3">' . $this->e($i->current_medication, 'None') . '</td>
            </tr>
            <tr>
                <td class="label">Allergies (General)</td>
                <td class="value">' . $this->e($i->current_allergy_history, 'None') . '</td>
                <td class="label">Medication Allergy</td>
                <td class="value">' . $this->e($i->current_medication_allergy, 'None') . '</td>
            </tr>
        </table>';
    }

    /* ── Section 5 ─────────────────────────────────────────── */
    private function section5(): string
    {
        $i = $this->intake;
        $milestones = [
            ['Smile',                   'milestone_social_smile',          '1–3 months'],
            ['Neck Control',            'milestone_neck_holding',          '2–4 months'],
            ['Roll Over',               'milestone_roll_over',             '4–6 months'],
            ['Cooing',                  'milestone_cooing',                '4–6 months'],
            ['Sitting Independently',   'milestone_sitting_independently', '6–8 months'],
            ['Babbling',                'milestone_babbling',              '6–9 months'],
            ['Crawling',                'milestone_crawling',              '8–10 months'],
            ['Standing Independently',  'milestone_standing_independently','10–12 months'],
            ['Walking Independently',   'milestone_walking_independently', '11–15 months'],
            ['Meaningful Single Words', 'milestone_use_of_meaningful_word','12–18 months'],
            ['Phrases',                 'milestone_phrases',               '15–20 months'],
            ['Simple Sentence',         'milestone_simple_sentence',       '23–30 months'],
            ['Complex Sentence',        'milestone_complex_sentence',      '24–36 months'],
            ['Toilet Control',          'milestone_toilet_control',        '36–48 months'],
        ];

        $rows = '';
        foreach ($milestones as $m) {
            $val   = (string)($i->{$m[1]} ?? '');
            $lower = strtolower($val);
            if ($lower === 'delay' || $lower === 'delayed') {
                $statusCell = '<span style="background:#fee2e2;color:#dc2626;font-weight:bold;padding:2px 6px;border-radius:3px;font-size:8px;">&#9888; DELAYED</span>';
            } elseif (in_array($lower, ['normal', 'achieved', 'yes', 'present'])) {
                $statusCell = '<span style="background:#dcfce7;color:#15803d;font-weight:bold;padding:2px 6px;border-radius:3px;font-size:8px;">&#10003; NORMAL</span>';
            } elseif ($val === '') {
                $statusCell = '<span style="color:#94a3b8;font-style:italic;font-size:8px;">Not Documented</span>';
            } else {
                $statusCell = '<span style="background:#eff6ff;color:#1d4ed8;font-weight:bold;padding:2px 6px;border-radius:3px;font-size:8px;">' . htmlspecialchars(ucfirst($val), ENT_QUOTES, 'UTF-8') . '</span>';
            }
            $rows .= '<tr>
                <td>' . $this->e($m[0]) . '</td>
                <td style="color:' . self::TEXT_LIGHT . ';font-style:italic;">' . $this->e($m[2]) . '</td>
                <td>' . $statusCell . '</td>
            </tr>';
        }

        $remark = $i->milestone_remark ? '<div class="remarks-block"><strong>&#128204; Milestone Remarks:</strong> ' . $this->e($i->milestone_remark) . '</div>' : '';

        return $this->sectionTitle(5, 'Developmental Milestones') . '
        <table class="grid-table">
            <thead>
                <tr>
                    <th style="width:38%;">Developmental Milestone</th>
                    <th style="width:30%;">Expected Normal Range</th>
                    <th style="width:32%;">Status</th>
                </tr>
            </thead>
            <tbody>' . $rows . '</tbody>
        </table>' . $remark;
    }

    /* ── Section 6 ─────────────────────────────────────────── */
    private function section6(): string
    {
        $i = $this->intake;
        $famHistory = $this->arr($i->family_history);
        $famText    = count($famHistory) > 0 ? implode(', ', array_map(fn($f) => '<span style="background:#fef3c7;color:#92400e;padding:1px 4px;border-radius:2px;font-size:8px;">' . htmlspecialchars($f, ENT_QUOTES, 'UTF-8') . '</span>', $famHistory)) : '<span class="dot-green">&#10003; None reported</span>';

        $famRemark      = $i->family_remark   ? '<tr><td class="label">Family Summary</td><td class="value" colspan="3">' . $this->e($i->family_remark) . '</td></tr>' : '';
        $pedigreeRemark = $i->pedigree_remarks ? '<div class="remarks-block"><strong>&#128204; Pedigree Remarks:</strong> ' . $this->e($i->pedigree_remarks) . '</div>' : '';

        $pedigreeData = $this->buildPedigreeSvg();
        if (is_array($pedigreeData)) {
            $svgStr = $pedigreeData['svg'];
            $w = $pedigreeData['width'];
            $h = $pedigreeData['height'];
            $pedigreeHtml = '<div class="pedigree-box" style="text-align:center;margin:10px 0;">
                <img src="data:image/svg+xml;base64,' . base64_encode($svgStr) . '" width="' . $w . '" height="' . $h . '" style="display:inline-block;" />
            </div>';
        } else {
            $pedigreeHtml = $pedigreeData;
        }

        return $this->sectionTitle(6, 'Family &amp; Pedigree History') . '
        <table class="info-table">
            <tr>
                <td class="label">Family Structure</td>
                <td class="value">' . $this->ucf($i->family_structure) . '</td>
                <td class="label">Consanguinity</td>
                <td class="value">' . $this->e($i->family_consanguinity) . '</td>
            </tr>
            <tr>
                <td class="label">Sibling Info</td>
                <td class="value">' . $this->e($i->sibling_info) . '</td>
                <td class="label">Sibling Age(s)</td>
                <td class="value">' . $this->e($i->sibling_age) . '</td>
            </tr>
            <tr>
                <td class="label">Hereditary Conditions</td>
                <td class="value" colspan="3">' . $famText . '</td>
            </tr>
            ' . $famRemark . '
        </table>

        <div class="sub-title">Pedigree Tree Chart</div>
        <div class="pedigree-box">' . $pedigreeHtml . '</div>
        ' . $pedigreeRemark;
    }

    private function buildPedigreeSvg(): array
    {
        $i = $this->intake;
        $pedigree = $i->pedigree;
        if (!$pedigree || !$pedigree->family_data) {
            return [
                'svg' => '<svg width="520" height="200" viewBox="0 0 520 200" xmlns="http://www.w3.org/2000/svg"><rect width="520" height="200" fill="#f8fafc" rx="8" stroke="#e2e8f0"/><text x="260" y="100" text-anchor="middle" font-size="12" fill="#94a3b8">No pedigree chart data recorded.</text></svg>',
                'width' => 520,
                'height' => 200
            ];
        }

        $members = json_decode($pedigree->family_data, true);
        if (!is_array($members) || empty($members)) {
            return [
                'svg' => '<svg width="520" height="200" viewBox="0 0 520 200" xmlns="http://www.w3.org/2000/svg"><rect width="520" height="200" fill="#f8fafc" rx="8" stroke="#e2e8f0"/><text x="260" y="100" text-anchor="middle" font-size="12" fill="#94a3b8">No pedigree chart data recorded.</text></svg>',
                'width' => 520,
                'height' => 200
            ];
        }

        // Layout settings
        $Y_G1 = 50;  
        $Y_G2 = 180; 
        $Y_G3 = 310; 
        $Y_G4 = 440; 

        // Extract individuals
        $patient = null;
        $father = null;
        $mother = null;
        $pGrandfather = null;
        $pGrandmother = null;
        $mGrandfather = null;
        $mGrandmother = null;
        $spouse = null;

        $siblings = [];
        $pUnclesAunts = [];
        $mUnclesAunts = [];
        $children = [];

        foreach ($members as $m) {
            if ($m['id'] === 'patient') $patient = $m;
            elseif ($m['id'] === 'father') $father = $m;
            elseif ($m['id'] === 'mother') $mother = $m;
            elseif ($m['id'] === 'p_grandfather') $pGrandfather = $m;
            elseif ($m['id'] === 'p_grandmother') $pGrandmother = $m;
            elseif ($m['id'] === 'm_grandfather') $mGrandfather = $m;
            elseif ($m['id'] === 'm_grandmother') $mGrandmother = $m;
            elseif ($m['id'] === 'spouse') $spouse = $m;
            elseif ($m['relation'] === 'brother' || $m['relation'] === 'sister') $siblings[] = $m;
            elseif ($m['relation'] === 'p_uncle' || $m['relation'] === 'p_aunt') $pUnclesAunts[] = $m;
            elseif ($m['relation'] === 'm_uncle' || $m['relation'] === 'm_aunt') $mUnclesAunts[] = $m;
            elseif ($m['relation'] === 'son' || $m['relation'] === 'daughter') $children[] = $m;
        }

        if (!$patient) {
            return [
                'svg' => '<svg width="520" height="200" viewBox="0 0 520 200" xmlns="http://www.w3.org/2000/svg"><rect width="520" height="200" fill="#f8fafc" rx="8" stroke="#e2e8f0"/><text x="260" y="100" text-anchor="middle" font-size="12" fill="#94a3b8">Patient node missing in pedigree tree.</text></svg>',
                'width' => 520,
                'height' => 200
            ];
        }

        $nodes = [];
        $edges = [];

        // 1. Position Patient & Siblings (Generation 3)
        $allSiblings = array_merge($siblings, [$patient]);
        $totalSiblings = count($allSiblings);
        foreach ($allSiblings as $index => $sib) {
            $defaultX = ($index - ($totalSiblings - 1) / 2) * 130;
            $nodes[$sib['id']] = [
                'id' => $sib['id'],
                'x' => isset($sib['x']) ? $sib['x'] : $defaultX,
                'y' => isset($sib['y']) ? $sib['y'] : $Y_G3,
                'gender' => $sib['gender'],
                'relation' => $sib['relation'],
                'name' => $sib['name'] ?: $sib['relationLabel'],
                'status' => $sib['status'],
                'living' => $sib['living'],
                'condition' => $sib['condition'] ?? ''
            ];
        }

        $patientX = $nodes['patient']['x'];
        $patientY = $nodes['patient']['y'];

        // 2. Position Parents (Generation 2)
        $fatherX = $father && isset($father['x']) ? $father['x'] : -70;
        $fatherY = $father && isset($father['y']) ? $father['y'] : $Y_G2;
        $motherX = $mother && isset($mother['x']) ? $mother['x'] : 70;
        $motherY = $mother && isset($mother['y']) ? $mother['y'] : $Y_G2;

        if ($father) {
            $nodes['father'] = [
                'id' => 'father',
                'x' => $fatherX,
                'y' => $fatherY,
                'gender' => $father['gender'],
                'relation' => 'father',
                'name' => $father['name'] ?: $father['relationLabel'],
                'status' => $father['status'],
                'living' => $father['living'],
                'condition' => $father['condition'] ?? ''
            ];
        }
        if ($mother) {
            $nodes['mother'] = [
                'id' => 'mother',
                'x' => $motherX,
                'y' => $motherY,
                'gender' => $mother['gender'],
                'relation' => 'mother',
                'name' => $mother['name'] ?: $mother['relationLabel'],
                'status' => $mother['status'],
                'living' => $mother['living'],
                'condition' => $mother['condition'] ?? ''
            ];
        }

        // Marriage Node for Parents
        if ($father && $mother) {
            $mx = ($fatherX + $motherX) / 2 + 20;
            $my = ($fatherY + $motherY) / 2 + 20;
            $nodes['m_parents'] = [
                'id' => 'm_parents',
                'type' => 'marriage',
                'x' => $mx,
                'y' => $my
            ];
            $edges[] = ['source' => 'father', 'sh' => 'right', 'target' => 'm_parents', 'th' => 'left', 'type' => 'straight'];
            $edges[] = ['source' => 'mother', 'sh' => 'left', 'target' => 'm_parents', 'th' => 'right', 'type' => 'straight'];
            foreach ($allSiblings as $sib) {
                $edges[] = ['source' => 'm_parents', 'sh' => 'bottom', 'target' => $sib['id'], 'th' => 'top', 'type' => 'step'];
            }
        } else {
            $activeParentId = $father ? 'father' : ($mother ? 'mother' : null);
            if ($activeParentId) {
                foreach ($allSiblings as $sib) {
                    $edges[] = ['source' => $activeParentId, 'sh' => 'bottom', 'target' => $sib['id'], 'th' => 'top', 'type' => 'step'];
                }
            }
        }

        // 3. Paternal Uncles & Aunts (Left of Father)
        foreach ($pUnclesAunts as $index => $ua) {
            $defaultX = $fatherX - 120 - ($index * 110);
            $nodes[$ua['id']] = [
                'id' => $ua['id'],
                'x' => isset($ua['x']) ? $ua['x'] : $defaultX,
                'y' => isset($ua['y']) ? $ua['y'] : $Y_G2,
                'gender' => $ua['gender'],
                'relation' => $ua['relation'],
                'name' => $ua['name'] ?: $ua['relationLabel'],
                'status' => $ua['status'],
                'living' => $ua['living'],
                'condition' => $ua['condition'] ?? ''
            ];
        }

        // 4. Maternal Uncles & Aunts (Right of Mother)
        foreach ($mUnclesAunts as $index => $ua) {
            $defaultX = $motherX + 120 + ($index * 110);
            $nodes[$ua['id']] = [
                'id' => $ua['id'],
                'x' => isset($ua['x']) ? $ua['x'] : $defaultX,
                'y' => isset($ua['y']) ? $ua['y'] : $Y_G2,
                'gender' => $ua['gender'],
                'relation' => $ua['relation'],
                'name' => $ua['name'] ?: $ua['relationLabel'],
                'status' => $ua['status'],
                'living' => $ua['living'],
                'condition' => $ua['condition'] ?? ''
            ];
        }

        // 5. Paternal Grandparents (Generation 1)
        if ($pGrandfather || $pGrandmother) {
            $gfX = $pGrandfather && isset($pGrandfather['x']) ? $pGrandfather['x'] : ($fatherX - 80);
            $gfY = $pGrandfather && isset($pGrandfather['y']) ? $pGrandfather['y'] : $Y_G1;
            $gmX = $pGrandmother && isset($pGrandmother['x']) ? $pGrandmother['x'] : ($fatherX + 20);
            $gmY = $pGrandmother && isset($pGrandmother['y']) ? $pGrandmother['y'] : $Y_G1;

            if ($pGrandfather) {
                $nodes['p_grandfather'] = [
                    'id' => 'p_grandfather',
                    'x' => $gfX,
                    'y' => $gfY,
                    'gender' => $pGrandfather['gender'],
                    'relation' => 'p_grandfather',
                    'name' => $pGrandfather['name'] ?: $pGrandfather['relationLabel'],
                    'status' => $pGrandfather['status'],
                    'living' => $pGrandfather['living'],
                    'condition' => $pGrandfather['condition'] ?? ''
                ];
            }
            if ($pGrandmother) {
                $nodes['p_grandmother'] = [
                    'id' => 'p_grandmother',
                    'x' => $gmX,
                    'y' => $gmY,
                    'gender' => $pGrandmother['gender'],
                    'relation' => 'p_grandmother',
                    'name' => $pGrandmother['name'] ?: $pGrandmother['relationLabel'],
                    'status' => $pGrandmother['status'],
                    'living' => $pGrandmother['living'],
                    'condition' => $pGrandmother['condition'] ?? ''
                ];
            }

            if ($pGrandfather && $pGrandmother) {
                $mx = ($gfX + $gmX) / 2 + 20;
                $my = ($gfY + $gmY) / 2 + 20;
                $nodes['m_p_grandparents'] = [
                    'id' => 'm_p_grandparents',
                    'type' => 'marriage',
                    'x' => $mx,
                    'y' => $my
                ];
                $edges[] = ['source' => 'p_grandfather', 'sh' => 'right', 'target' => 'm_p_grandparents', 'th' => 'left', 'type' => 'straight'];
                $edges[] = ['source' => 'p_grandmother', 'sh' => 'left', 'target' => 'm_p_grandparents', 'th' => 'right', 'type' => 'straight'];
                if ($father) {
                    $edges[] = ['source' => 'm_p_grandparents', 'sh' => 'bottom', 'target' => 'father', 'th' => 'top', 'type' => 'step'];
                }
                foreach ($pUnclesAunts as $ua) {
                    $edges[] = ['source' => 'm_p_grandparents', 'sh' => 'bottom', 'target' => $ua['id'], 'th' => 'top', 'type' => 'step'];
                }
            } else {
                $activeGFId = $pGrandfather ? 'p_grandfather' : 'p_grandmother';
                if ($father) {
                    $edges[] = ['source' => $activeGFId, 'sh' => 'bottom', 'target' => 'father', 'th' => 'top', 'type' => 'step'];
                }
                foreach ($pUnclesAunts as $ua) {
                    $edges[] = ['source' => $activeGFId, 'sh' => 'bottom', 'target' => $ua['id'], 'th' => 'top', 'type' => 'step'];
                }
            }
        }

        // 6. Maternal Grandparents (Generation 1)
        if ($mGrandfather || $mGrandmother) {
            $gfX = $mGrandfather && isset($mGrandfather['x']) ? $mGrandfather['x'] : ($motherX - 20);
            $gfY = $mGrandfather && isset($mGrandfather['y']) ? $mGrandfather['y'] : $Y_G1;
            $gmX = $mGrandmother && isset($mGrandmother['x']) ? $mGrandmother['x'] : ($motherX + 80);
            $gmY = $mGrandmother && isset($mGrandmother['y']) ? $mGrandmother['y'] : $Y_G1;

            if ($mGrandfather) {
                $nodes['m_grandfather'] = [
                    'id' => 'm_grandfather',
                    'x' => $gfX,
                    'y' => $gfY,
                    'gender' => $mGrandfather['gender'],
                    'relation' => 'm_grandfather',
                    'name' => $mGrandfather['name'] ?: $mGrandfather['relationLabel'],
                    'status' => $mGrandfather['status'],
                    'living' => $mGrandfather['living'],
                    'condition' => $mGrandfather['condition'] ?? ''
                ];
            }
            if ($mGrandmother) {
                $nodes['m_grandmother'] = [
                    'id' => 'm_grandmother',
                    'x' => $gmX,
                    'y' => $gmY,
                    'gender' => $mGrandmother['gender'],
                    'relation' => 'm_grandmother',
                    'name' => $mGrandmother['name'] ?: $mGrandmother['relationLabel'],
                    'status' => $mGrandmother['status'],
                    'living' => $mGrandmother['living'],
                    'condition' => $mGrandmother['condition'] ?? ''
                ];
            }

            if ($mGrandfather && $mGrandmother) {
                $mx = ($gfX + $gmX) / 2 + 20;
                $my = ($gfY + $gmY) / 2 + 20;
                $nodes['m_m_grandparents'] = [
                    'id' => 'm_m_grandparents',
                    'type' => 'marriage',
                    'x' => $mx,
                    'y' => $my
                ];
                $edges[] = ['source' => 'm_grandfather', 'sh' => 'right', 'target' => 'm_m_grandparents', 'th' => 'left', 'type' => 'straight'];
                $edges[] = ['source' => 'm_grandmother', 'sh' => 'left', 'target' => 'm_m_grandparents', 'th' => 'right', 'type' => 'straight'];
                if ($mother) {
                    $edges[] = ['source' => 'm_m_grandparents', 'sh' => 'bottom', 'target' => 'mother', 'th' => 'top', 'type' => 'step'];
                }
                foreach ($mUnclesAunts as $ua) {
                    $edges[] = ['source' => 'm_m_grandparents', 'sh' => 'bottom', 'target' => $ua['id'], 'th' => 'top', 'type' => 'step'];
                }
            } else {
                $activeGFId = $mGrandfather ? 'm_grandfather' : 'm_grandmother';
                if ($mother) {
                    $edges[] = ['source' => $activeGFId, 'sh' => 'bottom', 'target' => 'mother', 'th' => 'top', 'type' => 'step'];
                }
                foreach ($mUnclesAunts as $ua) {
                    $edges[] = ['source' => $activeGFId, 'sh' => 'bottom', 'target' => $ua['id'], 'th' => 'top', 'type' => 'step'];
                }
            }
        }

        // 7. Children (Generation 4)
        if (count($children) > 0) {
            $spouseNode = $spouse ?: [
                'id' => 'spouse',
                'relation' => 'spouse',
                'name' => 'Spouse',
                'gender' => $patient['gender'] === 'male' ? 'female' : 'male',
                'age' => '',
                'living' => true,
                'status' => 'Normal',
                'condition' => ''
            ];

            $spouseX = isset($spouseNode['x']) ? $spouseNode['x'] : ($patientX + 80);
            $spouseY = isset($spouseNode['y']) ? $spouseNode['y'] : $Y_G3;

            $nodes['spouse'] = [
                'id' => 'spouse',
                'x' => $spouseX,
                'y' => $spouseY,
                'gender' => $spouseNode['gender'],
                'relation' => 'spouse',
                'name' => $spouseNode['name'],
                'status' => $spouseNode['status'],
                'living' => $spouseNode['living'],
                'condition' => $spouseNode['condition'] ?? ''
            ];

            $nodes['m_patient'] = [
                'id' => 'm_patient',
                'type' => 'marriage',
                'x' => ($patientX + $spouseX) / 2 + 20,
                'y' => ($patientY + $spouseY) / 2 + 20
            ];

            $edges[] = ['source' => 'patient', 'sh' => 'right', 'target' => 'm_patient', 'th' => 'left', 'type' => 'straight'];
            $edges[] = ['source' => 'spouse', 'sh' => 'left', 'target' => 'm_patient', 'th' => 'right', 'type' => 'straight'];

            foreach ($children as $index => $child) {
                $defaultX = ($patientX + 40) + ($index - (count($children) - 1) / 2) * 120;
                $nodes[$child['id']] = [
                    'id' => $child['id'],
                    'x' => isset($child['x']) ? $child['x'] : $defaultX,
                    'y' => isset($child['y']) ? $child['y'] : $Y_G4,
                    'gender' => $child['gender'],
                    'relation' => $child['relation'],
                    'name' => $child['name'] ?: $child['relationLabel'],
                    'status' => $child['status'],
                    'living' => $child['living'],
                    'condition' => $child['condition'] ?? ''
                ];
                $edges[] = ['source' => 'm_patient', 'sh' => 'bottom', 'target' => $child['id'], 'th' => 'top', 'type' => 'step'];
            }
        }

        // Calculate bounding box bounds
        $minX = 99999;
        $maxX = -99999;
        $minY = 99999;
        $maxY = -99999;

        foreach ($nodes as $n) {
            $minX = min($minX, $n['x']);
            $maxX = max($maxX, $n['x']);
            $minY = min($minY, $n['y']);
            $maxY = max($maxY, $n['y']);
        }

        if ($minX == 99999) { $minX = -100; $maxX = 100; $minY = 0; $maxY = 400; }
        
        // Dynamic mathematical coordinate scaling to fit within 520x280 canvas
        // This bypasses any Dompdf SVG viewBox scaling bugs!
        $treeW = ($maxX - $minX) + 40; // width of nodes is 40
        $treeH = ($maxY - $minY) + 40; // height of nodes is 40
        
        $canvasW = 500;
        $canvasH = 240;
        
        $scaleX = $canvasW / $treeW;
        $scaleY = $canvasH / $treeH;
        $scale = min($scaleX, $scaleY);
        
        // Don't upscale small trees too much
        if ($scale > 1.1) {
            $scale = 1.1;
        }

        // Shift offsets to center the scaled tree inside the 520x280 canvas
        $offsetX = ($canvasW - $treeW * $scale) / 2 + 10;
        $offsetY = ($canvasH - $treeH * $scale) / 2 + 10;

        // Function helper to convert original coordinate to scaled coordinate
        $scaleXCoord = function($x) use ($minX, $scale, $offsetX) {
            return ($x - $minX) * $scale + $offsetX;
        };
        $scaleYCoord = function($y) use ($minY, $scale, $offsetY) {
            return ($y - $minY) * $scale + $offsetY;
        };

        // Render Connections with scaled coordinates
        $linesSvg = '';
        foreach ($edges as $e) {
            $sNode = $nodes[$e['source']] ?? null;
            $tNode = $nodes[$e['target']] ?? null;
            if (!$sNode || !$tNode) continue;

            $sX = $sNode['x'];
            $sY = $sNode['y'];
            $tX = $tNode['x'];
            $tY = $tNode['y'];

            // Adjust relative connection points
            if (isset($sNode['type']) && $sNode['type'] === 'marriage') {
                $sX += 0.75; $sY += 0.75;
            } else {
                if ($e['sh'] === 'right') { $sX += 40; $sY += 20; }
                elseif ($e['sh'] === 'left') { $sY += 20; }
                elseif ($e['sh'] === 'bottom') { $sX += 20; $sY += 40; }
            }

            if (isset($tNode['type']) && $tNode['type'] === 'marriage') {
                $tX += 0.75; $tY += 0.75;
            } else {
                if ($e['th'] === 'right') { $tX += 40; $tY += 20; }
                elseif ($e['th'] === 'left') { $tY += 20; }
                elseif ($e['th'] === 'top') { $tX += 20; }
            }

            // Scale to canvas
            $sX_s = $scaleXCoord($sX);
            $sY_s = $scaleYCoord($sY);
            $tX_s = $scaleXCoord($tX);
            $tY_s = $scaleYCoord($tY);

            if ($e['type'] === 'straight') {
                $linesSvg .= '<line x1="' . $sX_s . '" y1="' . $sY_s . '" x2="' . $tX_s . '" y2="' . $tY_s . '" />';
            } else {
                $midY = ($sY + $tY) / 2;
                $midY_s = $scaleYCoord($midY);
                $linesSvg .= '<path d="M ' . $sX_s . ' ' . $sY_s . ' V ' . $midY_s . ' H ' . $tX_s . ' V ' . $tY_s . '" />';
            }
        }

        // Render Nodes with scaled coordinates
        $shapesSvg = '';
        foreach ($nodes as $n) {
            $nX_s = $scaleXCoord($n['x']);
            $nY_s = $scaleYCoord($n['y']);
            
            // Scaled node dimensions
            $dim = 40 * $scale;
            $halfDim = 20 * $scale;
            $cx = $nX_s + $halfDim;
            $cy = $nY_s + $halfDim;

            if (isset($n['type']) && $n['type'] === 'marriage') {
                $shapesSvg .= '<circle cx="' . $scaleXCoord($n['x'] + 0.75) . '" cy="' . $scaleYCoord($n['y'] + 0.75) . '" r="' . (3 * $scale) . '" fill="#000000" />';
                continue;
            }

            $isMale = $n['gender'] === 'male';
            $isFemale = $n['gender'] === 'female';
            $isAffected = $n['status'] === 'Affected';
            $isCarrier = $n['status'] === 'Carrier';
            $isPatient = $n['id'] === 'patient';
            $isDeceased = !$n['living'];

            $strokeColor = $isPatient ? '#7c3aed' : '#1e293b';
            $strokeWidth = $isPatient ? (2.5 * $scale) : (1.5 * $scale);
            $fillColor = $isAffected ? ($isPatient ? '#7c3aed' : '#000000') : '#ffffff';

            if ($isMale) {
                $shapesSvg .= '<rect x="' . $nX_s . '" y="' . $nY_s . '" width="' . $dim . '" height="' . $dim . '" stroke="' . $strokeColor . '" stroke-width="' . $strokeWidth . '" fill="' . $fillColor . '" />';
            } elseif ($isFemale) {
                $shapesSvg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $halfDim . '" stroke="' . $strokeColor . '" stroke-width="' . $strokeWidth . '" fill="' . $fillColor . '" />';
            } else {
                $shapesSvg .= '<polygon points="' . $cx . ',' . $nY_s . ' ' . ($nX_s + $dim) . ',' . $cy . ' ' . $cx . ',' . ($nY_s + $dim) . ' ' . $nX_s . ',' . $cy . '" stroke="' . $strokeColor . '" stroke-width="' . $strokeWidth . '" fill="' . $fillColor . '" />';
            }

            if ($isCarrier) {
                $shapesSvg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . (4 * $scale) . '" fill="' . ($isPatient ? '#7c3aed' : '#000000') . '" />';
            }
            if ($isDeceased) {
                $shapesSvg .= '<line x1="' . $nX_s . '" y1="' . ($nY_s + $dim) . '" x2="' . ($nX_s + $dim) . '" y2="' . $nY_s . '" stroke="#ef4444" stroke-width="' . (2 * $scale) . '" />';
            }

            // Labels with scaled positioning and font size
            $fontSizeName = max(6, round(9.5 * $scale));
            $fontSizeCond = max(5, round(7.5 * $scale));
            
            $shapesSvg .= '<text x="' . $cx . '" y="' . ($nY_s + $dim + 10) . '" text-anchor="middle" font-size="' . $fontSizeName . '" font-weight="bold" fill="#334155">' . htmlspecialchars($n['name'], ENT_QUOTES, 'UTF-8') . '</text>';
            if (!empty($n['condition'])) {
                $shapesSvg .= '<text x="' . $cx . '" y="' . ($nY_s + $dim + 20) . '" text-anchor="middle" font-size="' . $fontSizeCond . '" font-weight="bold" fill="#ef4444">' . htmlspecialchars($n['condition'], ENT_QUOTES, 'UTF-8') . '</text>';
            }
        }

        // The canvas dimensions are always a fixed 520x280.
        // Dompdf will render this viewport cleanly at exactly 520x280px without any clipping.
        $svgStr = '<svg width="520" height="280" viewBox="0 0 520 280" xmlns="http://www.w3.org/2000/svg" style="display:block;margin:0 auto;">
            <!-- Background -->
            <rect x="0" y="0" width="520" height="280" fill="#f8fafc" rx="8" stroke="#e2e8f0" stroke-width="1"/>
            
            <!-- Connectors -->
            <g stroke="#64748b" stroke-width="1.8" fill="none">
                ' . $linesSvg . '
            </g>
            
            <!-- Shapes -->
            ' . $shapesSvg . '
        </svg>';

        return [
            'svg' => $svgStr,
            'width' => 520,
            'height' => 280
        ];
    }

    /* ── Section 7 ─────────────────────────────────────────── */
    private function section7(): string
    {
        $i = $this->intake;
        $selectiveEating = $this->arr($i->routine_selective_eating);
        $selectiveText = count($selectiveEating) > 0 ? $this->e(implode(', ', $selectiveEating)) : 'Normal eating behavior';

        $remark = $i->routine_remark ? '<div class="remarks-block"><strong>&#128204; Remarks:</strong> ' . $this->e($i->routine_remark) . '</div>' : '';

        $rows = [
            ['Dressing (clothing/buttons)',         $i->routine_dressing],
            ['Grooming (hair/face)',                $i->routine_grooming],
            ['Socks (wearing &amp; removing)',       $i->routine_socks],
            ['Bathing (washing/drying)',            $i->routine_bathing],
            ['Brushing Teeth',                     $i->routine_brushing],
            ['Eating (spoon/independence)',         $i->routine_eating],
            ['Selective Eating Habits',            null, $selectiveText],
            ['Drinking Water (glass/cup)',          $i->routine_drinking_water],
            ['Gross Motor: Ball Catching',         $i->routine_ball_catch],
            ['Gross Motor: Ball Throwing',         $i->routine_ball_throw],
            ['Gross Motor: Heel-Toe Walk',         $i->routine_heel_toe_walk],
        ];

        $html = $this->sectionTitle(7, 'Child Routine') . '
        <table class="grid-table">
            <thead><tr>
                <th style="width:45%;">Activity / Area</th>
                <th style="width:55%;">Performance Level</th>
            </tr></thead>
            <tbody>';
        foreach ($rows as $r) {
            $val = isset($r[2]) ? $r[2] : $this->badge($r[1] ?? null);
            $html .= '<tr><td>' . $r[0] . '</td><td>' . $val . '</td></tr>';
        }
        $html .= '</tbody></table>' . $remark;
        return $html;
    }

    /* ── Section 8 ─────────────────────────────────────────── */
    private function section8(): string
    {
        $i = $this->intake;
        $toiletSkills = [
            ['Indicates Toilet Need',            'toilet_indicates_need'],
            ['Goes to Toilet on Time',           'toilet_goes_on_time'],
            ['Removes Clothes Independently',    'toilet_removes_clothes'],
            ['Sits Properly on Toilet',          'toilet_sits_properly'],
            ['Cleans Self After Toileting',      'toilet_cleans_self'],
            ['Flushes Toilet',                   'toilet_flushes'],
            ['Washes Hands After Toilet',        'toilet_washes_hands'],
            ['Daytime Bladder Control',          'toilet_daytime_control'],
            ['Nighttime Bladder Control',        'toilet_nighttime_control'],
            ['Bowel Control',                    'toilet_bowel_control'],
        ];

        $rows = '';
        foreach ($toiletSkills as $ts) {
            $remarkVal = isset($i->{$ts[1] . '_remark'}) && $i->{$ts[1] . '_remark'} ? $this->e($i->{$ts[1] . '_remark'}) : '<span style="color:#94a3b8;">—</span>';
            $rows .= '<tr><td>' . $ts[0] . '</td><td>' . $this->badge($i->{$ts[1]} ?? null) . '</td><td>' . $remarkVal . '</td></tr>';
        }

        $remark = $i->toilet_remark ? '<div class="remarks-block"><strong>&#128204; Toileting Remarks:</strong> ' . $this->e($i->toilet_remark) . '</div>' : '';

        return $this->sectionTitle(8, 'Toileting Assessment') . '
        <table class="grid-table">
            <thead><tr>
                <th style="width:42%;">Toileting Skill (ADL)</th>
                <th style="width:28%;">Performance</th>
                <th style="width:30%;">Remarks</th>
            </tr></thead>
            <tbody>' . $rows . '</tbody>
        </table>
        <div class="sub-title">Additional Toileting Information</div>
        <table class="info-table">
            <tr>
                <td class="label">Toilet Trained</td>
                <td class="value">' . $this->e($i->toilet_trained) . ' (' . $this->e($i->toilet_indicates_before_after, 'Does not indicate') . ')</td>
                <td class="label">Uses Diaper/Pull-up</td>
                <td class="value">' . $this->badge($i->toilet_uses_diaper) . '</td>
            </tr>
            <tr>
                <td class="label">Constipation Issues</td>
                <td class="value">' . $this->badge($i->toilet_constipation) . '</td>
                <td class="label">Toilet Avoidance/Fear</td>
                <td class="value">' . $this->badge($i->toilet_avoidance_fear) . '</td>
            </tr>
            <tr>
                <td class="label">Accidents Frequency</td>
                <td class="value">' . $this->e($i->toilet_accidents_frequency) . '</td>
                <td class="label">Independence Score (1–10)</td>
                <td class="value" style="font-weight:bold;color:' . self::PRIMARY_LIGHT . ';">' . $this->e($i->toilet_independence_level) . '</td>
            </tr>
        </table>' . $remark;
    }

    /* ── Section 9 ─────────────────────────────────────────── */
    private function section9(): string
    {
        $i = $this->intake;
        $fineMotor = [
            ['Holds Pencil/Crayon Properly', 'fine_holds_pencil'],
            ['Scribbling',                   'fine_scribbling'],
            ['Coloring Within Boundary',     'fine_coloring'],
            ['Copying Shapes',               'fine_copying_shapes'],
            ['Writing Letters/Numbers',      'fine_writing_letters'],
            ['Cutting with Scissors',        'fine_cutting_scissors'],
            ['Pasting Activities',           'fine_pasting'],
            ['Buttoning/Unbuttoning',        'fine_buttoning'],
            ['Zipping/Unzipping',            'fine_zipping'],
            ['Bead Threading',               'fine_bead_threading'],
            ['Opening Containers',           'fine_opening_containers'],
            ['Using Spoon Properly',         'fine_using_spoon'],
            ['Bilateral Hand Use',           'fine_bilateral_hand'],
            ['Hand Strength',                'fine_hand_strength'],
            ['Hand-Eye Coordination',        'fine_hand_eye'],
        ];
        $rows = '';
        foreach ($fineMotor as $fm) {
            $remarkVal = isset($i->{$fm[1] . '_remark'}) && $i->{$fm[1] . '_remark'} ? $this->e($i->{$fm[1] . '_remark'}) : '<span style="color:#94a3b8;">—</span>';
            $rows .= '<tr><td>' . $fm[0] . '</td><td>' . $this->badge($i->{$fm[1]} ?? null) . '</td><td>' . $remarkVal . '</td></tr>';
        }
        $remark = $i->fine_remark ? '<div class="remarks-block"><strong>&#128204; Fine Motor Remarks:</strong> ' . $this->e($i->fine_remark) . '</div>' : '';

        return $this->sectionTitle(9, 'Fine Motor Skills') . '
        <table class="info-table">
            <tr>
                <td class="label">Hand Preference</td>
                <td class="value">' . $this->ucf($i->fine_hand_preference, 'Mixed') . '</td>
                <td class="label">Poor Grip / Grasp</td>
                <td class="value">' . $this->badge($i->fine_poor_grip, 'No') . ' / ' . $this->ucf($i->fine_grasp_pattern, 'Normal') . '</td>
            </tr>
            <tr>
                <td class="label">Writing Difficulty</td>
                <td class="value">' . $this->badge($i->fine_writing_difficulty, 'No') . '</td>
                <td class="label">Small Objects Difficulty</td>
                <td class="value">' . $this->badge($i->fine_small_objects_difficulty, 'No') . '</td>
            </tr>
        </table>
        <table class="grid-table">
            <thead><tr>
                <th style="width:40%;">Fine Motor Skill</th>
                <th style="width:28%;">Performance</th>
                <th style="width:32%;">Remarks</th>
            </tr></thead>
            <tbody>' . $rows . '</tbody>
        </table>' . $remark;
    }

    /* ── Section 10 ────────────────────────────────────────── */
    private function section10(): string
    {
        $i = $this->intake;
        $speechIssues = $this->arr($i->speech_issues);
        $issuesText   = count($speechIssues) > 0 ? implode(', ', array_map(fn($s) => '<span style="background:#fee2e2;color:#dc2626;padding:1px 4px;border-radius:2px;font-size:8px;">' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</span>', $speechIssues)) : '<span class="dot-green">&#10003; None reported</span>';
        $whQ          = $this->arr($i->speech_wh_questions);
        $whText       = count($whQ) > 0 ? $this->e(implode(', ', $whQ)) : 'None';
        $commStyle    = $this->arr($i->lang_communication_style);
        $commText     = count($commStyle) > 0 ? $this->e(implode(', ', $commStyle)) : 'N/A';

        $tongueMovements = [];
        if ($i->tongue_protrusion)    $tongueMovements[] = 'Protrusion';
        if ($i->tongue_retraction)    $tongueMovements[] = 'Retraction';
        if ($i->tongue_elevation)     $tongueMovements[] = 'Elevation';
        if ($i->tongue_lateralization) $tongueMovements[] = 'Lateralization';
        $tongueText = count($tongueMovements) > 0 ? $this->e(implode(', ', $tongueMovements)) : 'Normal Movement';

        $remark = $i->speech_remark ? '<div class="remarks-block"><strong>&#128204; Speech &amp; Oromotor Remarks:</strong> ' . $this->e($i->speech_remark) . '</div>' : '';

        return $this->sectionTitle(10, 'Speech, Language &amp; Oromotor') . '
        <table class="info-table">
            <tr>
                <td class="label">Communication Mode</td>
                <td class="value">' . $this->ucf($i->speech_communication) . '</td>
                <td class="label">Speech Clarity</td>
                <td class="value">' . $this->badge($i->speech_clarity) . '</td>
            </tr>
            <tr>
                <td class="label">Speech Issues</td>
                <td class="value">' . $issuesText . '</td>
                <td class="label">WH Questions Answered</td>
                <td class="value">' . $whText . '</td>
            </tr>
        </table>
        <div class="sub-title">Oromotor &amp; Lip/Palate Examination</div>
        <table class="grid-table">
            <thead><tr>
                <th style="width:25%;">Skill Area</th><th style="width:25%;">Status</th>
                <th style="width:25%;">Skill Area</th><th style="width:25%;">Status</th>
            </tr></thead>
            <tbody>
                <tr>
                    <td>Drooling</td><td>' . $this->badge($i->oromotor_drooling, 'No') . '</td>
                    <td>Bite Strength</td><td>' . $this->badge($i->oromotor_bite) . '</td>
                </tr>
                <tr>
                    <td>Chewing</td><td>' . $this->badge($i->oromotor_chewing) . '</td>
                    <td>Swallowing</td><td>' . $this->badge($i->oromotor_swallowing) . '</td>
                </tr>
                <tr>
                    <td>Straw Drinking</td><td>' . $this->badge($i->oromotor_straw_drinking) . '</td>
                    <td>Spoon Feeding</td><td>' . $this->badge($i->oromotor_spoon_feeding) . '</td>
                </tr>
                <tr>
                    <td>Blowing Skill</td><td>' . $this->badge($i->oromotor_blowing) . '</td>
                    <td>Tongue Tie</td><td>' . $this->badge($i->tongue_tie, 'Absent') . '</td>
                </tr>
                <tr>
                    <td>Lip Closure</td><td>' . $this->badge($i->lip_closure, 'Adequate') . '</td>
                    <td>Palate Exam</td><td>' . $this->badge($i->palate_exam, 'Normal') . '</td>
                </tr>
                <tr>
                    <td colspan="2" style="font-weight:bold;color:' . self::PRIMARY . ';">Tongue Movement</td>
                    <td colspan="2">' . $tongueText . '</td>
                </tr>
            </tbody>
        </table>
        <div class="sub-title">Home Language Environment</div>
        <table class="info-table">
            <tr>
                <td class="label">Home Languages</td>
                <td class="value">' . $this->e($i->lang_languages_spoken) . '</td>
                <td class="label">Preferred Language</td>
                <td class="value">' . $this->e($i->lang_preferred) . '</td>
            </tr>
            <tr>
                <td class="label">Communication Style</td>
                <td class="value" colspan="3">' . $commText . '</td>
            </tr>
        </table>' . $remark;
    }

    /* ── Section 11 ────────────────────────────────────────── */
    private function section11(): string
    {
        $i = $this->intake;
        $playBehavior = $this->arr($i->social_play_behavior);
        $playText     = count($playBehavior) > 0 ? $this->e(implode(', ', $playBehavior)) : 'N/A';

        $maladaptives = $this->arr($i->social_maladaptive_behavior);
        $obs = [
            ['Hyperactivity',           'Hyperactivity'],
            ['Inattention',             'Attention'],
            ['Impulsivity',             'Impulsivity'],
            ['Tantrums',                'Tantrums'],
            ['Aggression',              'Aggression'],
            ['Hitting Others',          'Hitting'],
            ['Biting Others',           'Biting'],
            ['Self Injurious Behavior', 'Self Injurious Behaviour'],
            ['Hand Flapping',           'Hand Flapping'],
            ['Repetitive Behaviors',    'Repetitive Behaviours'],
            ['Lining Up Objects',       'Lining Objects'],
        ];

        $obsRows = '';
        for ($idx = 0; $idx < count($obs); $idx += 2) {
            $c1  = in_array($obs[$idx][1], $maladaptives);
            $yes = '<span style="background:#fee2e2;color:#dc2626;font-weight:bold;padding:1px 6px;border-radius:3px;">&#9888; YES</span>';
            $no  = '<span style="background:#f1f5f9;color:#475569;font-weight:bold;padding:1px 6px;border-radius:3px;">&#10003; NO</span>';

            $right = isset($obs[$idx + 1])
                ? '<td style="font-weight:bold;color:' . self::PRIMARY . ';">' . $this->e($obs[$idx+1][0]) . '</td><td>' . (in_array($obs[$idx+1][1], $maladaptives) ? $yes : $no) . '</td>'
                : '<td></td><td></td>';

            $obsRows .= '<tr>
                <td style="font-weight:bold;color:' . self::PRIMARY . ';">' . $this->e($obs[$idx][0]) . '</td>
                <td>' . ($c1 ? $yes : $no) . '</td>
                ' . $right . '
            </tr>';
        }

        $remark = $i->social_remark ? '<div class="remarks-block"><strong>&#128204; Social Remarks:</strong> ' . $this->e($i->social_remark) . '</div>' : '';

        return $this->sectionTitle(11, 'Social &amp; Play History') . '
        <table class="info-table">
            <tr>
                <td class="label">Primary Caregiver</td>
                <td class="value">' . $this->e($i->social_caregiver) . '</td>
                <td class="label">Identifies Parents/Relatives</td>
                <td class="value">' . $this->badge($i->social_identifies_parents, 'Yes') . ' / ' . $this->badge($i->social_identifies_relatives, 'Yes') . '</td>
            </tr>
            <tr>
                <td class="label">Responds to Name</td>
                <td class="value">' . $this->badge($i->social_responds_name, 'Yes') . '</td>
                <td class="label">Eye Contact / Imitation</td>
                <td class="value">' . $this->badge($i->social_eye_contact, 'Yes') . ' / ' . $this->badge($i->social_imitation, 'Yes') . '</td>
            </tr>
            <tr>
                <td class="label">Turn Taking / Sharing</td>
                <td class="value">' . $this->badge($i->social_turn_taking, 'Yes') . ' / ' . $this->badge($i->social_sharing, 'Yes') . '</td>
                <td class="label">Rules of Games</td>
                <td class="value">' . $this->badge($i->social_rules_of_games, 'Yes') . '</td>
            </tr>
            <tr>
                <td class="label">Separation / Stranger Anxiety</td>
                <td class="value">' . $this->badge($i->social_separation_anxiety, 'No') . ' / ' . $this->badge($i->social_stranger_anxiety, 'No') . '</td>
                <td class="label">Follows Group Activities</td>
                <td class="value">' . $this->badge($i->social_follows_group, 'Yes') . '</td>
            </tr>
            <tr>
                <td class="label">Favorite Toys</td>
                <td class="value" colspan="3">' . $this->e($i->social_favorite_toys, 'None') . '</td>
            </tr>
            <tr>
                <td class="label">Play Behaviors</td>
                <td class="value" colspan="3">' . $playText . '</td>
            </tr>
            <tr>
                <td class="label">Screen Time / Day</td>
                <td class="value">' . $this->e($i->social_average_screen_time) . '</td>
                <td class="label">Sleep / Eating Habits</td>
                <td class="value">' . $this->e($i->social_sleep_pattern, 'Normal') . ' / ' . $this->e($i->social_eating_habits, 'Normal') . '</td>
            </tr>
            <tr>
                <td class="label">Emotional Regulation</td>
                <td class="value">' . $this->ucf($i->social_emotional_regulation, 'Appropriate') . '</td>
                <td class="label">Outdoor Play</td>
                <td class="value">' . $this->ucf($i->social_outdoor_play, 'Occasional') . '</td>
            </tr>
        </table>
        <div class="sub-title">Maladaptive Behavior Observations</div>
        <table class="grid-table">
            <thead><tr>
                <th style="width:30%;">Behavior</th><th style="width:20%;">Status</th>
                <th style="width:30%;">Behavior</th><th style="width:20%;">Status</th>
            </tr></thead>
            <tbody>' . $obsRows . '</tbody>
        </table>' . $remark;
    }

    /* ── Section 12 ────────────────────────────────────────── */
    private function section12(): string
    {
        $i = $this->intake;
        $recognition    = $this->arr($i->cognitive_recognition);
        $recText        = count($recognition) > 0 ? implode(' &nbsp; ', array_map(fn($r) => '<span style="background:#eff6ff;color:#1d4ed8;padding:1px 5px;border-radius:3px;font-size:8px;">' . htmlspecialchars($r, ENT_QUOTES, 'UTF-8') . '</span>', $recognition)) : 'None reported';
        $academicSkills = $this->arr($i->academic_skills);
        $acadText       = count($academicSkills) > 0 ? implode(' &nbsp; ', array_map(fn($a) => '<span style="background:#f0fdf4;color:#15803d;padding:1px 5px;border-radius:3px;font-size:8px;">' . htmlspecialchars($a, ENT_QUOTES, 'UTF-8') . '</span>', $academicSkills)) : 'N/A';

        $remark = $i->cognitive_remark ? '<div class="remarks-block"><strong>&#128204; Cognitive Remarks:</strong> ' . $this->e($i->cognitive_remark) . '</div>' : '';

        return $this->sectionTitle(12, 'Cognitive &amp; Academic') . '
        <table class="info-table">
            <tr>
                <td class="label">Recognition Skills</td>
                <td class="value" colspan="3">' . $recText . '</td>
            </tr>
            <tr>
                <td class="label">Academic Skills</td>
                <td class="value" colspan="3">' . $acadText . '</td>
            </tr>
            <tr>
                <td class="label">Attention / Memory</td>
                <td class="value">' . $this->badge($i->cognitive_attention) . ' / ' . $this->badge($i->cognitive_memory) . '</td>
                <td class="label">School Feedback</td>
                <td class="value">' . $this->e($i->cognitive_school_feedback, 'None') . '</td>
            </tr>
        </table>' . $remark;
    }

    /* ── Section 13 ────────────────────────────────────────── */
    private function section13(): string
    {
        $i = $this->intake;
        $suggested = $this->arr($i->plan_therapy_suggested);
        $therapyText = count($suggested) > 0
            ? implode(' &nbsp; ', array_map(fn($t) => '<span style="background:' . self::PRIMARY . ';color:#fff;font-weight:bold;padding:3px 8px;border-radius:4px;font-size:9px;">' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '</span>', $suggested))
            : '<em style="color:#94a3b8;">None</em>';

        $planRemark = $i->plan_remark ? '<tr><td class="label">Plan Remarks</td><td class="value" colspan="3">' . $this->e($i->plan_remark) . '</td></tr>' : '';

        return $this->sectionTitle(13, 'Clinical Therapy Plan') . '
        <table class="info-table">
            <tr>
                <td class="label">Therapies Suggested</td>
                <td class="value" colspan="3" style="padding:8px;">' . $therapyText . '</td>
            </tr>
            <tr>
                <td class="label">Home Program Given</td>
                <td class="value" colspan="3">' . $this->badge($i->plan_home_program_given, 'No') . '</td>
            </tr>
            <tr>
                <td class="label">Final Clinical Impression</td>
                <td class="value" colspan="3" style="font-weight:bold;font-size:10.5px;color:' . self::PRIMARY . ';padding:10px 8px;">' . $this->e($i->plan_final_impression, 'No final impression documented') . '</td>
            </tr>
            ' . $planRemark . '
        </table>

        <!-- Signature -->
        <table class="sig-table">
            <tr>
                <td style="width:45%;text-align:left;">
                    <div class="sig-line"></div>
                    <div class="sig-label">Clinical Assessor Signature &amp; Date</div>
                </td>
                <td style="width:10%;"></td>
                <td style="width:45%;text-align:right;">
                    <div class="sig-line"></div>
                    <div class="sig-label">Parent / Guardian Signature &amp; Date</div>
                </td>
            </tr>
        </table>

        <div class="pdf-footer">
            <strong>Helping Hands Child Development &amp; Education Centre</strong> &bull; Confidential Medical Record &bull; Generated on ' . date('d M Y, h:i A') . '
        </div>';
    }

    /* ── Build Full HTML ───────────────────────────────────── */
    public function buildHtml(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Intake Report &mdash; ' . $this->e($this->intake->child_name ?? 'Patient') . '</title>
    ' . $this->styles() . '
</head>
<body>
' . $this->header()    . '
' . $this->section1()  . '
' . $this->section2()  . '
' . $this->section3()  . '
' . $this->section4()  . '
<div class="page-break"></div>
' . $this->section5()  . '
<div class="page-break"></div>
' . $this->section6()  . '
<div class="page-break"></div>
' . $this->section7()  . '
' . $this->section8()  . '
<div class="page-break"></div>
' . $this->section9()  . '
<div class="page-break"></div>
' . $this->section10() . '
<div class="page-break"></div>
' . $this->section11() . '
<div class="page-break"></div>
' . $this->section12() . '
' . $this->section13() . '
</body>
</html>';
    }
}
