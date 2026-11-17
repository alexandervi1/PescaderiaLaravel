<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // El middleware 'admin' ya se encarga de la autorización real.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nombre'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'precio'            => 'required|numeric|min:0',
            'stock'             => 'required|numeric|min:0',
            'categoria_id'      => 'required|exists:categoria,categoria_id',
            'unidad_compra_id'  => 'required|exists:unidad_medida,unidad_id',
            'unidad_venta_id'   => 'required|exists:unidad_medida,unidad_id',
            'imagen'            => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ];
    }

    /**
     * Rechaza combinaciones de unidades sin factor de conversión definido.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $compra = $this->input('unidad_compra_id');
            $venta = $this->input('unidad_venta_id');

            if ($compra && $venta && \App\Models\Producto::factorConversion((int) $compra, (int) $venta) === null) {
                $validator->errors()->add(
                    'unidad_venta_id',
                    'La combinación de unidad de compra y unidad de venta no tiene conversión definida.'
                );
            }
        });
    }
}
