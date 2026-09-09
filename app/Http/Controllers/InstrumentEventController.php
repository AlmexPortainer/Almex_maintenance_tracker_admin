<?php

namespace App\Http\Controllers;

use App\Models\Instrument;
use App\Models\InstrumentEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstrumentEventController extends Controller
{
    /**
     * 📋 Listar eventos por instrumento o todos
     */
    public function index(Request $request)
    {
        $query = InstrumentEvent::query()->with('instrument');

        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('instrument_id')) {
            $query->where('instrument_id', $request->instrument_id);
        }

        $events = $query->latest('fecha_evento')->get();

        return response()->json([
            'meta' => ['total' => $events->count()],
            'data' => $events,
        ]);
    }

    /**
     * ➕ Registrar nuevo evento
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'instrument_id' => 'required|exists:instruments,id',
            'event_type' => 'required|in:CALIBRACION,VALIDACION,MANTENIMIENTO',
            'fecha_evento' => 'required|date',
            'responsable' => 'nullable|string|max:255',
            'reporte' => 'nullable|string|max:255',
            'resultados' => 'nullable|string',
            'adecuado' => 'boolean',
            'fecha_proxima' => 'nullable|date',
            'fecha_maxima' => 'nullable|date',
        ]);

        $event = InstrumentEvent::create($validated);

        return response()->json([
            'message' => 'Evento registrado correctamente.',
            'data' => $event,
        ], Response::HTTP_CREATED);
    }

    /**
     * 🔍 Ver detalles de un evento
     */
    public function show(InstrumentEvent $instrumentEvent)
    {
        $instrumentEvent->load('instrument');

        return response()->json($instrumentEvent);
    }

    /**
     * ✏️ Actualizar un evento
     */
    public function update(Request $request, InstrumentEvent $instrumentEvent)
    {
        $validated = $request->validate([
            'event_type' => 'in:CALIBRACION,VALIDACION,MANTENIMIENTO',
            'fecha_evento' => 'nullable|date',
            'responsable' => 'nullable|string|max:255',
            'reporte' => 'nullable|string|max:255',
            'resultados' => 'nullable|string',
            'adecuado' => 'boolean',
            'fecha_proxima' => 'nullable|date',
            'fecha_maxima' => 'nullable|date',
        ]);

        $instrumentEvent->update($validated);

        return response()->json([
            'message' => 'Evento actualizado correctamente.',
            'data' => $instrumentEvent,
        ]);
    }

    /**
     * 🗑️ Eliminar evento
     */
    public function destroy(InstrumentEvent $instrumentEvent)
    {
        $instrumentEvent->delete();

        return response()->json(['message' => 'Evento eliminado correctamente.']);
    }
}
