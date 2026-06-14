<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Intake Report - {{ $intake->child_name ?? 'Patient' }}</title>
    <style>
        @@page {
            margin: 40px 40px 50px 40px;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            color: #2d3748;
            font-size: 10px;
            line-height: 1.5;
        }
        .header-container {
            border-bottom: 2px solid #2b6cb0;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .clinic-title {
            font-size: 16px;
            font-weight: bold;
            color: #1a365d;
            text-align: center;
            margin: 0;
        }
        .clinic-subtitle {
            font-size: 9px;
            color: #718096;
            text-align: center;
            margin: 2px 0 0 0;
        }
        .document-title {
            font-size: 12px;
            font-weight: bold;
            color: #2b6cb0;
            text-align: center;
            margin: 10px 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            background-color: #ebf8ff;
            color: #2b6cb0;
            padding: 5px 8px;
            margin-top: 12px;
            margin-bottom: 8px;
            border-left: 3px solid #3182ce;
            page-break-inside: avoid;
            page-break-after: avoid;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        table.info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        table.info-table td.label {
            font-weight: bold;
            color: #4a5568;
            width: 22%;
        }
        table.info-table td.value {
            color: #1a202c;
            border-bottom: 1px dotted #cbd5e0;
        }
        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        table.grid-table th {
            background-color: #f7fafc;
            font-weight: bold;
            color: #4a5568;
            border: 1px solid #cbd5e0;
            padding: 4px 6px;
            font-size: 9px;
            text-align: left;
        }
        table.grid-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 9px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
        .checkmark {
            color: #38a169;
            font-weight: bold;
        }
        .unborn-shape {
            font-size: 11px;
        }
        .pedigree-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: #f8fafc;
            padding: 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
            text-align: center;
        }
        .remarks-block {
            margin-top: 5px;
            padding: 6px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-style: italic;
        }
        .footer-text {
            font-size: 8px;
            color: #a0aec0;
            text-align: center;
            margin-top: 25px;
        }
    </style>
