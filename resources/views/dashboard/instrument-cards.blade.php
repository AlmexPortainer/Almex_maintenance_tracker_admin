@php
    /** Metadatos de presentación */
    $typeIcons = [
        'calibracion'   => 'bs.speedometer2',
        'verificacion'  => 'bs.clipboard-check',
        'mantenimiento' => 'bs.tools',
    ];

    $bandMeta = [
        'vencido' => ['label' => 'Vencido',   'class' => 'text-bg-danger'],
        'b0_15'   => ['label' => '≤ 15 días', 'class' => 'text-bg-warning'],
        'b16_30'  => ['label' => '16–30 días','class' => 'text-bg-info'],
        'b31_90'  => ['label' => '31–90 días','class' => 'text-bg-success'],
    ];

    $summary = [
        ['label' => 'Total instrumentos', 'value' => $total,          'icon' => 'bs.dropbox',              'class' => 'text-body'],
        ['label' => 'Operativos',         'value' => $operativos,     'icon' => 'bs.check-circle',         'class' => 'text-success'],
        ['label' => 'No operativos',      'value' => $no_operativos,  'icon' => 'bs.x-octagon',            'class' => 'text-secondary'],
        ['label' => 'Críticos',           'value' => $criticos,       'icon' => 'bs.exclamation-diamond',  'class' => 'text-warning'],
        ['label' => 'Total vencidos',     'value' => $vencidos_total, 'icon' => 'bs.calendar-x',           'class' => 'text-danger',
            'link'  => route('platform.instruments.list', ['due' => 'any', 'band' => 'vencido'])],
    ];
@endphp

{{-- ===================================================== --}}
{{-- SECCIÓN 1 · TODOS LOS INSTRUMENTOS                    --}}
{{-- ===================================================== --}}
<h5 class="mb-3">Todos los instrumentos</h5>

<div class="row g-3 mb-4">
    @foreach ($summary as $card)
        <div class="col-6 col-md-4 col-xl">
            @php $isLink = ! empty($card['link']); @endphp
            <{{ $isLink ? 'a' : 'div' }}
                @if ($isLink) href="{{ $card['link'] }}" @endif
                class="card h-100 shadow-sm text-decoration-none {{ $isLink ? '' : '' }}">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 fs-2 {{ $card['class'] }}">
                        <x-orchid-icon :path="$card['icon']"/>
                    </div>
                    <div>
                        <div class="h3 mb-0 {{ $card['class'] }}">{{ $card['value'] }}</div>
                        <div class="text-muted small">{{ $card['label'] }}</div>
                    </div>
                </div>
            </{{ $isLink ? 'a' : 'div' }}>
        </div>
    @endforeach
</div>

{{-- ===================================================== --}}
{{-- SECCIÓN 2 · ESFUERZO POR TIPO × HORIZONTE            --}}
{{-- ===================================================== --}}
<h5 class="mb-1">Próximos a vencer por tipo</h5>
<p class="text-muted small mb-3">Bandas exclusivas: cada instrumento cuenta en una sola columna. Clic en un número para ver el listado.</p>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-center">
            <thead>
                <tr>
                    <th class="text-start">Tipo</th>
                    @foreach ($bands as $band)
                        <th><span class="badge {{ $bandMeta[$band]['class'] }}">{{ $bandMeta[$band]['label'] }}</span></th>
                    @endforeach
                    <th>Requieren</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($types as $key => $label)
                    <tr>
                        <td class="text-start fw-bold">
                            <x-orchid-icon :path="$typeIcons[$key]" class="me-1"/> {{ $label }}
                        </td>
                        @foreach ($bands as $band)
                            @php $count = $matrix[$key][$band]; @endphp
                            <td>
                                @if ($count > 0)
                                    <a href="{{ route('platform.instruments.list', ['due' => $key, 'band' => $band]) }}"
                                       class="fw-bold text-decoration-none {{ $band === 'vencido' ? 'text-danger' : '' }}">
                                        {{ $count }}
                                    </a>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-muted">{{ $requieren[$key] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>