<?php

namespace Bluelightco\LeverPhp\Tests;

use Bluelightco\LeverPhp\Http\Client\LeverClient;
use Bluelightco\LeverPhp\Http\Middleware\QueryStringCleanerMiddleware;
use Bluelightco\LeverPhp\Providers\LeverServiceProvider;
use GrahamCampbell\GuzzleFactory\GuzzleFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var LeverClient
     */
    protected $lever;

    protected $mockHandler;

    protected $container;

    const BACKOFF_TEST = 100;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();

        $this->container = [];

        $mock = HandlerStack::create($this->mockHandler);

        $mock->push(QueryStringCleanerMiddleware::buildQuery());
        $mock->push(RateLimiterMiddleware::perSecond(1));

        $stack = GuzzleFactory::handler(backoff: self::BACKOFF_TEST, stack: $mock);

        $stack->push(Middleware::history($this->container));

        $guzzleClient = new Client([
            'base_uri' => '',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'handler' => $stack,
        ]);

        $this->lever = new LeverClient(apiKey: 'x', client: $guzzleClient);
    }
}
