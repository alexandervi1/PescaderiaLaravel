<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Se lee de config('app.trusted_proxies') — no de env() directo, porque con
     * `config:cache` las llamadas a env() fuera de config/ devuelven null.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    public function __construct()
    {
        $proxies = config('app.trusted_proxies');

        if (is_string($proxies) && trim($proxies) === '*') {
            $this->proxies = '*';
        } elseif (is_string($proxies) && trim($proxies) !== '') {
            $this->proxies = array_map('trim', explode(',', $proxies));
        } else {
            $this->proxies = null;
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
