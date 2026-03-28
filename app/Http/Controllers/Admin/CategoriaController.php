<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Mostrar listado de categorías.
     */
    public function index()
    {
        $categorias = Categoria::withCount('productos')->get();
        return view('admin.categorias.index', compact('categorias'));
    }

    /**
     * Almacenar una nueva categoría.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categoria,nombre'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con este nombre.'
        ]);

        Categoria::create(['nombre' => $request->nombre]);

        return redirect()->route('admin.categorias.index')
                         ->with('success', 'Categoría registrada exitosamente.');
    }

    /**
     * Actualizar una categoría existente.
     */
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:categoria,nombre,' . $categoria->categoria_id . ',categoria_id'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe otra categoría con este nombre.'
        ]);

        $categoria->update(['nombre' => $request->nombre]);

        return redirect()->route('admin.categorias.index')
                         ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Eliminar una categoría de la base de datos.
     */
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        
        // Al borrar la categoría, la base de datos pondrá en null el 'categoria_id' de los productos que le pertenecen
        $categoria->delete();

        return redirect()->route('admin.categorias.index')
                         ->with('success', 'Categoría eliminada con éxito. Los productos asociados ahora figuran "Sin Categoría".');
    }
}
