<?php

declare(strict_types=1);

use App\Orchid\Screens\Calibration\CalibrationEditScreen;
use App\Orchid\Screens\Calibration\CalibrationGlobalListScreen;
use App\Orchid\Screens\Calibration\CalibrationListScreen;
use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\InstrumentEvents\InstrumentEventEditScreen;
use App\Orchid\Screens\InstrumentEvents\InstrumentEventListScreen;
use App\Orchid\Screens\InstrumentEvents\InstrumentEventShowScreen;
use App\Orchid\Screens\Instruments\InstrumentEditScreen;
use App\Orchid\Screens\Instruments\InstrumentListScreen;
use App\Orchid\Screens\Instruments\InstrumentShowScreen;
// === Instrumentos ===
use App\Orchid\Screens\Items\CatalogItemEditScreen;
use App\Orchid\Screens\Items\CatalogItemListScreen;
// === Eventos de Instrumento (Calibración / Validación / Mantenimiento) ===
use App\Orchid\Screens\Items\CatalogItemShowScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Reporter\ReporterScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
// === Instrumentos ===
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
// === Eventos de Instrumento (Calibración / Validación / Mantenimiento) ===
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');

// Route::screen('idea', Idea::class, 'platform.screens.idea');

// Listado de ítems (ya lo tienes)
Route::screen('catalog-items', CatalogItemListScreen::class)
    ->name('platform.catalog_items');

Route::screen('catalog-items/create', CatalogItemEditScreen::class)
    ->name('platform.catalog_items.create');

Route::screen('catalog-items/{catalogItem}', CatalogItemEditScreen::class)
    ->name('platform.catalog_items.edit');

Route::screen('catalog-items/{catalogItem}/view', CatalogItemShowScreen::class)
    ->name('platform.catalog_items.view');

// === Calibrations ===
Route::screen('calibrations', CalibrationGlobalListScreen::class)
    ->name('platform.calibrations');

// === Calibrations nested ===
Route::screen('catalog-items/{catalogItem}/calibrations', CalibrationListScreen::class)
    ->name('platform.catalog_items.calibrations');

Route::screen('catalog-items/{catalogItem}/calibrations/create', CalibrationEditScreen::class)
    ->name('platform.catalog_items.calibrations.create');

Route::screen('catalog-items/{catalogItem}/calibrations/{calibration}', CalibrationEditScreen::class)
    ->name('platform.catalog_items.calibrations.edit');

// -----------------------------------------------------
// 📦 Catálogo de Instrumentos
// -----------------------------------------------------

Route::screen('instruments', InstrumentListScreen::class)
    ->name('platform.instruments.list');

Route::screen('instruments/create', InstrumentEditScreen::class)
    ->name('platform.instruments.create');

Route::screen('instruments/{instrument}', InstrumentEditScreen::class)
    ->name('platform.instruments.edit');

Route::screen('instruments/{instrument}/view', InstrumentShowScreen::class)
    ->name('platform.instruments.view');

// Instrumentos
Route::screen('instruments', InstrumentListScreen::class) // Cambia al nombre real de tu Screen
    ->name('platform.instruments.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.main')
        ->push(__('Instrumentos'), route('platform.instruments.list')));

Route::screen('instruments/{instrument}/events/create', InstrumentEventEditScreen::class)
    ->name('platform.instruments.events.create');

// Reportería Dinámica (tipo y area)
Route::screen('reporter/{tipo}/{area}', ReporterScreen::class)
    ->name('platform.reporter')
    ->breadcrumbs(fn (Trail $trail, $tipo, $area) => $trail
        ->parent('platform.main')
        ->push(ucfirst((string) $tipo).' - '.ucfirst((string) $area)));

// -----------------------------------------------------
// ⚙️ Eventos de Instrumento (Global y Anidados)
// -----------------------------------------------------

// 🌍 Listado global de todos los eventos (calibraciones, validaciones, mantenimientos)
Route::screen('instrument-events', InstrumentEventListScreen::class)
    ->name('platform.instrument_events.global');

Route::screen('instrument-events/create', InstrumentEventEditScreen::class)
    ->name('platform.instrument_events.create');

// ✏️ Editar un evento existente
Route::screen('instruments/{instrument}/events/{instrumentEvent}', InstrumentEventEditScreen::class)
    ->name('platform.instruments.events.edit');

Route::screen('instrument-events/{instrumentEvent}/view', InstrumentEventShowScreen::class)
    ->name('platform.instrument_events.view');

// 📑 Listado de eventos de un instrumento específico
Route::screen('instruments/{instrument}/events', InstrumentEventListScreen::class)
    ->name('platform.instruments.events');

// ➕ Crear evento para un instrumento
Route::screen('instruments/{instrument}/events/create', InstrumentEventEditScreen::class)
    ->name('platform.instruments.events.create');

/*
Route::screen('instrument-events-global', InstrumentEventGlobalScreen::class)
    ->name('platform.instrument_events.global')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.main')
        ->push(__('Actualización Global'), route('platform.instrument_events.global')));
*/

// Reportería Dinámica (tipo y area)
Route::screen('reporter/{tipo}/{area}', \App\Orchid\Screens\Reporter\ReporterScreen::class)
    ->name('platform.reporter')
    ->breadcrumbs(fn (Trail $trail, $tipo, $area) => $trail
        ->parent('platform.index')
        ->push(ucfirst((string) $tipo).' - '.ucfirst((string) $area)));
