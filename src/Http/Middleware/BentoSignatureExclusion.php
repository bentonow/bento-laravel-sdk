<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class BentoSignatureExclusion
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     *
     * @throws InvalidSignatureException
     */
    public function handle(Request $request, Closure $next)
    {
        $excludedParameters = [
            'fbclid',
            'utm_campaign',
            'utm_content',
            'utm_medium',
            'utm_source',
            'utm_term',
            'bento_uuid',
        ];

        // Clone the request query parameters
        $queryParams = $request->query->all();

        // Remove excluded parameters from the cloned query
        foreach ($excludedParameters as $param) {
            unset($queryParams[$param]);
        }

        // Create a new request for validation
        $validationRequest = Request::create(
            $request->url(),
            $request->method(),
            $queryParams
        );

        if (! $validationRequest->hasValidSignature()) {
            throw new InvalidSignatureException;
        }

        return $next($request);
    }
}
