<?php

namespace App\Http\Controllers;

use App\Models\PaymentConcept;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentConceptController extends Controller
{
    /** ================= LISTAR ================= */
    public function index()
    {
        $payment_concepts = PaymentConcept::orderBy('nombre')->get();
        return view('payment_concepts.index', compact('payment_concepts'));
    }

    /** ================= FORMULARIO CREAR ================= */
    public function create()
    {
        return view('payment_concepts.create');
    }

    /** ================= GUARDAR ================= */
    public function store(Request $request)
    {
        $request->merge([
            'nombre' => trim(mb_strtoupper($request->nombre)),
        ]);

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_concepts', 'nombre'),
            ],
            'descripcion' => 'nullable|string|max:255',
            'es_mensual' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ], [
            'nombre.unique' => 'Este concepto de pago ya existe.',
        ]);

        $validated['es_mensual'] = $request->has('es_mensual');
        $validated['activo'] = $request->has('activo');

        PaymentConcept::create($validated);

        return redirect()
            ->route('payment_concepts.index')
            ->with('success', 'Concepto de pago creado correctamente.');
    }

    /** ================= FORMULARIO EDITAR ================= */
    public function edit(PaymentConcept $payment_concept)
    {
        return view('payment_concepts.edit', compact('payment_concept'));
    }

    /** ================= ACTUALIZAR ================= */
    public function update(Request $request, PaymentConcept $payment_concept)
    {
        $request->merge([
            'nombre' => trim(mb_strtoupper($request->nombre)),
        ]);

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_concepts', 'nombre')
                    ->ignore($payment_concept->id),
            ],
            'descripcion' => 'nullable|string|max:255',
            'es_mensual' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ], [
            'nombre.unique' => 'Este concepto de pago ya existe.',
        ]);

        $validated['es_mensual'] = $request->has('es_mensual');
        $validated['activo'] = $request->has('activo');

        $payment_concept->update($validated);

        return redirect()
            ->route('payment_concepts.index')
            ->with('success', 'Concepto de pago actualizado correctamente.');
    }

    /** ================= ELIMINAR ================= */
    public function destroy(PaymentConcept $payment_concept)
    {
        $payment_concept->delete();

        return redirect()
            ->route('payment_concepts.index')
            ->with('success', 'Concepto de pago eliminado correctamente.');
    }
}