</head>
<body>

    <!-- Header Banner -->
    <div class="header-container">
        <h1 class="clinic-title">Helping Hands Child Development And Education Centre</h1>
        <p class="clinic-subtitle">Clinical Assessment Suite | Patient Intake Record</p>
    </div>

    <div class="document-title">Comprehensive Developmental Intake Assessment</div>

    <!-- Section 1: Demographics -->
    <div class="section-title">1. Demographic Details</div>
    <table class="info-table">
        <tr>
            <td class="label">Child Name:</td>
            <td class="value" colspan="3">{{ $intake->child_name }}</td>
        </tr>
        <tr>
            <td class="label">DOB:</td>
            <td class="value">{{ $intake->dob ? $intake->dob->format('Y-m-d') : 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Age / Gender:</td>
            <td class="value">{{ $intake->age ? $intake->age . ' Years' : 'N/A' }} / {{ ucfirst($intake->gender ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="label">Father's Name:</td>
            <td class="value">{{ $intake->father_name }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Occupation / Phone:</td>
            <td class="value">{{ $intake->father_occupation ?? 'N/A' }} / {{ $intake->father_phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Mother's Name:</td>
            <td class="value">{{ $intake->mother_name }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Occupation / Phone:</td>
            <td class="value">{{ $intake->mother_occupation ?? 'N/A' }} / {{ $intake->mother_phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Email Address:</td>
            <td class="value">{{ $intake->email ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">School & Grade:</td>
            <td class="value">{{ $intake->school_grade ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Informant:</td>
            <td class="value">{{ $intake->informant ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Assessment Date:</td>
            <td class="value">{{ $intake->date_of_assessment ? $intake->date_of_assessment->format('Y-m-d') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td class="value" colspan="3">{{ $intake->address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Referral By:</td>
            <td class="value" colspan="3">{{ $intake->referral_by ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Previous Therapy:</td>
            <td class="value" colspan="3">
                @if(is_array($intake->previous_therapy) && count($intake->previous_therapy) > 0)
                    {{ implode(', ', $intake->previous_therapy) }}
                @else
                    None Documented
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Chief Complaint:</td>
            <td class="value" colspan="3">{{ $intake->chief_complaint ?? 'None' }}</td>
        </tr>
    </table>

    <!-- Section 2: Personal History -->
    <div class="section-title">2. Personal History</div>
    <h3 style="margin: 4px 0; color:#2b6cb0; font-size: 9px; text-transform: uppercase;">Mother's Natal History</h3>
    <table class="info-table">
        <tr>
            <td class="label">Maternal Age at Delivery:</td>
            <td class="value">{{ $intake->natal_mother_age ? $intake->natal_mother_age . ' Years' : 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Gestation Term:</td>
            <td class="value">{{ ucfirst($intake->natal_gestation ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="label">Mother Name & Age:</td>
            <td class="value">{{ $intake->natal_mother_name_age ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Father Name & Age:</td>
            <td class="value">{{ $intake->natal_father_name_age ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Delivery Place / Type:</td>
            <td class="value">{{ $intake->natal_place_delivery ?? 'N/A' }} / {{ ucfirst($intake->natal_delivery_type ?? 'N/A') }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Vaccination History:</td>
            <td class="value">{{ $intake->natal_vaccination_history ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Pregnancy History:</td>
            <td class="value" colspan="3">
                @if(is_array($intake->natal_pregnancy_history) && count($intake->natal_pregnancy_history) > 0)
                    {{ implode(', ', $intake->natal_pregnancy_history) }}
                @else
                    Normal Pregnancy
                @endif
            </td>
        </tr>
    </table>

    <h3 style="margin: 8px 0 4px 0; color:#2b6cb0; font-size: 9px; text-transform: uppercase;">Mother's Perinatal & Postnatal History</h3>
    <table class="info-table">
        <tr>
            <td class="label">Perinatal Condition:</td>
            <td class="value">{{ $intake->perinatal_medical_condition ?? 'Normal' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Medication:</td>
            <td class="value">{{ $intake->perinatal_medication ?? 'None' }}</td>
        </tr>
        <tr>
            <td class="label">Maternal Tensions:</td>
            <td class="value" colspan="3">
                @php
                    $conditions = [];
                    if($intake->perinatal_anxiety) $conditions[] = 'Anxiety';
                    if($intake->perinatal_depression) $conditions[] = 'Depression';
                    if($intake->perinatal_social_withdrawal) $conditions[] = 'Social Withdrawal';
                    if($intake->perinatal_eating_difficulties) $conditions[] = 'Eating Difficulties';
                    if($intake->perinatal_sleeping) $conditions[] = 'Sleeping Issues';
                @endphp
                @if(count($conditions) > 0)
                    {{ implode(', ', $conditions) }}
                @else
                    No maternal tensions reported
                @endif
            </td>
        </tr>
        @if($intake->perinatal_other)
        <tr>
            <td class="label">Other Mental Notes:</td>
            <td class="value" colspan="3">{{ $intake->perinatal_other }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Postnatal Complication:</td>
            <td class="value">{{ $intake->postnatal_complication ?? 'No complications' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Concerns:</td>
            <td class="value">{{ $intake->postnatal_concerns ?? 'Good' }}</td>
        </tr>
    </table>

    <h3 style="margin: 8px 0 4px 0; color:#2b6cb0; font-size: 9px; text-transform: uppercase;">Child Birth History</h3>
    <table class="info-table">
        <tr>
            <td class="label">Birth Weight:</td>
            <td class="value">{{ $intake->child_birth_weight ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">NICU Admission:</td>
            <td class="value">{{ $intake->child_nicu_admission ?? 'No' }}</td>
        </tr>
        <tr>
            <td class="label">Immediate Birth Cry:</td>
            <td class="value">{{ $intake->child_birth_cry ?? 'Present' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Neonatal Jaundice:</td>
            <td class="value">{{ $intake->child_jaundice ?? 'Absent' }}</td>
        </tr>
        <tr>
            <td class="label">Child Convulsions:</td>
            <td class="value">{{ $intake->child_convulsions ?? 'Absent' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Birth Asphyxia:</td>
            <td class="value">{{ $intake->child_birth_asphyxia ?? 'Absent' }}</td>
        </tr>
        <tr>
            <td class="label">Congenital Anomaly:</td>
            <td class="value" colspan="3">{{ $intake->child_congenital_anomaly ?? 'None' }}</td>
        </tr>
        @if($intake->child_remark)
        <tr>
            <td class="label">Child Remarks:</td>
            <td class="value" colspan="3">{{ $intake->child_remark }}</td>
        </tr>
        @endif
    </table>

    <div class="page-break"></div>

    <!-- Section 3: Medical & Surgical History -->
    <div class="section-title">3. Medical & Surgical History</div>
    <table class="info-table">
        <tr>
            <td class="label">Prev. Hospitalization:</td>
            <td class="value">{{ $intake->med_prev_hospitalization ?? 'No' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Prev. Infection:</td>
            <td class="value">{{ $intake->med_prev_infection ?? 'No' }}</td>
        </tr>
        <tr>
            <td class="label">Seizure History:</td>
            <td class="value">{{ $intake->med_seizure_history ?? 'No' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Medication History:</td>
            <td class="value">{{ $intake->med_medication_history ?? 'No' }}</td>
        </tr>
        <tr>
            <td class="label">Surgical History:</td>
            <td class="value">{{ $intake->med_surgical_history ?? 'No' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Blood Transfusion:</td>
            <td class="value">{{ $intake->med_blood_transfusion ?? 'No' }}</td>
        </tr>
        @if($intake->med_remark)
        <tr>
            <td class="label">Medical Remarks:</td>
            <td class="value" colspan="3">{{ $intake->med_remark }}</td>
        </tr>
        @endif
    </table>

    <!-- Section 4: Current Medical History -->
    <div class="section-title">4. Current Medical History</div>
    <table class="info-table">
        <tr>
            <td class="label">Current Conditions:</td>
            <td class="value">{{ $intake->current_medical_condition ?? 'None' }}</td>
        </tr>
        <tr>
            <td class="label">Current Medication:</td>
            <td class="value">{{ $intake->current_medication ?? 'None' }}</td>
        </tr>
        <tr>
            <td class="label">Allergies (General):</td>
            <td class="value">{{ $intake->current_allergy_history ?? 'None' }}</td>
        </tr>
        <tr>
            <td class="label">Medication Allergy:</td>
            <td class="value">{{ $intake->current_medication_allergy ?? 'None' }}</td>
        </tr>
    </table>

    <!-- Section 5: Developmental Milestones -->
    <div class="section-title">5. Developmental Milestones</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 40%;">Developmental Milestone</th>
                <th style="width: 30%;">Expected Normal Range</th>
                <th style="width: 30%;">Status Recorded</th>
            </tr>
        </thead>
        <tbody>
            @php
                $milestones = [
                    ['Smile', 'milestone_social_smile', '1-3 months'],
                    ['Neck Control', 'milestone_neck_holding', '2-4 months'],
                    ['Roll Over', 'milestone_roll_over', '4-6 months'],
                    ['Cooing', 'milestone_cooing', '4-6 months'],
                    ['Sitting independently', 'milestone_sitting_independently', '6-8 months'],
                    ['Babbling', 'milestone_babbling', '6-9 months'],
                    ['Crawling', 'milestone_crawling', '8-10 months'],
                    ['Standing independently', 'milestone_standing_independently', '10-12 months'],
                    ['Walking independently', 'milestone_walking_independently', '11-15 months'],
                    ['Meaningful single words', 'milestone_use_of_meaningful_word', '12-18 months'],
                    ['Phrases', 'milestone_phrases', '15-20 months'],
                    ['Simple sentence', 'milestone_simple_sentence', '23-30 months'],
                    ['Complex sentence', 'milestone_complex_sentence', '24-36 months'],
                    ['Toilet control', 'milestone_toilet_control', '36-48 months'],
                ];
            @endphp
            @foreach($milestones as $m)
                <tr>
                    <td>{{ $m[0] }}</td>
                    <td>{{ $m[2] }}</td>
                    <td style="font-weight: bold; color: {{ strtolower($intake->{$m[1]} ?? '') === 'delay' ? '#e53e3e' : '#2d3748' }}">
                        {{ ucfirst($intake->{$m[1]} ?? 'Not Documented') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($intake->milestone_remark)
        <div class="remarks-block"><strong>Milestone Remarks:</strong> {{ $intake->milestone_remark }}</div>
    @endif

    <div class="page-break"></div>

    <!-- Section 6: Family & Pedigree History -->
    <div class="section-title">6. Family & Pedigree History</div>
    <table class="info-table">
        <tr>
            <td class="label">Family Structure:</td>
            <td class="value">{{ ucfirst($intake->family_structure ?? 'N/A') }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Consanguinity:</td>
            <td class="value">{{ $intake->family_consanguinity ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Sibling Info:</td>
            <td class="value">{{ $intake->sibling_info ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Sibling Age(s):</td>
            <td class="value">{{ $intake->sibling_age ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Hereditary Conditions:</td>
            <td class="value" colspan="3">
                @if(is_array($intake->family_history) && count($intake->family_history) > 0)
                    {{ implode(', ', $intake->family_history) }}
                @else
                    None reported
                @endif
            </td>
        </tr>
        @if($intake->family_remark)
        <tr>
            <td class="label">Family Summary:</td>
            <td class="value" colspan="3">{{ $intake->family_remark }}</td>
        </tr>
        @endif
    </table>

    <h3 style="margin: 8px 0 4px 0; color:#2b6cb0; font-size: 9px; text-transform: uppercase;">Pedigree Tree Chart</h3>
    <div class="pedigree-box">
        @php
            $pedigreeData = is_array($intake->pedigree_chart_data) ? $intake->pedigree_chart_data : [];
            
            $nodes = [
                ['id' => 'I-1', 'label' => '1', 'type' => 'male', 'cx' => 160, 'cy' => 50],
                ['id' => 'I-2', 'label' => '2', 'type' => 'female', 'cx' => 240, 'cy' => 50],
                ['id' => 'I-3', 'label' => '3', 'type' => 'male', 'cx' => 480, 'cy' => 50],
                ['id' => 'I-4', 'label' => '4', 'type' => 'female', 'cx' => 560, 'cy' => 50],

                ['id' => 'II-1', 'label' => '1', 'type' => 'male', 'cx' => 70, 'cy' => 140],
                ['id' => 'II-2', 'label' => '2', 'type' => 'female', 'cx' => 140, 'cy' => 140],
                ['id' => 'II-3', 'label' => '3', 'type' => 'male', 'cx' => 210, 'cy' => 140],
                ['id' => 'II-4', 'label' => '4', 'type' => 'female', 'cx' => 280, 'cy' => 140],
                ['id' => 'II-5', 'label' => '5', 'type' => 'female', 'cx' => 440, 'cy' => 140],
                ['id' => 'II-6', 'label' => '6', 'type' => 'male', 'cx' => 510, 'cy' => 140],
                ['id' => 'II-7', 'label' => '7', 'type' => 'female', 'cx' => 580, 'cy' => 140],

                ['id' => 'III-1', 'label' => '1', 'type' => 'female', 'cx' => 75, 'cy' => 240],
                ['id' => 'III-2', 'label' => '2', 'type' => 'male', 'cx' => 155, 'cy' => 240],
                ['id' => 'III-3', 'label' => '3', 'type' => 'female', 'cx' => 235, 'cy' => 240],
                ['id' => 'III-4', 'label' => '4', 'type' => 'male', 'cx' => 315, 'cy' => 240],
                ['id' => 'III-5', 'label' => '5', 'type' => 'female', 'cx' => 395, 'cy' => 240],
                ['id' => 'III-6', 'label' => '6', 'type' => 'female', 'cx' => 475, 'cy' => 240],
                ['id' => 'III-7', 'label' => '7', 'type' => 'male', 'cx' => 555, 'cy' => 240],
                ['id' => 'III-8', 'label' => '8', 'type' => 'unborn', 'cx' => 635, 'cy' => 240],

                ['id' => 'IV-1', 'label' => '1', 'type' => 'female', 'cx' => 235, 'cy' => 340],
                ['id' => 'IV-2', 'label' => '2', 'type' => 'male', 'cx' => 315, 'cy' => 340],
                ['id' => 'IV-3', 'label' => '3', 'type' => 'female', 'cx' => 395, 'cy' => 340],
            ];
        @endphp

        <!-- Printable Pedigree Tree SVG -->
        <svg width="500" height="280" viewBox="0 0 720 410" xmlns="http://www.w3.org/2000/svg" style="display: block; margin: 0 auto;">
            <!-- Gen Labels -->
            <text x="25" y="55" text-anchor="middle" font-weight="bold" font-size="12" fill="#718096">I</text>
            <text x="25" y="145" text-anchor="middle" font-weight="bold" font-size="12" fill="#718096">II</text>
            <text x="25" y="245" text-anchor="middle" font-weight="bold" font-size="12" fill="#718096">III</text>
            <text x="25" y="345" text-anchor="middle" font-weight="bold" font-size="12" fill="#718096">IV</text>

            <!-- Relationship connectors -->
            <g stroke="#718096" stroke-width="2" fill="none">
                <!-- Couple I-1 & I-2 -->
                <line x1="160" y1="50" x2="240" y2="50" />
                <line x1="200" y1="50" x2="200" y2="95" />
                <line x1="70" y1="95" x2="280" y2="95" />
                <line x1="70" y1="95" x2="70" y2="120" />
                <line x1="140" y1="95" x2="140" y2="120" />
                <line x1="210" y1="95" x2="210" y2="120" />
                <line x1="280" y1="95" x2="280" y2="120" />

                <!-- Couple I-3 & I-4 -->
                <line x1="480" y1="50" x2="560" y2="50" />
                <line x1="520" y1="50" x2="520" y2="95" />
                <line x1="440" y1="95" x2="580" y2="95" />
                <line x1="440" y1="95" x2="440" y2="120" />
                <line x1="510" y1="95" x2="510" y2="120" />
                <line x1="580" y1="95" x2="580" y2="120" />

                <!-- Gen II descent (II-4 & II-5 couple) -->
                <line x1="280" y1="140" x2="440" y2="140" />
                <line x1="360" y1="140" x2="360" y2="195" />
                <line x1="75" y1="195" x2="635" y2="195" />
                <line x1="75" y1="195" x2="75" y2="220" />
                <line x1="155" y1="195" x2="155" y2="220" />
                <line x1="235" y1="195" x2="235" y2="220" />
                <line x1="315" y1="195" x2="315" y2="220" />
                <line x1="395" y1="195" x2="395" y2="220" />
                <line x1="475" y1="195" x2="475" y2="220" />
                <line x1="555" y1="195" x2="555" y2="220" />
                <line x1="635" y1="195" x2="635" y2="220" />

                <!-- Gen III to IV (III-4 children) -->
                <line x1="315" y1="240" x2="315" y2="295" />
                <line x1="235" y1="295" x2="395" y2="295" />
                <line x1="235" y1="295" x2="235" y2="320" />
                <line x1="315" y1="295" x2="315" y2="320" />
                <line x1="395" y1="295" x2="395" y2="320" />
            </g>

            <!-- Draw Nodes -->
            @foreach($nodes as $node)
                @php
                    $nodeState = $pedigreeData[$node['id']] ?? ['status' => 'normal', 'condition' => ''];
                    $isAffected = ($nodeState['status'] ?? '') === 'affected';
                    $isCarrier = ($nodeState['status'] ?? '') === 'carrier';
                    $isUnborn = $node['type'] === 'unborn' || ($nodeState['status'] ?? '') === 'unborn';

                    $fillColor = $isAffected ? '#2d3748' : '#ffffff';
                    $strokeColor = '#2d3748';
                    $textColor = $isAffected ? '#ffffff' : '#2d3748';
                @endphp

                <!-- Node shape -->
                @if($isUnborn)
                    <polygon points="{{ $node['cx'] }},{{ $node['cy'] - 17 }} {{ $node['cx'] + 17 }},{{ $node['cy'] }} {{ $node['cx'] }},{{ $node['cy'] + 17 }} {{ $node['cx'] - 17 }},{{ $node['cy'] }}" fill="{{ $fillColor }}" stroke="{{ $strokeColor }}" stroke-width="2" />
                @elseif($node['type'] === 'male')
                    <rect x="{{ $node['cx'] - 17 }}" y="{{ $node['cy'] - 17 }}" width="34" height="34" rx="2" fill="{{ $fillColor }}" stroke="{{ $strokeColor }}" stroke-width="2" />
                @else
                    <circle cx="{{ $node['cx'] }}" cy="{{ $node['cy'] }}" r="17" fill="{{ $fillColor }}" stroke="{{ $strokeColor }}" stroke-width="2" />
                @endif

                <!-- Carrier dot -->
                @if($isCarrier)
                    <circle cx="{{ $node['cx'] }}" cy="{{ $node['cy'] }}" r="4" fill="#2d3748" />
                @endif

                <!-- Node index labels -->
                <text x="{{ $node['cx'] }}" y="{{ $node['cy'] + 4 }}" text-anchor="middle" font-size="10" font-weight="bold" fill="{{ $textColor }}">{{ $node['label'] }}</text>
                
                <!-- Node ID code -->
                <text x="{{ $node['cx'] }}" y="{{ $node['cy'] + 27 }}" text-anchor="middle" font-size="8" font-weight="bold" fill="#718096">{{ $node['id'] }}</text>

                <!-- Condition Label -->
                @if(!empty($nodeState['condition']))
                    <text x="{{ $node['cx'] }}" y="{{ $node['cy'] + 36 }}" text-anchor="middle" font-size="7" font-weight="bold" fill="#e53e3e">{{ substr($nodeState['condition'], 0, 10) }}</text>
                @endif
            @endforeach
        </svg>
    </div>
    @if($intake->pedigree_remarks)
        <div class="remarks-block"><strong>Pedigree Chart Remarks:</strong> {{ $intake->pedigree_remarks }}</div>
    @endif

    <!-- Section 7: Child Routine -->
    <div class="section-title">7. Child Routine</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 40%;">Activity / Area</th>
                <th style="width: 60%;">Performance Level / Remark</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dressing (clothing/buttons)</td>
                <td>{{ ucfirst($intake->routine_dressing ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Grooming (hair/face)</td>
                <td>{{ ucfirst($intake->routine_grooming ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Socks (wearing & removing)</td>
                <td>{{ ucfirst($intake->routine_socks ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Bathing (washing/drying)</td>
                <td>{{ ucfirst($intake->routine_bathing ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Brushing teeth</td>
                <td>{{ ucfirst($intake->routine_brushing ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Eating (spoon use/independence)</td>
                <td>{{ ucfirst($intake->routine_eating ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Selective eating habits</td>
                <td>
                    @if(is_array($intake->routine_selective_eating) && count($intake->routine_selective_eating) > 0)
                        {{ implode(', ', $intake->routine_selective_eating) }}
                    @else
                        Normal eating behavior
                    @endif
                </td>
            </tr>
            <tr>
                <td>Drinking water (glass/cup handling)</td>
                <td>{{ ucfirst($intake->routine_drinking_water ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Gross Motor: Ball Catching</td>
                <td>{{ ucfirst($intake->routine_ball_catch ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Gross Motor: Ball Throwing</td>
                <td>{{ ucfirst($intake->routine_ball_throw ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td>Gross Motor: Heel-Toe Walk</td>
                <td>{{ $intake->routine_heel_toe_walk ? ucfirst($intake->routine_heel_toe_walk) : 'N/A' }}</td>
            </tr>
        </tbody>
    </table>
    @if($intake->routine_remark)
        <div class="remarks-block"><strong>Child Routine Remarks:</strong> {{ $intake->routine_remark }}</div>
    @endif

    <div class="page-break"></div>

    <!-- Section 8: Toileting Assessment -->
    <div class="section-title">8. Toileting Assessment</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 40%;">Toileting Skill (ADL)</th>
                <th style="width: 30%;">Performance Level</th>
                <th style="width: 30%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php
                $toiletSkills = [
                    ['Indicates toilet need', 'toilet_indicates_need'],
                    ['Goes to toilet on time', 'toilet_goes_on_time'],
                    ['Removes clothes independently', 'toilet_removes_clothes'],
                    ['Sits properly on toilet', 'toilet_sits_properly'],
                    ['Cleans self after toileting', 'toilet_cleans_self'],
                    ['Flushes toilet', 'toilet_flushes'],
                    ['Washes hands after toilet', 'toilet_washes_hands'],
                    ['Daytime bladder control', 'toilet_daytime_control'],
                    ['Nighttime bladder control', 'toilet_nighttime_control'],
                    ['Bowel control', 'toilet_bowel_control'],
                ];
            @endphp
            @foreach($toiletSkills as $ts)
                <tr>
                    <td>{{ $ts[0] }}</td>
                    <td>{{ ucfirst($intake->{$ts[1]} ?? 'N/A') }}</td>
                    <td>{{ $intake->{$ts[1] . '_remark'} ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="margin: 8px 0 4px 0; color:#2b6cb0; font-size: 9px; text-transform: uppercase;">Additional Toileting Information</h3>
    <table class="info-table">
        <tr>
            <td class="label">Toilet Trained:</td>
            <td class="value">{{ $intake->toilet_trained ?? 'N/A' }} ({{ $intake->toilet_indicates_before_after ?? 'Does not indicate' }})</td>
            <td class="label" style="padding-left:15px; width:15%;">Uses Diaper/Pull-up:</td>
            <td class="value">{{ $intake->toilet_uses_diaper ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Constipation Issues:</td>
            <td class="value">{{ $intake->toilet_constipation ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Toilet Avoidance/Fear:</td>
            <td class="value">{{ $intake->toilet_avoidance_fear ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Accidents Frequency:</td>
            <td class="value">{{ $intake->toilet_accidents_frequency ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Independence Score (1-10):</td>
            <td class="value">{{ $intake->toilet_independence_level ?? 'N/A' }}</td>
        </tr>
    </table>
    @if($intake->toilet_remark)
        <div class="remarks-block"><strong>Toileting Remarks:</strong> {{ $intake->toilet_remark }}</div>
    @endif

    <!-- Section 9: Fine Motor Skills -->
    <div class="section-title">9. Fine Motor Skills</div>
    <table class="info-table">
        <tr>
            <td class="label">Hand Preference:</td>
            <td class="value">{{ ucfirst($intake->fine_hand_preference ?? 'Mixed') }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Poor Grip / Grasp:</td>
            <td class="value">{{ $intake->fine_poor_grip ?? 'No' }} / {{ ucfirst($intake->fine_grasp_pattern ?? 'Normal') }}</td>
        </tr>
        <tr>
            <td class="label">Writing Difficulty:</td>
            <td class="value">{{ $intake->fine_writing_difficulty ?? 'No' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Small Objects Difficulty:</td>
            <td class="value">{{ $intake->fine_small_objects_difficulty ?? 'No' }}</td>
        </tr>
    </table>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 40%;">Fine Motor Skill</th>
                <th style="width: 30%;">Performance Level</th>
                <th style="width: 30%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php
                $fineMotorSkills = [
                    ['Holds pencil/crayon properly', 'fine_holds_pencil'],
                    ['Scribbling', 'fine_scribbling'],
                    ['Coloring within boundary', 'fine_coloring'],
                    ['Copying shapes', 'fine_copying_shapes'],
                    ['Writing letters/numbers', 'fine_writing_letters'],
                    ['Cutting with scissors', 'fine_cutting_scissors'],
                    ['Pasting activities', 'fine_pasting'],
                    ['Buttoning/unbuttoning', 'fine_buttoning'],
                    ['Zipping/unzipping', 'fine_zipping'],
                    ['Bead threading', 'fine_bead_threading'],
                    ['Opening containers', 'fine_opening_containers'],
                    ['Using spoon properly', 'fine_using_spoon'],
                    ['Bilateral hand use', 'fine_bilateral_hand'],
                    ['Hand strength', 'fine_hand_strength'],
                    ['Hand-eye coordination', 'fine_hand_eye'],
                ];
            @endphp
            @foreach($fineMotorSkills as $fm)
                <tr>
                    <td>{{ $fm[0] }}</td>
                    <td>{{ ucfirst($intake->{$fm[1]} ?? 'N/A') }}</td>
                    <td>{{ $intake->{$fm[1] . '_remark'} ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($intake->fine_remark)
        <div class="remarks-block"><strong>Fine Motor Remarks:</strong> {{ $intake->fine_remark }}</div>
    @endif

    <div class="page-break"></div>

    <!-- Section 10: Speech, Language & Oromotor -->
    <div class="section-title">10. Speech, Language & Oromotor</div>
    <table class="info-table">
        <tr>
            <td class="label">Communication:</td>
            <td class="value">{{ ucfirst($intake->speech_communication ?? 'N/A') }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Speech Clarity:</td>
            <td class="value">{{ ucfirst($intake->speech_clarity ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="label">Speech Issues:</td>
            <td class="value">
                @if(is_array($intake->speech_issues) && count($intake->speech_issues) > 0)
                    {{ implode(', ', $intake->speech_issues) }}
                @else
                    None reported
                @endif
            </td>
            <td class="label" style="padding-left:15px; width:15%;">WH Questions Answered:</td>
            <td class="value">
                @if(is_array($intake->speech_wh_questions) && count($intake->speech_wh_questions) > 0)
                    {{ implode(', ', $intake->speech_wh_questions) }}
                @else
                    None
                @endif
            </td>
        </tr>
    </table>

    <h3 style="margin: 8px 0 4px 0; color:#2b6cb0; font-size: 9px; text-transform: uppercase;">Oromotor & Lip/Palate Examination</h3>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 25%;">Skill Area</th>
                <th style="width: 25%;">Status</th>
                <th style="width: 25%;">Skill Area</th>
                <th style="width: 25%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Drooling</strong></td>
                <td>{{ ucfirst($intake->oromotor_drooling ?? 'No') }}</td>
                <td><strong>Bite Strength</strong></td>
                <td>{{ ucfirst($intake->oromotor_bite ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td><strong>Chewing</strong></td>
                <td>{{ ucfirst($intake->oromotor_chewing ?? 'N/A') }}</td>
                <td><strong>Swallowing</strong></td>
                <td>{{ ucfirst($intake->oromotor_swallowing ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td><strong>Straw Drinking</strong></td>
                <td>{{ ucfirst($intake->oromotor_straw_drinking ?? 'N/A') }}</td>
                <td><strong>Spoon Feeding</strong></td>
                <td>{{ ucfirst($intake->oromotor_spoon_feeding ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td><strong>Blowing Skill</strong></td>
                <td>{{ ucfirst($intake->oromotor_blowing ?? 'N/A') }}</td>
                <td><strong>Tongue Tie</strong></td>
                <td>{{ ucfirst($intake->tongue_tie ?? 'Absent') }}</td>
            </tr>
            <tr>
                <td><strong>Lip Closure</strong></td>
                <td>{{ ucfirst($intake->lip_closure ?? 'Adequate') }}</td>
                <td><strong>Palate Exam</strong></td>
                <td>{{ ucfirst($intake->palate_exam ?? 'Normal') }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Tongue Movement</strong></td>
                <td colspan="2">
                    @php
                        $tongueMovements = [];
                        if($intake->tongue_protrusion) $tongueMovements[] = 'Protrusion';
                        if($intake->tongue_retraction) $tongueMovements[] = 'Retraction';
                        if($intake->tongue_elevation) $tongueMovements[] = 'Elevation';
                        if($intake->tongue_lateralization) $tongueMovements[] = 'Lateralization';
                    @endphp
                    @if(count($tongueMovements) > 0)
                        {{ implode(', ', $tongueMovements) }}
                    @else
                        Normal Movement
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <h3 style="margin: 8px 0 4px 0; color:#2b6cb0; font-size: 9px; text-transform: uppercase;">Home Language Environment</h3>
    <table class="info-table">
        <tr>
            <td class="label">Home Languages:</td>
            <td class="value">{{ $intake->lang_languages_spoken ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Preferred Language:</td>
            <td class="value">{{ $intake->lang_preferred ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Comm. Style:</td>
            <td class="value" colspan="3">
                @if(is_array($intake->lang_communication_style) && count($intake->lang_communication_style) > 0)
                    {{ implode(', ', $intake->lang_communication_style) }}
                @else
                    N/A
                @endif
            </td>
        </tr>
    </table>
    @if($intake->speech_remark)
        <div class="remarks-block"><strong>Speech & Oromotor Remarks:</strong> {{ $intake->speech_remark }}</div>
    @endif

    <!-- Section 11: Social & Play History -->
    <div class="section-title">11. Social & Play History</div>
    <table class="info-table">
        <tr>
            <td class="label">Primary Caregiver:</td>
            <td class="value">{{ $intake->social_caregiver ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Identifies Parents / Relatives:</td>
            <td class="value">{{ $intake->social_identifies_parents ?? 'Yes' }} / {{ $intake->social_identifies_relatives ?? 'Yes' }}</td>
        </tr>
        <tr>
            <td class="label">Responds to Name:</td>
            <td class="value">{{ $intake->social_responds_name ?? 'Yes' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Eye Contact / Imitation:</td>
            <td class="value">{{ $intake->social_eye_contact ?? 'Yes' }} / {{ $intake->social_imitation ?? 'Yes' }}</td>
        </tr>
        <tr>
            <td class="label">Turn Taking / Sharing:</td>
            <td class="value">{{ $intake->social_turn_taking ?? 'Yes' }} / {{ $intake->social_sharing ?? 'Yes' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Rules of Games:</td>
            <td class="value">{{ $intake->social_rules_of_games ?? 'Yes' }}</td>
        </tr>
        <tr>
            <td class="label">Separation / Stranger Anxiety:</td>
            <td class="value">{{ $intake->social_separation_anxiety ?? 'No' }} / {{ $intake->social_stranger_anxiety ?? 'No' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Follows Group Activities:</td>
            <td class="value">{{ $intake->social_follows_group ?? 'Yes' }}</td>
        </tr>
        <tr>
            <td class="label">Favorite Toys:</td>
            <td class="value" colspan="3">{{ $intake->social_favorite_toys ?? 'None' }}</td>
        </tr>
        <tr>
            <td class="label">Play Behaviors:</td>
            <td class="value" colspan="3">
                @if(is_array($intake->social_play_behavior) && count($intake->social_play_behavior) > 0)
                    {{ implode(', ', $intake->social_play_behavior) }}
                @else
                    N/A
                @endif
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <h3 style="margin: 8px 0 4px 0; color:#2b6cb0; font-size: 9px; text-transform: uppercase;">Maladaptive Behavior Observations</h3>
    <table class="grid-table">
        <thead>
            <tr>
                <th>Behavior Observation</th>
                <th>Status</th>
                <th>Behavior Observation</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $maladaptives = is_array($intake->social_maladaptive_behavior) ? $intake->social_maladaptive_behavior : [];
                $obs = [
                    ['Hyperactivity', 'Hyperactivity'],
                    ['Inattention', 'Attention'],
                    ['Impulsivity', 'Impulsivity'],
                    ['Tantrums', 'Tantrums'],
                    ['Aggression', 'Aggression'],
                    ['Hitting others', 'Hitting'],
                    ['Biting others', 'Biting'],
                    ['Self Injurious Behavior', 'Self Injurious Behaviour'],
                    ['Hand Flapping', 'Hand Flapping'],
                    ['Repetitive Behaviors', 'Repetitive Behaviours'],
                    ['Lining Up Objects', 'Lining Objects'],
                ];
            @endphp
            @for($i = 0; $i < count($obs); $i += 2)
                <tr>
                    <td><strong>{{ $obs[$i][0] }}</strong></td>
                    <td style="font-weight: bold; color: {{ in_array($obs[$i][1], $maladaptives) ? '#e53e3e' : '#2d3748' }}">
                        {{ in_array($obs[$i][1], $maladaptives) ? 'Yes' : 'No' }}
                    </td>
                    @if($i + 1 < count($obs))
                        <td><strong>{{ $obs[$i+1][0] }}</strong></td>
                        <td style="font-weight: bold; color: {{ in_array($obs[$i+1][1], $maladaptives) ? '#e53e3e' : '#2d3748' }}">
                            {{ in_array($obs[$i+1][1], $maladaptives) ? 'Yes' : 'No' }}
                        </td>
                    @else
                        <td></td>
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="info-table">
        <tr>
            <td class="label">Screen Time / Day:</td>
            <td class="value">{{ $intake->social_average_screen_time ?? 'N/A' }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Sleep / Eating Habits:</td>
            <td class="value">{{ $intake->social_sleep_pattern ?? 'Normal' }} / {{ $intake->social_eating_habits ?? 'Normal' }}</td>
        </tr>
        <tr>
            <td class="label">Emotional Regulation:</td>
            <td class="value">{{ ucfirst($intake->social_emotional_regulation ?? 'Appropriate') }}</td>
            <td class="label" style="padding-left:15px; width:15%;">Outdoor Play:</td>
            <td class="value">{{ ucfirst($intake->social_outdoor_play ?? 'Occasional') }}</td>
        </tr>
    </table>
    @if($intake->social_remark)
        <div class="remarks-block"><strong>Social History Remarks:</strong> {{ $intake->social_remark }}</div>
    @endif

    <!-- Section 12: Cognitive & Academic -->
    <div class="section-title">12. Cognitive & Academic</div>
    <table class="info-table">
        <tr>
            <td class="label">Recognition:</td>
            <td class="value" colspan="3">
                @if(is_array($intake->cognitive_recognition) && count($intake->cognitive_recognition) > 0)
                    {{ implode(', ', $intake->cognitive_recognition) }}
                @else
                    None reported
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Academic Skills:</td>
            <td class="value" colspan="3">
                @if(is_array($intake->academic_skills) && count($intake->academic_skills) > 0)
                    {{ implode(', ', $intake->academic_skills) }}
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Attention / Memory:</td>
            <td class="value">{{ ucfirst($intake->cognitive_attention ?? 'N/A') }} / {{ ucfirst($intake->cognitive_memory ?? 'N/A') }}</td>
            <td class="label" style="padding-left:15px; width:15%;">School Feedback:</td>
            <td class="value">{{ $intake->cognitive_school_feedback ?? 'None' }}</td>
        </tr>
    </table>
    @if($intake->cognitive_remark)
        <div class="remarks-block"><strong>Cognitive Remarks:</strong> {{ $intake->cognitive_remark }}</div>
    @endif

    <!-- Section 13: Clinical Therapy Plan -->
    <div class="section-title">13. Clinical Therapy Plan</div>
    <table class="info-table">
        <tr>
            <td class="label">Therapies Suggested:</td>
            <td class="value" colspan="3">
                @if(is_array($intake->plan_therapy_suggested) && count($intake->plan_therapy_suggested) > 0)
                    <strong>{{ implode(', ', $intake->plan_therapy_suggested) }}</strong>
                @else
                    None Suggestion
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Home Program Given:</td>
            <td class="value" colspan="3">{{ $intake->plan_home_program_given ?? 'No' }}</td>
        </tr>
        <tr>
            <td class="label">Final Clinical Impression:</td>
            <td class="value" colspan="3" style="font-weight: bold; font-size: 11px;">{{ $intake->plan_final_impression ?? 'No final impression' }}</td>
        </tr>
        @if($intake->plan_remark)
        <tr>
            <td class="label">Plan Remarks:</td>
            <td class="value" colspan="3">{{ $intake->plan_remark }}</td>
        </tr>
        @endif
    </table>

    <br><br><br>
    <!-- Signatures -->
    <table style="width: 100%; margin-top: 30px; page-break-inside: avoid;">
        <tr>
            <td style="width: 50%; text-align: left;">
                <p>Clinical Assessor Signature: ___________________________</p>
                <p>Date: ___________________________</p>
            </td>
            <td style="width: 50%; text-align: right;">
                <p>Parent / Guardian Signature: ___________________________</p>
                <p>Date: ___________________________</p>
            </td>
        </tr>
    </table>

    <div class="footer-text">
        Helping Hands Child Development And Education Centre | Confidential Medical Record
    </div>

</body>
</html>
