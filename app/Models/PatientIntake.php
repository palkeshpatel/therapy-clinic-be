<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientIntake extends Model
{
    protected $table = 'patient_intakes';

    protected $fillable = [
        'patient_id',
        'date_of_assessment',
        'status',

        // Demographics
        'child_name',
        'dob',
        'age',
        'gender',
        'father_name',
        'father_occupation',
        'father_phone',
        'mother_name',
        'mother_occupation',
        'mother_phone',
        'email',
        'school_grade',
        'informant',
        'address',
        'previous_therapy',
        'referral_by',
        'chief_complaint',

        // Personal History - Natal
        'natal_mother_age',
        'natal_mother_name_age',
        'natal_father_name_age',
        'natal_place_delivery',
        'natal_vaccination_history',
        'natal_pregnancy_history',
        'natal_delivery_type',
        'natal_gestation',

        // Personal History - Perinatal
        'perinatal_medical_condition',
        'perinatal_medication',
        'perinatal_anxiety',
        'perinatal_depression',
        'perinatal_social_withdrawal',
        'perinatal_eating_difficulties',
        'perinatal_sleeping',
        'perinatal_other',

        // Personal History - Postnatal & Child History
        'postnatal_complication',
        'postnatal_concerns',
        'child_birth_weight',
        'child_nicu_admission',
        'child_birth_cry',
        'child_jaundice',
        'child_convulsions',
        'child_birth_asphyxia',
        'child_congenital_anomaly',
        'child_remark',

        // Medical & Surgical History
        'med_prev_hospitalization',
        'med_prev_infection',
        'med_seizure_history',
        'med_medication_history',
        'med_surgical_history',
        'med_blood_transfusion',
        'med_remark',

        // Current History
        'current_medical_condition',
        'current_medication',
        'current_allergy_history',
        'current_medication_allergy',

        // Developmental Milestones
        'milestone_social_smile',
        'milestone_neck_holding',
        'milestone_roll_over',
        'milestone_cooing',
        'milestone_sitting_independently',
        'milestone_babbling',
        'milestone_crawling',
        'milestone_standing_independently',
        'milestone_walking_independently',
        'milestone_use_of_meaningful_word',
        'milestone_phrases',
        'milestone_simple_sentence',
        'milestone_complex_sentence',
        'milestone_toilet_control',
        'milestone_remark',

        // Family History
        'family_structure',
        'family_history',
        'family_consanguinity',
        'sibling_info',
        'sibling_age',
        'family_remark',
        'pedigree_chart_data',
        'pedigree_remarks',

        // Child Routine
        'routine_dressing',
        'routine_grooming',
        'routine_socks',
        'routine_bathing',
        'routine_brushing',
        'routine_eating',
        'routine_selective_eating',
        'routine_drinking_water',
        'routine_ball_catch',
        'routine_ball_throw',
        'routine_heel_toe_walk',
        'routine_remark',

        // Toileting Assessment Status & Remarks
        'toilet_indicates_need',
        'toilet_indicates_need_remark',
        'toilet_goes_on_time',
        'toilet_goes_on_time_remark',
        'toilet_removes_clothes',
        'toilet_removes_clothes_remark',
        'toilet_sits_properly',
        'toilet_sits_properly_remark',
        'toilet_cleans_self',
        'toilet_cleans_self_remark',
        'toilet_flushes',
        'toilet_flushes_remark',
        'toilet_washes_hands',
        'toilet_washes_hands_remark',
        'toilet_daytime_control',
        'toilet_daytime_control_remark',
        'toilet_nighttime_control',
        'toilet_nighttime_control_remark',
        'toilet_bowel_control',
        'toilet_bowel_control_remark',

        // Toileting Additional
        'toilet_trained',
        'toilet_indicates_before_after',
        'toilet_uses_diaper',
        'toilet_constipation',
        'toilet_avoidance_fear',
        'toilet_accidents_frequency',
        'toilet_independence_level',
        'toilet_remark',

        // Fine Motor Assessment Status & Remarks
        'fine_holds_pencil',
        'fine_holds_pencil_remark',
        'fine_scribbling',
        'fine_scribbling_remark',
        'fine_coloring',
        'fine_coloring_remark',
        'fine_copying_shapes',
        'fine_copying_shapes_remark',
        'fine_writing_letters',
        'fine_writing_letters_remark',
        'fine_cutting_scissors',
        'fine_cutting_scissors_remark',
        'fine_pasting',
        'fine_pasting_remark',
        'fine_buttoning',
        'fine_buttoning_remark',
        'fine_zipping',
        'fine_zipping_remark',
        'fine_bead_threading',
        'fine_bead_threading_remark',
        'fine_opening_containers',
        'fine_opening_containers_remark',
        'fine_using_spoon',
        'fine_using_spoon_remark',
        'fine_bilateral_hand',
        'fine_bilateral_hand_remark',
        'fine_hand_strength',
        'fine_hand_strength_remark',
        'fine_hand_eye',
        'fine_hand_eye_remark',

        // Fine Motor Additional
        'fine_hand_preference',
        'fine_poor_grip',
        'fine_grasp_pattern',
        'fine_writing_difficulty',
        'fine_small_objects_difficulty',
        'fine_remark',

        // Speech, Language & Oromotor
        'speech_communication',
        'speech_clarity',
        'speech_issues',
        'speech_wh_questions',
        'oromotor_drooling',
        'oromotor_bite',
        'oromotor_chewing',
        'oromotor_swallowing',
        'oromotor_straw_drinking',
        'oromotor_spoon_feeding',
        'oromotor_blowing',
        'tongue_protrusion',
        'tongue_retraction',
        'tongue_elevation',
        'tongue_lateralization',
        'tongue_tie',
        'palate_exam',
        'lip_closure',
        'lang_languages_spoken',
        'lang_preferred',
        'lang_communication_style',
        'speech_remark',

        // Social & Play
        'social_caregiver',
        'social_identifies_parents',
        'social_identifies_relatives',
        'social_eye_contact',
        'social_responds_name',
        'social_imitation',
        'social_rules_of_games',
        'social_sharing',
        'social_turn_taking',
        'social_follows_group',
        'social_stranger_anxiety',
        'social_separation_anxiety',
        'social_favorite_toys',
        'social_play_behavior',
        'social_maladaptive_behavior',
        'social_emotional_regulation',
        'social_average_screen_time',
        'social_sleep_pattern',
        'social_eating_habits',
        'social_outdoor_play',
        'social_remark',

        // Cognitive & Academic
        'cognitive_recognition',
        'academic_skills',
        'cognitive_attention',
        'cognitive_memory',
        'cognitive_school_feedback',
        'cognitive_remark',

        // Therapy Plan
        'plan_therapy_suggested',
        'plan_home_program_given',
        'plan_final_impression',
        'plan_remark',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'date_of_assessment' => 'date',
        'dob' => 'date',
        'age' => 'integer',
        'natal_mother_age' => 'integer',
        'perinatal_anxiety' => 'boolean',
        'perinatal_depression' => 'boolean',
        'perinatal_social_withdrawal' => 'boolean',
        'perinatal_eating_difficulties' => 'boolean',
        'perinatal_sleeping' => 'boolean',

        // JSON columns cast to array
        'previous_therapy' => 'array',
        'natal_pregnancy_history' => 'array',
        'family_history' => 'array',
        'pedigree_chart_data' => 'array',
        'routine_selective_eating' => 'array',
        'speech_issues' => 'array',
        'speech_wh_questions' => 'array',
        'lang_communication_style' => 'array',
        'social_play_behavior' => 'array',
        'social_maladaptive_behavior' => 'array',
        'cognitive_recognition' => 'array',
        'academic_skills' => 'array',
        'plan_therapy_suggested' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
