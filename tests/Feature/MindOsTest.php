<?php

namespace Tests\Feature;

use App\Models\MindOs\ActionPlan;
use App\Models\MindOs\AudioTrack;
use App\Models\MindOs\BugAssessment;
use App\Models\MindOs\BugQuestion;
use App\Models\MindOs\Booking;
use App\Models\MindOs\CertificationEnrollment;
use App\Models\MindOs\CertificationProgram;
use App\Models\MindOs\CommunityPost;
use App\Models\MindOs\FoundationModule;
use App\Models\MindOs\Journal;
use App\Models\MindOs\MindService;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Database\Seeders\MindOsSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MindOsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, MindOsSeeder::class]);
    }

    private function user(string $role = 'Klien'): User
    {
        $user = User::factory()->create(['username' => 'u' . uniqid(), 'is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_landing_page_is_public_seo_ready_and_hardened(): void
    {
        $res = $this->get('/');

        $res->assertOk()
            ->assertSee('Mind OS', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('FAQPage', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('index, follow', false)
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy')
            ->assertHeader('Permissions-Policy')
            ->assertHeader('Content-Security-Policy');

        $this->assertStringNotContainsStringIgnoringCase('startertemp', $res->getContent());
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertSee('<urlset', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /app/')->assertSee('Sitemap:');
    }

    public function test_member_pages_render_and_are_noindex(): void
    {
        $user = $this->user();
        $module = FoundationModule::first();
        $track = AudioTrack::first();
        $service = MindService::first();

        foreach ([
            route('mindos.journey'), route('mindos.foundation.index'), route('mindos.foundation.show', $module),
            route('mindos.bug-detector.index'), route('mindos.journal.index'), route('mindos.journal.create'),
            route('mindos.audio.index'), route('mindos.audio.show', $track), route('mindos.six-r.index'),
            route('mindos.action-plan.index'), route('mindos.evaluation.index'), route('mindos.evaluation.create'),
            route('mindos.community.index'), route('mindos.services.index'), route('mindos.bookings.create', $service),
            route('mindos.bookings.index'), route('mindos.certification.index'), route('dashboard'),
        ] as $url) {
            $this->actingAs($user)->get($url)
                ->assertOk()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
    }

    public function test_client_cannot_reach_admin_or_practitioner_area(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get(route('mindos.admin.bookings.index'))->assertForbidden();
        $this->actingAs($user)->get(route('mindos.practitioner.index'))->assertForbidden();
    }

    public function test_full_client_journey(): void
    {
        $user = $this->user();

        // Foundation
        $module = FoundationModule::first();
        $this->actingAs($user)->post(route('mindos.foundation.complete', $module))->assertRedirect();
        $this->assertDatabaseHas('module_progress', ['user_id' => $user->id, 'foundation_module_id' => $module->id]);

        // Bug Detector — answer "5" everywhere → heavy → tatap muka
        $answers = BugQuestion::pluck('id')->mapWithKeys(fn ($id) => [$id => 5])->all();
        $this->actingAs($user)->post(route('mindos.bug-detector.store'), ['answers' => $answers])->assertRedirect();
        $assessment = BugAssessment::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('berat', $assessment->level);
        $this->assertSame('tatap_muka', $assessment->recommendation);
        $this->actingAs($user)->get(route('mindos.bug-detector.show', $assessment))->assertOk()->assertSee('Booking Sesi Tatap Muka');

        // Another user cannot see it
        $this->actingAs($this->user())->get(route('mindos.bug-detector.show', $assessment))->assertForbidden();

        // Journal
        $this->actingAs($user)->post(route('mindos.journal.store'), [
            'title' => 'Panik meeting', 'situation' => 'Rapat', 'thought' => 'Saya akan gagal',
            'feeling' => 'Cemas', 'intensity' => 8,
        ])->assertRedirect(route('mindos.journal.index'));
        $journal = Journal::where('user_id', $user->id)->firstOrFail();
        $this->actingAs($user)->get(route('mindos.journal.show', $journal))->assertOk();

        // 6R
        $this->actingAs($user)->post(route('mindos.six-r.store'), ['stage' => 'R1', 'note' => 'Sadar pola', 'intensity_before' => 8, 'intensity_after' => 5])->assertRedirect();
        $this->assertDatabaseHas('six_r_logs', ['user_id' => $user->id, 'stage' => 'R1', 'modality' => 'web']);

        // Audio listen
        $this->actingAs($user)->postJson(route('mindos.audio.listen', AudioTrack::first()))->assertOk();

        // Action plan: 30 days created in one go, today can be ticked
        $this->actingAs($user)->post(route('mindos.action-plan.store'), [
            'title' => 'Upgrade', 'goal' => 'Lebih tenang', 'start_date' => today()->toDateString(),
        ])->assertRedirect();
        $plan = ActionPlan::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(30, $plan->days()->count());
        $this->actingAs($user)->post(route('mindos.action-plan.day', $plan->days()->first()))->assertRedirect();
        $this->assertNotNull($plan->days()->first()->done_at);
        // Future day is refused
        $this->actingAs($user)->post(route('mindos.action-plan.day', $plan->days()->where('day_number', 10)->first()));
        $this->assertNull($plan->days()->where('day_number', 10)->first()->done_at);

        // Community
        $this->actingAs($user)->post(route('mindos.community.store'), ['category' => 'cerita', 'title' => 'Hari 1', 'body' => 'Mulai!'])->assertRedirect();
        $post = CommunityPost::firstOrFail();
        $this->actingAs($user)->post(route('mindos.community.comment', $post), ['body' => 'Semangat'])->assertRedirect();
        $this->actingAs($user)->post(route('mindos.community.like', $post));
        $post->refresh();
        $this->assertSame(1, $post->comments_count);
        $this->assertSame(1, $post->likes_count);
        $this->actingAs($user)->post(route('mindos.community.like', $post));
        $this->assertSame(0, $post->fresh()->likes_count);

        // Booking
        $service = MindService::where('modality', 'offline')->firstOrFail();
        $this->actingAs($user)->post(route('mindos.bookings.store', $service), [
            'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i'), 'complaint' => 'Trauma lama',
        ])->assertRedirect(route('mindos.bookings.index'));
        $booking = Booking::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('pending', $booking->status);
        $this->assertSame($service->price, $booking->amount);

        // Evaluation
        $this->actingAs($user)->post(route('mindos.evaluation.store'), [
            'score_before' => 8, 'score_after' => 4, 'next_step' => 'sertifikasi', 'reflection' => 'Jauh lebih tenang',
        ])->assertRedirect(route('mindos.certification.index'));
        $this->assertDatabaseHas('evaluations', ['user_id' => $user->id, 'result' => 'berhasil']);

        // Journey reflects progress
        $this->actingAs($user)->get(route('mindos.journey'))->assertOk()->assertSee('Booking Tatap Muka', false);
    }

    public function test_certification_ladder_and_admin_flow(): void
    {
        $user = $this->user();
        $practitioner = CertificationProgram::where('level', 'practitioner')->firstOrFail();
        $master = CertificationProgram::where('level', 'master')->firstOrFail();

        // Not an alumnus yet → refused
        $this->actingAs($user)->post(route('mindos.certification.apply', $practitioner), ['motivation' => str_repeat('Ingin membantu. ', 3)]);
        $this->assertDatabaseCount('certification_enrollments', 0);

        foreach (FoundationModule::all() as $m) {
            $this->actingAs($user)->post(route('mindos.foundation.complete', $m));
        }
        $this->actingAs($user)->post(route('mindos.certification.apply', $practitioner), ['motivation' => str_repeat('Ingin membantu. ', 3)]);
        $enrollment = CertificationEnrollment::firstOrFail();

        // Master requires a certified practitioner
        $this->actingAs($user)->post(route('mindos.certification.apply', $master), ['motivation' => str_repeat('Ingin membantu. ', 3)]);
        $this->assertDatabaseCount('certification_enrollments', 1);

        // Admin certifies → certificate number + Practitioner role
        $admin = $this->user('Superadmin');
        $this->actingAs($admin)->put(route('mindos.admin.enrollments.update', $enrollment), [
            'status' => 'certified', 'hours_done' => 24, 'clients_done' => 3,
        ])->assertRedirect();
        $enrollment->refresh();
        $this->assertNotNull($enrollment->certificate_number);
        $this->assertTrue($user->fresh()->hasRole('Practitioner'));

        // Admin assigns a booking; practitioner handles it
        $booking = Booking::create([
            'user_id' => $this->user()->id, 'mind_service_id' => MindService::first()->id,
            'scheduled_at' => now()->addDay(), 'complaint' => 'Cemas', 'amount' => 0, 'payment_status' => 'free',
        ]);
        $this->actingAs($admin)->put(route('mindos.admin.bookings.update', $booking), [
            'practitioner_id' => $user->id, 'status' => 'confirmed', 'payment_status' => 'paid',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i'),
        ])->assertRedirect();
        $this->assertSame($user->id, $booking->fresh()->practitioner_id);

        $this->actingAs($user->fresh())->get(route('mindos.practitioner.index'))->assertOk()->assertSee($booking->code);
        $this->actingAs($user->fresh())->put(route('mindos.practitioner.update', $booking), ['status' => 'completed'])->assertRedirect();
        $this->assertSame('completed', $booking->fresh()->status);

        // Admin pages + dashboard render
        foreach (['modules', 'audio', 'services', 'bugs'] as $type) {
            $this->actingAs($admin)->get(route('mindos.admin.content.index', $type))->assertOk();
            $this->actingAs($admin)->get(route('mindos.admin.content.create', $type))->assertOk();
        }
        $this->actingAs($admin)->get(route('mindos.admin.bookings.index'))->assertOk();
        $this->actingAs($admin)->get(route('mindos.admin.enrollments.index'))->assertOk();
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Distribusi Pola Pikiran Dominan');
    }

    public function test_admin_content_crud(): void
    {
        $admin = $this->user('Superadmin');

        $this->actingAs($admin)->post(route('mindos.admin.content.store', 'services'), [
            'name' => 'Sesi Baru', 'modality' => 'online', 'stage' => 'R4', 'description' => 'Desc',
            'price' => 1000, 'duration_minutes' => 60, 'capacity' => 1, 'sort_order' => 99, 'is_active' => '1',
        ])->assertRedirect(route('mindos.admin.content.index', 'services'));

        $service = MindService::where('name', 'Sesi Baru')->firstOrFail();
        $this->assertSame('sesi-baru', $service->slug);
        $this->actingAs($admin)->get(route('mindos.admin.content.edit', ['services', $service->id]))->assertOk();
        $this->actingAs($admin)->delete(route('mindos.admin.content.destroy', ['services', $service->id]))->assertRedirect();
        $this->assertModelMissing($service);
    }

    public function test_registration_creates_client(): void
    {
        $this->post('/admin/register', [
            'name' => 'Klien Baru', 'email' => 'baru@example.com',
            'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123',
        ])->assertRedirect(route('mindos.journey'));

        $user = User::where('email', 'baru@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('Klien'));
        $this->assertFalse($user->hasRole('Superadmin'));
        $this->assertSame('baru', $user->username);
    }

    public function test_account_seeder_creates_working_logins_and_never_overwrites(): void
    {
        $this->seed(AccountSeeder::class);

        $home = [
            'Superadmin' => route('dashboard'),
            'admin' => route('dashboard'),
            'Practitioner' => route('mindos.journey'),
            'Trainer' => route('mindos.journey'),
            'Klien' => route('mindos.journey'),
        ];

        foreach (AccountSeeder::accounts() as [$role, , $email]) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertTrue($user->hasRole($role), "{$email} should be {$role}");

            $this->post('/admin/login', ['email' => $email, 'password' => AccountSeeder::defaultPassword()])
                ->assertRedirect($home[$role]);
            $this->assertAuthenticatedAs($user);
            $this->post(route('logout'));
        }

        // A changed password survives a second seeding run.
        $admin = User::where('email', 'superadmin@mindos.mooda.id')->first();
        $admin->update(['password' => 'GantiSendiri123']);
        $this->seed(AccountSeeder::class);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('GantiSendiri123', $admin->fresh()->password));
        $this->assertSame(count(AccountSeeder::accounts()), User::where('email', 'like', '%@mindos.mooda.id')->count());
    }
}
