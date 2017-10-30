<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Helpers\AuthHelper;
use App\Helpers\ResponseHelper;

class Authenticate
{
    protected $authHelper;
    protected $responseHelper;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(AuthHelper $auth, ResponseHelper $responseHelper)
    {
        $this->authHelper = $auth;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        if (!$this->authHelper->verifyToken($request->header('auth-token'))) {
            return $this->responseHelper->unauthorized();
        }
        return $next($request);
    }
}
