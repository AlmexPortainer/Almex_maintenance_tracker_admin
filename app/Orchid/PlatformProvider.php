<?php

declare(strict_types=1);

namespace App\Orchid;

use App\Models\Instrument;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Instrumentos')
                ->icon('dropbox')
                ->title('Instrumentos y Actualización de Fechas')
                ->list([

                    Menu::make('Altas, baja, cambio INSTRUMENTOS')
                        ->icon('bs.tools')
                        ->route('platform.instruments.create'),

                    Menu::make('Actualizar fecha CALIBRACIÓN')
                        ->icon('bs.speedometer')
                        ->route('platform.instrument_events.create', ['event_type' => 'CALIBRACION']),

                    Menu::make('Actualizar fecha VALIDACIÓN')
                        ->icon('bs.check-square')
                        ->route('platform.instrument_events.create', [
                            'event_type' => 'VALIDACION',
                        ]),

                    Menu::make('Actualizar fecha MANTENIMIENTO')
                        ->icon('bs.nut-fill')
                        ->route('platform.instrument_events.create', [
                            'event_type' => 'MANTENIMIENTO',
                        ]),
                ]),

            Menu::make('Listado')
                ->icon('bs.list-nested')
                ->title('Listado Instrumentos')
                ->list([
                    Menu::make('Instrumentos CRÍTICOS')
                        ->icon('bs.exclamation-diamond')
                        ->route('platform.instruments.list', [
                            'types_of_criticality' => 'CRITICO',
                        ])
                        ->badge(fn () => Instrument::where('types_of_criticality', 'CRITICO')->count()),

                    Menu::make('Instrumentos NO CRÍTICOS')
                        ->icon('bs.slash-circle')
                        ->route('platform.instrument_events.global', [
                            'types_of_criticality' => 'NO_CRITICO',
                        ])
                        ->badge(fn () => Instrument::where('types_of_criticality', 'NO_CRITICO')->count()),
                ]),

            Menu::make('Tickets de Instrumentos')
                ->icon('bs.printer')
                ->route('platform.instruments.tickets'),

            Menu::make('Reportería CALIBRACION')
                ->icon('bs.clipboard-data')
                ->title('Reportería')
                ->list([
                    Menu::make('Produccion')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'calibracion',
                            'area' => 'produccion',
                        ]),

                    Menu::make('Calidad')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'calibracion',
                            'area' => 'calidad',
                        ]),

                    Menu::make('Servicios')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'calibracion',
                            'area' => 'servicios',
                        ]),
                ]),

            // ===== VERIFICACION =====
            Menu::make('VERIFICACIÓN')
                ->icon('bs.check2-square')
                ->list([
                    Menu::make('Producción')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'verificacion',
                            'area' => 'produccion',
                        ]),

                    Menu::make('Calidad')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'verificacion',
                            'area' => 'calidad',
                        ]),

                    Menu::make('Servicios')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'verificacion',
                            'area' => 'servicios',
                        ]),
                ]),

            // ===== MANTENIMIENTO =====
            Menu::make('MANTENIMIENTO')
                ->icon('bs.tools')
                ->list([
                    Menu::make('Producción')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'mantenimiento',
                            'area' => 'produccion',
                        ]),

                    Menu::make('Calidad')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'mantenimiento',
                            'area' => 'calidad',
                        ]),

                    Menu::make('Servicios')
                        ->icon('bs.book')
                        ->route('platform.reporter', [
                            'tipo' => 'mantenimiento',
                            'area' => 'servicios',
                        ]),
                ]),

            Menu::make('Get Started')
                ->icon('bs.book')
                ->title('Navigation')
                ->route(config('platform.index')),

            Menu::make('Sample Screen')
                ->icon('bs.collection')
                ->route('platform.example')
                ->badge(fn () => 6),

            Menu::make('Form Elements')
                ->icon('bs.card-list')
                ->route('platform.example.fields')
                ->active('*/examples/form/*'),

            Menu::make('Layouts Overview')
                ->icon('bs.window-sidebar')
                ->route('platform.example.layouts'),

            Menu::make('Grid System')
                ->icon('bs.columns-gap')
                ->route('platform.example.grid'),

            Menu::make('Charts')
                ->icon('bs.bar-chart')
                ->route('platform.example.charts'),

            Menu::make('Cards')
                ->icon('bs.card-text')
                ->route('platform.example.cards')
                ->divider(),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
                ->icon('bs.box-arrow-up-right')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),

            Menu::make('Changelog')
                ->icon('bs.box-arrow-up-right')
                ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
                ->target('_blank')
                ->badge(fn () => Dashboard::version(), Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
