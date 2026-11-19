<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SaludController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['estado' => 'error'], 503);
        }

        return response()->json(['estado' => 'ok']);
    }
}
