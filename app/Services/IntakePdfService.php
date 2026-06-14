<?php

namespace App\Services;

use App\Models\PatientIntake;

class IntakePdfService
{
    private PatientIntake $intake;

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

    public function __construct(PatientIntake $intake)
    {
        $this->intake = $intake;
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
                padding: 18px 22px 14px 22px;
                margin-bottom: 0;
            }
            .clinic-name {
                font-size: 17px;
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
                text-align: center;
                font-size: 12px;
                font-weight: bold;
                padding: 7px 0;
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

        return '
        <div class="pdf-header">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="vertical-align:middle;width:70%;">
                        <p class="clinic-name">&#9829; Helping Hands Child Development &amp; Education Centre</p>
                        <p class="clinic-tagline">Clinical Assessment Suite &bull; Patient Intake Record</p>
                    </td>
                    <td style="vertical-align:middle;text-align:right;width:30%;">
                        <div style="background:#1e4a7a;border-radius:4px;padding:8px 12px;display:inline-block;">
                            <div style="font-size:8px;color:#93c5fd;letter-spacing:0.5px;text-transform:uppercase;">Record ID</div>
                            <div style="font-size:14px;font-weight:bold;color:#fff;">#INK-' . str_pad($i->id ?? 0, 4, '0', STR_PAD_LEFT) . '</div>
                            <div style="font-size:7px;color:#93c5fd;">' . $date . '</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="doc-title-bar">
            Comprehensive Developmental Intake Assessment &nbsp;&nbsp;
            <span style="background:' . $statusBg . ';border-radius:3px;padding:2px 8px;font-size:9px;">' . $status . '</span>
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
                <td class="value">' . $this->e($i->natal_mother_name_age) . '</td>
                <td class="label">Father Name &amp; Age</td>
                <td class="value">' . $this->e($i->natal_father_name_age) . '</td>
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
                <td class="label">Immediate Birth Cry</td>
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
        <div class="pedigree-box">' . $this->buildPedigreeSvg() . '</div>
        ' . $pedigreeRemark;
    }

