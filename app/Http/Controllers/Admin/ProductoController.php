<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['categoria', 'unidadCompra', 'unidadVenta'])->get();
        return view('admin.productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::all();
        $unidades = UnidadMedida::all();
        return view('admin.productos.create', compact('categorias', 'unidades'));
    }

    public function store(\App\Http\Requests\Admin\StoreProductoRequest $request)
    {

        $data = $request->except('imagen');

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = uniqid('prod_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/productos'), $filename);
            $data['imagen_url'] = 'img/productos/' . $filename;
        }

        Producto::create($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        $unidades = UnidadMedida::all();
        return view('admin.productos.edit', compact('producto', 'categorias', 'unidades'));
    }

    public function update(\App\Http\Requests\Admin\UpdateProductoRequest $request, Producto $producto)
    {

        $data = $request->except('imagen');

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen_url && file_exists(public_path($producto->imagen_url))) {
                unlink(public_path($producto->imagen_url));
            }
            $file = $request->file('imagen');
            $filename = uniqid('prod_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/productos'), $filename);
            $data['imagen_url'] = 'img/productos/' . $filename;
        }

        $producto->update($data);

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->imagen_url && file_exists(public_path($producto->imagen_url))) {
            unlink(public_path($producto->imagen_url));
        }

        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
