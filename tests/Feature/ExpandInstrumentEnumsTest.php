<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('accepts expanded form values and free-text variable', function () {
    DB::table('instruments')->insert([
        'code' => 'TEST-ENUM-1',
        'form' => 'Reactivo',
        'variable_unit_of_measure' => 'Tamaño de particula',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $row = DB::table('instruments')->where('code', 'TEST-ENUM-1')->first();
    expect($row->form)->toBe('Reactivo')
        ->and($row->variable_unit_of_measure)->toBe('Tamaño de particula');
});