    private function buildPedigreeSvg(): string
    {
        $i = $this->intake;
        $pedigreeData = $this->arr($i->pedigree_chart_data);

        $nodes = [
            ['id'=>'I-1',   'label'=>'1','type'=>'male',  'cx'=>160,'cy'=>50],
            ['id'=>'I-2',   'label'=>'2','type'=>'female','cx'=>240,'cy'=>50],
            ['id'=>'I-3',   'label'=>'3','type'=>'male',  'cx'=>480,'cy'=>50],
            ['id'=>'I-4',   'label'=>'4','type'=>'female','cx'=>560,'cy'=>50],
            ['id'=>'II-1',  'label'=>'1','type'=>'male',  'cx'=>70, 'cy'=>140],
            ['id'=>'II-2',  'label'=>'2','type'=>'female','cx'=>140,'cy'=>140],
            ['id'=>'II-3',  'label'=>'3','type'=>'male',  'cx'=>210,'cy'=>140],
            ['id'=>'II-4',  'label'=>'4','type'=>'female','cx'=>280,'cy'=>140],
            ['id'=>'II-5',  'label'=>'5','type'=>'female','cx'=>440,'cy'=>140],
            ['id'=>'II-6',  'label'=>'6','type'=>'male',  'cx'=>510,'cy'=>140],
            ['id'=>'II-7',  'label'=>'7','type'=>'female','cx'=>580,'cy'=>140],
            ['id'=>'III-1', 'label'=>'1','type'=>'female','cx'=>75, 'cy'=>240],
            ['id'=>'III-2', 'label'=>'2','type'=>'male',  'cx'=>155,'cy'=>240],
            ['id'=>'III-3', 'label'=>'3','type'=>'female','cx'=>235,'cy'=>240],
            ['id'=>'III-4', 'label'=>'4','type'=>'male',  'cx'=>315,'cy'=>240],
            ['id'=>'III-5', 'label'=>'5','type'=>'female','cx'=>395,'cy'=>240],
            ['id'=>'III-6', 'label'=>'6','type'=>'female','cx'=>475,'cy'=>240],
            ['id'=>'III-7', 'label'=>'7','type'=>'male',  'cx'=>555,'cy'=>240],
            ['id'=>'III-8', 'label'=>'8','type'=>'unborn','cx'=>635,'cy'=>240],
            ['id'=>'IV-1',  'label'=>'1','type'=>'female','cx'=>235,'cy'=>340],
            ['id'=>'IV-2',  'label'=>'2','type'=>'male',  'cx'=>315,'cy'=>340],
            ['id'=>'IV-3',  'label'=>'3','type'=>'female','cx'=>395,'cy'=>340],
        ];

        $shapes = '';
        foreach ($nodes as $node) {
            $state      = $pedigreeData[$node['id']] ?? ['status'=>'normal','condition'=>''];
            $status     = $state['status'] ?? 'normal';
            $condition  = $state['condition'] ?? '';
            $isAffected = $status === 'affected';
            $isCarrier  = $status === 'carrier';
            $isUnborn   = $node['type'] === 'unborn' || $status === 'unborn';
            $cx = $node['cx']; $cy = $node['cy'];
            $fill   = $isAffected ? self::PRIMARY : '#ffffff';
            $stroke = self::PRIMARY_LIGHT;
            $tColor = $isAffected ? '#ffffff' : self::PRIMARY;

            if ($isUnborn) {
                $shapes .= '<polygon points="' . $cx . ',' . ($cy-18) . ' ' . ($cx+18) . ',' . $cy . ' ' . $cx . ',' . ($cy+18) . ' ' . ($cx-18) . ',' . $cy . '" fill="' . $fill . '" stroke="' . $stroke . '" stroke-width="2"/>';
            } elseif ($node['type'] === 'male') {
                $shapes .= '<rect x="' . ($cx-18) . '" y="' . ($cy-18) . '" width="36" height="36" rx="3" fill="' . $fill . '" stroke="' . $stroke . '" stroke-width="2"/>';
            } else {
                $shapes .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="18" fill="' . $fill . '" stroke="' . $stroke . '" stroke-width="2"/>';
            }
            if ($isCarrier) {
                $shapes .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="5" fill="' . self::PRIMARY . '"/>';
            }
            $shapes .= '<text x="' . $cx . '" y="' . ($cy+5) . '" text-anchor="middle" font-size="10" font-weight="bold" fill="' . $tColor . '">' . htmlspecialchars($node['label'], ENT_QUOTES, 'UTF-8') . '</text>';
            $shapes .= '<text x="' . $cx . '" y="' . ($cy+30) . '" text-anchor="middle" font-size="7.5" fill="' . self::TEXT_LIGHT . '">' . htmlspecialchars($node['id'], ENT_QUOTES, 'UTF-8') . '</text>';
            if (!empty($condition)) {
                $shapes .= '<text x="' . $cx . '" y="' . ($cy+40) . '" text-anchor="middle" font-size="6.5" fill="' . self::DANGER . '" font-weight="bold">' . htmlspecialchars(substr($condition, 0, 10), ENT_QUOTES, 'UTF-8') . '</text>';
            }
        }

        return '<svg width="520" height="290" viewBox="0 0 720 420" xmlns="http://www.w3.org/2000/svg" style="display:block;margin:0 auto;">
            <!-- Background -->
            <rect x="0" y="0" width="720" height="420" fill="#f8faff" rx="6"/>
            <!-- Gen Labels -->
            <text x="28" y="55"  text-anchor="middle" font-weight="bold" font-size="13" fill="' . self::PRIMARY . '">I</text>
            <text x="28" y="145" text-anchor="middle" font-weight="bold" font-size="13" fill="' . self::PRIMARY . '">II</text>
            <text x="28" y="245" text-anchor="middle" font-weight="bold" font-size="13" fill="' . self::PRIMARY . '">III</text>
            <text x="28" y="345" text-anchor="middle" font-weight="bold" font-size="13" fill="' . self::PRIMARY . '">IV</text>
            <!-- Gen separator lines -->
            <line x1="48" y1="80"  x2="700" y2="80"  stroke="#dbeafe" stroke-width="1" stroke-dasharray="4,4"/>
            <line x1="48" y1="175" x2="700" y2="175" stroke="#dbeafe" stroke-width="1" stroke-dasharray="4,4"/>
            <line x1="48" y1="275" x2="700" y2="275" stroke="#dbeafe" stroke-width="1" stroke-dasharray="4,4"/>
            <!-- Connectors -->
            <g stroke="' . self::PRIMARY_LIGHT . '" stroke-width="1.8" fill="none">
                <line x1="160" y1="50"  x2="240" y2="50"/>
                <line x1="200" y1="50"  x2="200" y2="95"/>
                <line x1="70"  y1="95"  x2="280" y2="95"/>
                <line x1="70"  y1="95"  x2="70"  y2="122"/>
                <line x1="140" y1="95"  x2="140" y2="122"/>
                <line x1="210" y1="95"  x2="210" y2="122"/>
                <line x1="280" y1="95"  x2="280" y2="122"/>
                <line x1="480" y1="50"  x2="560" y2="50"/>
                <line x1="520" y1="50"  x2="520" y2="95"/>
                <line x1="440" y1="95"  x2="580" y2="95"/>
                <line x1="440" y1="95"  x2="440" y2="122"/>
                <line x1="510" y1="95"  x2="510" y2="122"/>
                <line x1="580" y1="95"  x2="580" y2="122"/>
                <line x1="280" y1="140" x2="440" y2="140"/>
                <line x1="360" y1="140" x2="360" y2="195"/>
                <line x1="75"  y1="195" x2="635" y2="195"/>
                <line x1="75"  y1="195" x2="75"  y2="222"/>
                <line x1="155" y1="195" x2="155" y2="222"/>
                <line x1="235" y1="195" x2="235" y2="222"/>
                <line x1="315" y1="195" x2="315" y2="222"/>
                <line x1="395" y1="195" x2="395" y2="222"/>
                <line x1="475" y1="195" x2="475" y2="222"/>
                <line x1="555" y1="195" x2="555" y2="222"/>
                <line x1="635" y1="195" x2="635" y2="222"/>
                <line x1="315" y1="240" x2="315" y2="295"/>
                <line x1="235" y1="295" x2="395" y2="295"/>
                <line x1="235" y1="295" x2="235" y2="322"/>
                <line x1="315" y1="295" x2="315" y2="322"/>
                <line x1="395" y1="295" x2="395" y2="322"/>
            </g>
            ' . $shapes . '
            <!-- Legend -->
            <rect x="50" y="380" width="12" height="12" fill="' . self::PRIMARY . '" stroke="' . self::PRIMARY_LIGHT . '" stroke-width="1.5"/>
            <text x="66" y="391" font-size="8" fill="' . self::TEXT_MID . '">Affected</text>
            <circle cx="130" cy="386" r="6" fill="#fff" stroke="' . self::PRIMARY_LIGHT . '" stroke-width="1.5"/>
            <text x="140" y="391" font-size="8" fill="' . self::TEXT_MID . '">Unaffected</text>
            <rect x="210" y="380" width="12" height="12" fill="#fff" stroke="' . self::PRIMARY_LIGHT . '" stroke-width="1.5" rx="1"/>
            <text x="226" y="391" font-size="8" fill="' . self::TEXT_MID . '">Male</text>
            <circle cx="270" cy="386" r="6" fill="#fff" stroke="' . self::PRIMARY_LIGHT . '" stroke-width="1.5"/>
            <text x="280" y="391" font-size="8" fill="' . self::TEXT_MID . '">Female</text>
        </svg>';
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
