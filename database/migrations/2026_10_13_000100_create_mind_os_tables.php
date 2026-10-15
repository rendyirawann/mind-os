<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mind OS domain: Foundation modules, Bug Detector, Jurnal Recognize,
 * Audio Guided Healing, 6R Tracker, Action Plan 30 Hari, Komunitas,
 * booking Online Live / Tatap Muka, sertifikasi dan evaluasi 30 hari.
 *
 * Every per-user table is indexed on (user_id, created_at) because the
 * member area always lists "my latest X".
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- 1. Modul Foundation -------------------------------------------
        Schema::create('foundation_modules', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category', 20)->index(); // filosofi | lapisan | bug
            $table->string('title');
            $table->string('summary', 500);
            $table->text('content');
            $table->unsignedSmallInteger('duration_minutes')->default(10);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('module_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('foundation_module_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->unique(['user_id', 'foundation_module_id']);
        });

        // --- 2. Bug Detector -----------------------------------------------
        Schema::create('mind_bugs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('tagline');
            $table->text('description');
            $table->text('symptoms');
            $table->text('reframe_hint');
            $table->string('color', 20)->default('primary');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('bug_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mind_bug_id')->constrained()->cascadeOnDelete();
            $table->string('question', 500);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('bug_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dominant_bug_id')->nullable()->constrained('mind_bugs')->nullOnDelete();
            $table->unsignedSmallInteger('total_score');
            $table->unsignedTinyInteger('percentage');
            $table->string('level', 20); // ringan | sedang | berat
            $table->string('recommendation', 30); // web_app | online_live | tatap_muka
            $table->json('scores'); // {bug_id: percentage}
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // --- 3. Jurnal Recognize -------------------------------------------
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mind_bug_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('situation');
            $table->text('thought');
            $table->string('feeling', 100);
            $table->string('body_response')->nullable();
            $table->unsignedTinyInteger('intensity'); // 1-10
            $table->text('insight')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // --- 4. Audio Guided Healing ---------------------------------------
        Schema::create('audio_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('description', 500);
            $table->string('stage', 4)->index(); // R1..R6
            $table->unsignedInteger('duration_seconds')->default(600);
            $table->string('audio_url')->nullable();
            $table->text('script')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('audio_listens', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audio_track_id')->constrained()->cascadeOnDelete();
            $table->timestamp('listened_at');

            $table->index(['user_id', 'listened_at']);
        });

        // --- 5. 6R Tracker -------------------------------------------------
        Schema::create('six_r_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mind_bug_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage', 4); // R1 R2 R3 R4 R5A R5B R6
            $table->string('modality', 12); // web | online | offline
            $table->text('note');
            $table->unsignedTinyInteger('intensity_before')->nullable();
            $table->unsignedTinyInteger('intensity_after')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'stage']);
        });

        // --- 6. Action Plan 30 Hari ----------------------------------------
        Schema::create('action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mind_bug_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('goal');
            $table->date('start_date');
            $table->string('status', 12)->default('active'); // active | completed | stopped
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('action_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_number');
            $table->string('task');
            $table->timestamp('done_at')->nullable();
            $table->string('reflection', 500)->nullable();

            $table->unique(['action_plan_id', 'day_number']);
        });

        // --- 7. Evaluasi 30 Hari -------------------------------------------
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('action_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('score_before'); // 1-10 seberapa berat masalah
            $table->unsignedTinyInteger('score_after');
            $table->string('result', 12); // berhasil | belum
            $table->string('next_step', 20); // self_mastery | konsultasi | sertifikasi | komunitas
            $table->text('reflection');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // --- 8. Komunitas --------------------------------------------------
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 20)->default('cerita'); // cerita | tanya | insight | kemenangan
            $table->string('title');
            $table->text('body');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();

            $table->index(['is_pinned', 'created_at']);
            $table->index(['category', 'created_at']);
        });

        Schema::create('community_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['community_post_id', 'created_at']);
        });

        Schema::create('community_likes', function (Blueprint $table) {
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['community_post_id', 'user_id']);
        });

        // --- 9. Layanan Online Live / Tatap Muka + Booking -----------------
        Schema::create('mind_services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('modality', 12)->index(); // online | offline
            $table->string('name');
            $table->string('stage', 4)->nullable(); // 6R stage covered
            $table->string('description', 600);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->boolean('is_group')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mind_service_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('practitioner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('status', 12)->default('pending'); // pending | confirmed | completed | cancelled
            $table->string('payment_status', 10)->default('unpaid'); // unpaid | paid | free
            $table->unsignedInteger('amount')->default(0);
            $table->string('complaint', 1000);
            $table->string('meeting_link')->nullable();
            $table->string('location')->nullable();
            $table->text('practitioner_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'scheduled_at']);
            $table->index(['practitioner_id', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
        });

        // --- 10. Sertifikasi & Lisensi -------------------------------------
        Schema::create('certification_programs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('level', 15); // practitioner | master | trainer
            $table->string('name');
            $table->string('prerequisite');
            $table->unsignedSmallInteger('hours');
            $table->unsignedSmallInteger('practice_clients');
            $table->json('curriculum');
            $table->string('description', 800);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('certification_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certification_program_id')->constrained()->restrictOnDelete();
            $table->string('status', 12)->default('applied'); // applied | learning | exam | certified | rejected
            $table->unsignedSmallInteger('hours_done')->default(0);
            $table->unsignedSmallInteger('clients_done')->default(0);
            $table->string('motivation', 1000);
            $table->string('certificate_number', 40)->nullable()->unique();
            $table->timestamp('certified_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'certification_program_id']);
            $table->index(['status', 'created_at']);
        });

        // --- Query hot-spots in the existing activity log ------------------
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->index('created_at', 'activity_log_created_at_idx');
                $table->index(['causer_id', 'created_at'], 'activity_log_causer_created_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropIndex('activity_log_created_at_idx');
                $table->dropIndex('activity_log_causer_created_idx');
            });
        }

        foreach ([
            'certification_enrollments', 'certification_programs', 'bookings', 'mind_services',
            'community_likes', 'community_comments', 'community_posts', 'evaluations',
            'action_plan_days', 'action_plans', 'six_r_logs', 'audio_listens', 'audio_tracks',
            'journals', 'bug_assessments', 'bug_questions', 'mind_bugs', 'module_progress',
            'foundation_modules',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
