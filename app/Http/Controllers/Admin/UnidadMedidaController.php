<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadMedidaController extends Controller
{
    public function index()
    {
        $unidades = UnidadMedida::withCount(['productosCompra', 'productosVenta'])
            ->orderBy('unidad_id')
            ->get();

        return view('admin.unidades.index', compact('unidades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:unidad_medida,nombre',
            'abreviatura' => 'required|string|max:5|unique:unidad_medida,abreviatura',
        ]);

        UnidadMedida::create($data);

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad de medida creada correctamente.');
    }

    public function update(Request $request, UnidadMedida $unidad)
    {
        $data = $request->validate([
            'nombre' => [
                'required', 'string', 'max:50',
                Rule::unique('unidad_medida', 'nombre')->ignore($unidad->unidad_id, 'unidad_id'),
            ],
            'abreviatura' => [
                'required', 'string', 'max:5',
                Rule::unique('unidad_medida', 'abreviatura')->ignore($unidad->unidad_id, 'unidad_id'),
            ],
        ]);

        $unidad->update($data);

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad de medida actualizada correctamente.');
    }

    public function destroy(UnidadMedida $unidad)
    {
        $enUso = $unidad->productosCompra()->exists() || $unidad->productosVenta()->exists();

        if ($enUso) {
            return back()->with('error', 'No se puede eliminar una unidad que usan productos. Cambia primero esos productos.');
        }

        $unidad->delete();

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad de medida eliminada.');
    }
}
