<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\MtaSyncQueue;
use App\Models\Pemuda;
use App\Models\User;
use App\Services\MtaSyncService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MtaSyncQueueFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::where('role_id', 1)->first()
            ?? User::where('username', 'superadmin')->first()
            ?? User::create([
                'name'     => 'Super Admin Test',
                'username' => 'superadmin_test',
                'email'    => 'superadmin@test.com',
                'password' => bcrypt('password'),
                'role_id'  => 1,
            ]);
    }

    public function test_mta_sync_index_page_displays_rate_and_rest_ui(): void
    {
        $this->actingAs($this->superadmin);

        $response = $this->get(route('admin.mta-sync.index'));

        $response->assertStatus(200);
        $response->assertSee('Laju: 40 Data / Menit');
        $response->assertSee('Jeda: Istirahat 10 Detik / 40 Data');
        $response->assertSee('Antrean Sinkronisasi &amp; Verifikasi Massal MTA Pusat', false);
        $response->assertSee('id="queueProgressBar"', false);
        $response->assertSee('id="queueRestBadge"', false);
        $response->assertSee('id="queueRestCountdown"', false);
        $response->assertSee('id="liveActivityLog"', false);
    }

    public function test_queue_init_creates_queue_and_returns_accurate_summary(): void
    {
        $this->actingAs($this->superadmin);

        $cabang = Cabang::first();
        $this->assertNotNull($cabang);

        // Buat pemuda dummy untuk cabang ini
        Pemuda::create([
            'registration_number' => 'PMD-TEST-' . uniqid(),
            'cabang_id'           => $cabang->id,
            'name'                => 'Pemuda Queue Test 1',
            'gender'              => 'L',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        $response = $this->postJson(route('admin.mta-sync.queue-init'), [
            'cabang_id'    => $cabang->id,
            'only_pending' => true,
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertGreaterThanOrEqual(1, $data['total']);
        $this->assertEquals(40, $data['summary']['rate_per_minute']);
        $this->assertEquals(40, $data['summary']['rest_every_items']);
        $this->assertEquals(10, $data['summary']['rest_seconds']);
        $this->assertEquals(1.5, $data['summary']['delay_seconds']);
        $this->assertArrayHasKey('estimated_formatted', $data['summary']);
        $this->assertArrayHasKey('estimated_time', $data);

        // Pastikan tabel mta_sync_queue terisi
        $this->assertGreaterThanOrEqual(1, MtaSyncQueue::where('status', 'pending')->count());
    }

    public function test_queue_process_item_processes_single_record(): void
    {
        $this->actingAs($this->superadmin);

        $cabang = Cabang::first();
        $this->assertNotNull($cabang);

        $pemuda = Pemuda::create([
            'registration_number' => 'PMD-TEST-' . uniqid(),
            'cabang_id'           => $cabang->id,
            'name'                => 'Pemuda Queue Test Process',
            'gender'              => 'L',
            'status_verifikasi'   => 'pending',
            'status_data'         => 'active',
        ]);

        MtaSyncQueue::truncate();
        MtaSyncQueue::create([
            'pemuda_id'  => $pemuda->id,
            'cabang_id'  => $cabang->id,
            'status'     => 'pending',
            'result'     => 'pending',
            'created_by' => $this->superadmin->id,
        ]);

        // Mock API call to MTA Pusat
        Http::fake([
            'api.mta.or.id/api/v1/warga*' => Http::response([
                'status'  => true,
                'data'    => [],
                'message' => 'Tidak ada data cocok.',
            ], 200),
        ]);

        $response = $this->postJson(route('admin.mta-sync.queue-process-item'));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertFalse($data['finished']);
        $this->assertArrayHasKey('item', $data);
        $this->assertEquals($pemuda->name, $data['item']['name']);
        $this->assertEquals($cabang->name, $data['item']['cabang_name']);
        $this->assertEquals('completed', $data['item']['status']);

        // Check summary
        $this->assertEquals(1, $data['summary']['processed']);
        $this->assertEquals(0, $data['summary']['remaining']);
    }

    public function test_queue_status_returns_live_summary_and_recent_processed(): void
    {
        $this->actingAs($this->superadmin);

        $response = $this->getJson(route('admin.mta-sync.queue-status'));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('summary', $data);
        $this->assertArrayHasKey('recent_processed', $data);
        $this->assertEquals(40, $data['summary']['rate_per_minute']);
        $this->assertEquals(40, $data['summary']['rest_every_items']);
        $this->assertEquals(10, $data['summary']['rest_seconds']);
    }

    public function test_queue_cancel_cancels_all_pending_items(): void
    {
        $this->actingAs($this->superadmin);

        $cabang = Cabang::first();
        $pemuda = Pemuda::first();

        MtaSyncQueue::truncate();
        MtaSyncQueue::create([
            'pemuda_id'  => $pemuda->id,
            'cabang_id'  => $cabang->id,
            'status'     => 'pending',
            'result'     => 'pending',
            'created_by' => $this->superadmin->id,
        ]);

        $response = $this->postJson(route('admin.mta-sync.queue-cancel'));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertEquals(0, MtaSyncQueue::where('status', 'pending')->count());

        $failedItem = MtaSyncQueue::first();
        $this->assertEquals('failed', $failedItem->status);
        $this->assertStringContainsString('Dibatalkan', $failedItem->message);
    }

    public function test_queue_summary_calculates_rest_duration_correctly(): void
    {
        $cabang = Cabang::first();
        $pemuda = Pemuda::first();

        MtaSyncQueue::truncate();

        // Buat 85 queue items (seharusnya ada 2 kali istirahat 10 detik: di item 40 dan item 80)
        $items = [];
        $now = now();
        for ($i = 0; $i < 85; $i++) {
            $items[] = [
                'pemuda_id'  => $pemuda->id,
                'cabang_id'  => $cabang->id,
                'status'     => 'pending',
                'result'     => 'pending',
                'created_by' => $this->superadmin->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        MtaSyncQueue::insert($items);

        $summary = MtaSyncQueue::getQueueSummary();

        // 85 * 1.5s = 127.5 => ceil: 128s
        // 85 items => floor(85 / 40) = 2 rest batches * 10s = 20s
        // Total expected seconds = 128 + 20 = 148 seconds
        $this->assertEquals(85, $summary['total']);
        $this->assertEquals(85, $summary['remaining']);
        $this->assertEquals(148, $summary['estimated_seconds']);
        $this->assertEquals('2 mnt 28 dtk', $summary['estimated_formatted']);
    }
}
