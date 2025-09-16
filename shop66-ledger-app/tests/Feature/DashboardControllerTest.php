<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\TaxRegion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_metrics(): void
    {
        $user = User::factory()->create();
        $taxRegion = TaxRegion::factory()->create();
        $store = Store::factory()->create(['tax_region_id' => $taxRegion->id]);
        
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/stores/{$store->id}/dashboard");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'metrics' => [
                'transactions' => ['current', 'previous', 'change_percent'],
                'total_amount' => ['current', 'previous', 'change_percent'],
                'documents_processed' => ['current', 'previous', 'change_percent'],
                'active_vendors'
            ],
            'charts' => [
                'transaction_trend',
                'category_breakdown',
                'top_vendors'
            ],
            'recent_activity'
        ]);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $taxRegion = TaxRegion::factory()->create();
        $store = Store::factory()->create(['tax_region_id' => $taxRegion->id]);

        $response = $this->getJson("/api/stores/{$store->id}/dashboard");

        $response->assertStatus(401);
    }
}
