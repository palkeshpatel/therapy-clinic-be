<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Disable strict mode temporarily to allow creating the table with 150+ columns
        DB::statement('SET SESSION innodb_strict_mode=0');

        Schema::create('patient_intakes', function (Blueprint $table) {
            // Force InnoDB and DYNAMIC row format to allow 150+ columns
            $table->engine = 'InnoDB ROW_FORMAT=DYNAMIC';

            // Core columns
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->date('date_of_assessment')->nullable();
            $table->string('status', 30)->default('draft');

            // Demographics
            $table->text('child_name')->nullable();
            $table->date('dob')->nullable();
            $table->integer('age')->nullable();
            $table->text('gender')->nullable();
            $table->text('father_name')->nullable();
            $table->text('father_occupation')->nullable();
            $table->text('father_phone')->nullable();
            $table->text('mother_name')->nullable();
            $table->text('mother_occupation')->nullable();
            $table->text('mother_phone')->nullable();
            $table->text('email')->nullable();
            $table->text('school_grade')->nullable();
            $table->text('informant')->nullable();
            $table->text('address')->nullable();
            $table->json('previous_therapy')->nullable();
            $table->text('referral_by')->nullable();
            $table->text('chief_complaint')->nullable();

            // Personal History - Natal
            $table->integer('natal_mother_age')->nullable();
            $table->text('natal_mother_name_age')->nullable();
            $table->text('natal_father_name_age')->nullable();
            $table->text('natal_place_delivery')->nullable();
            $table->text('natal_vaccination_history')->nullable();
            $table->json('natal_pregnancy_history')->nullable();
            $table->text('natal_delivery_type')->nullable();
            $table->text('natal_gestation')->nullable();

            // Personal History - Perinatal
            $table->text('perinatal_medical_condition')->nullable();
            $table->text('perinatal_medication')->nullable();
            $table->boolean('perinatal_anxiety')->nullable();
            $table->boolean('perinatal_depression')->nullable();
            $table->boolean('perinatal_social_withdrawal')->nullable();
            $table->boolean('perinatal_eating_difficulties')->nullable();
            $table->boolean('perinatal_sleeping')->nullable();
            $table->text('perinatal_other')->nullable();

            // Personal History - Postnatal & Child History
            $table->text('postnatal_complication')->nullable();
            $table->text('postnatal_concerns')->nullable();
            $table->text('child_birth_weight')->nullable();
            $table->text('child_nicu_admission')->nullable();
            $table->text('child_birth_cry')->nullable();
            $table->text('child_jaundice')->nullable();
            $table->text('child_convulsions')->nullable();
            $table->text('child_birth_asphyxia')->nullable();
            $table->text('child_congenital_anomaly')->nullable();
            $table->text('child_remark')->nullable();

            // Medical & Surgical History
            $table->text('med_prev_hospitalization')->nullable();
            $table->text('med_prev_infection')->nullable();
            $table->text('med_seizure_history')->nullable();
            $table->text('med_medication_history')->nullable();
            $table->text('med_surgical_history')->nullable();
            $table->text('med_blood_transfusion')->nullable();
            $table->text('med_remark')->nullable();

            // Current History
            $table->text('current_medical_condition')->nullable();
            $table->text('current_medication')->nullable();
            $table->text('current_allergy_history')->nullable();
            $table->text('current_medication_allergy')->nullable();

            // Developmental milestones
            $table->text('milestone_social_smile')->nullable();
            $table->text('milestone_neck_holding')->nullable();
            $table->text('milestone_roll_over')->nullable();
            $table->text('milestone_cooing')->nullable();
            $table->text('milestone_sitting_independently')->nullable();
            $table->text('milestone_babbling')->nullable();
            $table->text('milestone_crawling')->nullable();
            $table->text('milestone_standing_independently')->nullable();
            $table->text('milestone_walking_independently')->nullable();
            $table->text('milestone_use_of_meaningful_word')->nullable();
            $table->text('milestone_phrases')->nullable();
            $table->text('milestone_simple_sentence')->nullable();
            $table->text('milestone_complex_sentence')->nullable();
            $table->text('milestone_toilet_control')->nullable();
            $table->text('milestone_remark')->nullable();

            // Family History
            $table->text('family_structure')->nullable();
            $table->json('family_history')->nullable();
            $table->text('family_consanguinity')->nullable();
            $table->text('sibling_info')->nullable();
            $table->text('sibling_age')->nullable();
            $table->text('family_remark')->nullable();

            // Child Routine
            $table->text('routine_dressing')->nullable();
            $table->text('routine_grooming')->nullable();
            $table->text('routine_socks')->nullable();
            $table->text('routine_bathing')->nullable();
            $table->text('routine_brushing')->nullable();
            $table->text('routine_eating')->nullable();
            $table->json('routine_selective_eating')->nullable();
            $table->text('routine_drinking_water')->nullable();
            $table->text('routine_ball_catch')->nullable();
            $table->text('routine_ball_throw')->nullable();
            $table->text('routine_heel_toe_walk')->nullable();
            $table->text('routine_remark')->nullable();

            // Toileting Assessment Status & Remarks
            $table->text('toilet_indicates_need')->nullable();
            $table->text('toilet_indicates_need_remark')->nullable();
            $table->text('toilet_goes_on_time')->nullable();
            $table->text('toilet_goes_on_time_remark')->nullable();
            $table->text('toilet_removes_clothes')->nullable();
            $table->text('toilet_removes_clothes_remark')->nullable();
            $table->text('toilet_sits_properly')->nullable();
            $table->text('toilet_sits_properly_remark')->nullable();
            $table->text('toilet_cleans_self')->nullable();
            $table->text('toilet_cleans_self_remark')->nullable();
            $table->text('toilet_flushes')->nullable();
            $table->text('toilet_flushes_remark')->nullable();
            $table->text('toilet_washes_hands')->nullable();
            $table->text('toilet_washes_hands_remark')->nullable();
            $table->text('toilet_daytime_control')->nullable();
            $table->text('toilet_daytime_control_remark')->nullable();
            $table->text('toilet_nighttime_control')->nullable();
            $table->text('toilet_nighttime_control_remark')->nullable();
            $table->text('toilet_bowel_control')->nullable();
            $table->text('toilet_bowel_control_remark')->nullable();

            // Toileting Additional
            $table->text('toilet_trained')->nullable();
            $table->text('toilet_indicates_before_after')->nullable();
            $table->text('toilet_uses_diaper')->nullable();
            $table->text('toilet_constipation')->nullable();
            $table->text('toilet_avoidance_fear')->nullable();
            $table->text('toilet_accidents_frequency')->nullable();
            $table->text('toilet_independence_level')->nullable();
            $table->text('toilet_remark')->nullable();

            // Fine Motor Assessment Status & Remarks
            $table->text('fine_holds_pencil')->nullable();
            $table->text('fine_holds_pencil_remark')->nullable();
            $table->text('fine_scribbling')->nullable();
            $table->text('fine_scribbling_remark')->nullable();
            $table->text('fine_coloring')->nullable();
            $table->text('fine_coloring_remark')->nullable();
            $table->text('fine_copying_shapes')->nullable();
            $table->text('fine_copying_shapes_remark')->nullable();
            $table->text('fine_writing_letters')->nullable();
            $table->text('fine_writing_letters_remark')->nullable();
            $table->text('fine_cutting_scissors')->nullable();
            $table->text('fine_cutting_scissors_remark')->nullable();
            $table->text('fine_pasting')->nullable();
            $table->text('fine_pasting_remark')->nullable();
            $table->text('fine_buttoning')->nullable();
            $table->text('fine_buttoning_remark')->nullable();
            $table->text('fine_zipping')->nullable();
            $table->text('fine_zipping_remark')->nullable();
            $table->text('fine_bead_threading')->nullable();
            $table->text('fine_bead_threading_remark')->nullable();
            $table->text('fine_opening_containers')->nullable();
            $table->text('fine_opening_containers_remark')->nullable();
            $table->text('fine_using_spoon')->nullable();
            $table->text('fine_using_spoon_remark')->nullable();
            $table->text('fine_bilateral_hand')->nullable();
            $table->text('fine_bilateral_hand_remark')->nullable();
            $table->text('fine_hand_strength')->nullable();
            $table->text('fine_hand_strength_remark')->nullable();
            $table->text('fine_hand_eye')->nullable();
            $table->text('fine_hand_eye_remark')->nullable();

            // Fine Motor Additional
            $table->text('fine_hand_preference')->nullable();
            $table->text('fine_poor_grip')->nullable();
            $table->text('fine_grasp_pattern')->nullable();
            $table->text('fine_writing_difficulty')->nullable();
            $table->text('fine_small_objects_difficulty')->nullable();
            $table->text('fine_remark')->nullable();

            // Speech, Language & Oromotor
            $table->text('speech_communication')->nullable();
            $table->text('speech_clarity')->nullable();
            $table->json('speech_issues')->nullable();
            $table->json('speech_wh_questions')->nullable();
            $table->text('oromotor_drooling')->nullable();
            $table->text('oromotor_bite')->nullable();
            $table->text('oromotor_chewing')->nullable();
            $table->text('oromotor_swallowing')->nullable();
            $table->text('oromotor_straw_drinking')->nullable();
            $table->text('oromotor_spoon_feeding')->nullable();
            $table->text('oromotor_blowing')->nullable();
            $table->text('tongue_protrusion')->nullable();
            $table->text('tongue_retraction')->nullable();
            $table->text('tongue_elevation')->nullable();
            $table->text('tongue_lateralization')->nullable();
            $table->text('tongue_tie')->nullable();
            $table->text('palate_exam')->nullable();
            $table->text('lip_closure')->nullable();
            $table->text('lang_languages_spoken')->nullable();
            $table->text('lang_preferred')->nullable();
            $table->json('lang_communication_style')->nullable();
            $table->text('speech_remark')->nullable();

            // Social & Play
            $table->text('social_caregiver')->nullable();
            $table->text('social_identifies_parents')->nullable();
            $table->text('social_identifies_relatives')->nullable();
            $table->text('social_eye_contact')->nullable();
            $table->text('social_responds_name')->nullable();
            $table->text('social_imitation')->nullable();
            $table->text('social_rules_of_games')->nullable();
            $table->text('social_sharing')->nullable();
            $table->text('social_turn_taking')->nullable();
            $table->text('social_follows_group')->nullable();
            $table->text('social_stranger_anxiety')->nullable();
            $table->text('social_separation_anxiety')->nullable();
            $table->text('social_favorite_toys')->nullable();
            $table->json('social_play_behavior')->nullable();
            $table->json('social_maladaptive_behavior')->nullable();
            $table->text('social_emotional_regulation')->nullable();
            $table->text('social_average_screen_time')->nullable();
            $table->text('social_sleep_pattern')->nullable();
            $table->text('social_eating_habits')->nullable();
            $table->text('social_outdoor_play')->nullable();
            $table->text('social_remark')->nullable();

            // Cognitive & Academic
            $table->json('cognitive_recognition')->nullable();
            $table->json('academic_skills')->nullable();
            $table->text('cognitive_attention')->nullable();
            $table->text('cognitive_memory')->nullable();
            $table->text('cognitive_school_feedback')->nullable();
            $table->text('cognitive_remark')->nullable();

            // Therapy Plan
            $table->json('plan_therapy_suggested')->nullable();
            $table->text('plan_home_program_given')->nullable();
            $table->text('plan_final_impression')->nullable();
            $table->text('plan_remark')->nullable();

            $table->timestamps();

            // Foreign key relation
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_intakes');
    }
};
