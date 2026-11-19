<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Índices para las consultas más frecuentes.
     *
     * Se comprueba antes de crear: MySQL ya crea índices para las FK y el
     * unique de carrito(usuario_id, producto_id) cubre las búsquedas por
     * usuario_id, así que esos casos se omiten solos.
     */
    private const INDICES = [
        ['pedido', ['usuario_id', 'fecha_pedido'], 'pedido_usuario_fecha_index'],
        ['pedido', ['estado'], 'pedido_estado_index'],
        ['carrito', ['usuario_id'], 'carrito_usuario_index'],
        ['producto', ['categoria_id'], 'producto_categoria_index'],
    ];

    public function up()
    {
        foreach (self::INDICES as [$tabla, $columnas, $nombre]) {
            if (! $this->cubierto($tabla, $columnas)) {
                Schema::table($tabla, function (Blueprint $table) use ($columnas, $nombre) {
                    $table->index($columnas, $nombre);
                });
            }
        }
    }

    public function down()
    {
        foreach (array_reverse(self::INDICES) as [$tabla, $columnas, $nombre]) {
            if ($this->existe($tabla, $nombre)) {
                Schema::table($tabla, function (Blueprint $table) use ($nombre) {
                    $table->dropIndex($nombre);
                });
            }
        }
    }

    /**
     * ¿Existe ya un índice cuyas columnas empiecen por las pedidas?
     *
     * @param array<int, string> $columnas
     */
    private function cubierto(string $tabla, array $columnas): bool
    {
        foreach ($this->indices($tabla) as $indice => $columnasIndice) {
            if (array_slice($columnasIndice, 0, count($columnas)) === $columnas) {
                return true;
            }
        }

        return false;
    }

    private function existe(string $tabla, string $nombre): bool
    {
        return array_key_exists($nombre, $this->indices($tabla));
    }

    /**
     * @return array<string, array<int, string>> nombre del índice => columnas
     */
    private function indices(string $tabla): array
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $resultado = [];

            foreach ($connection->select('PRAGMA index_list(' . $connection->getQueryGrammar()->wrapTable($tabla) . ')') as $indice) {
                $columnas = array_map(
                    fn ($columna) => $columna->name,
                    $connection->select('PRAGMA index_info(' . $connection->getQueryGrammar()->wrap($indice->name) . ')')
                );
                $resultado[$indice->name] = $columnas;
            }

            return $resultado;
        }

        if ($driver === 'mysql') {
            $filas = $connection->select('SHOW INDEX FROM ' . $connection->getQueryGrammar()->wrapTable($tabla));
            $resultado = [];

            foreach ($filas as $fila) {
                $resultado[$fila->Key_name][(int) $fila->Seq_in_index - 1] = $fila->Column_name;
            }

            foreach ($resultado as $nombre => $columnas) {
                ksort($columnas);
                $resultado[$nombre] = array_values($columnas);
            }

            return $resultado;
        }

        return [];
    }
};
