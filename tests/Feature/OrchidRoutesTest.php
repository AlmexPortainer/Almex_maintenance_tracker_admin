<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrchidRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Creamos un usuario con permisos de acceso a la plataforma
        $this->admin = User::factory()->create([
            'permissions' => [
                'platform.index' => true,
                'platform.systems.users' => true,
                'platform.systems.roles' => true,
            ],
        ]);
    }

    /** @test */
    public function can_access_instrument_list()
    {
        $response = $this->actingAs($this->admin)->get(route('platform.instruments.list'));
        $response->assertStatus(200);
    }

    /** @test */
    public function can_access_global_events()
    {
        $response = $this->actingAs($this->admin)->get(route('platform.instrument_events.global'));
        $response->assertStatus(200);
    }

    /** @test */
    public function can_access_all_reporter_combinations()
    {
        $tipos = ['calibracion', 'verificacion', 'mantenimiento'];
        $areas = ['produccion', 'calidad', 'servicios'];

        foreach ($tipos as $tipo) {
            foreach ($areas as $area) {
                $response = $this->actingAs($this->admin)->get(route('platform.reporter', [
                    'tipo' => $tipo,
                    'area' => $area,
                ]));

                $response->assertStatus(200)
                    ->assertSee(ucfirst($tipo))
                    ->assertSee(ucfirst($area));
            }
        }
    }
}
