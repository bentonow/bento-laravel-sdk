<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class BentoSignatureExclusion
{
    /**
     * List of parameters required for Laravel's signed URLs
     *
     * @var array<string>
     */
    private const REQUIRED_PARAMETERS = [
        'expires',
        'signature',
    ];

    /**
     * Handle an incoming request.
     *
     * @return mixed
     *
     * @throws InvalidSignatureException
     */
    public function handle(Request $request, Closure $next)
    {
        // Get all current query parameters
        $queryParams = $request->query->all();

        // Filter to keep only required parameters
        $cleanedParams = array_filter(
            $queryParams,
            fn ($key) => in_array($key, self::REQUIRED_PARAMETERS, true),
            ARRAY_FILTER_USE_KEY
        );

        // Create a new request with only the required parameters for validation
        $validationRequest = Request::create(
            $request->url(),
            $request->method(),
            $cleanedParams
        );

        if (! $validationRequest->hasValidSignature()) {
            throw new InvalidSignatureException;
        }

        // Replace the query parameters in the original request with the cleaned ones
        $request->query->replace($cleanedParams);

        // Update the server's QUERY_STRING
        $request->server->set('QUERY_STRING', http_build_query($cleanedParams));

        return $next($request);
    }
}
