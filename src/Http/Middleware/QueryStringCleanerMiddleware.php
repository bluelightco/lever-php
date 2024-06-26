<?php

namespace Bluelightco\LeverPhp\Http\Middleware;

use Psr\Http\Message\RequestInterface;

class QueryStringCleanerMiddleware
{
    public static function buildQuery()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $query = $request->getUri()->getQuery();
                $request = $request->withUri(
                    $request->getUri()->withQuery(preg_replace('/%5B[0-9]%5D/', '', $query)),
                    true
                );

                return $handler($request, $options);
            };
        };
    }
}
