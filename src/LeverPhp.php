<?php

namespace ViaWork\LeverPhp;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\LazyCollection;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GrahamCampbell\GuzzleFactory\GuzzleFactory;

class LeverPhp
{
    /** @var string */
    private $leverKey;

    /** @var string */
    private $endpoint = '';

    /** @var string */
    private $method = 'get';

    /** @var GuzzleClient */
    private $client;

    /** @var array */
    private $options = ['query' => []];

    /**
     * LeverPhp constructor.
     * @param string $leverKey
     * @param GuzzleClient|null $client
     */
    public function __construct(string $leverKey, GuzzleClient $client = null)
    {
        $this->leverKey = $leverKey;

        // TODO pass RateLimiterMiddleware, check if compatible with exponential backoff
        $this->client = $client ?? GuzzleFactory::make(
                [
                    'base_uri' => 'https://api.lever.co/v1/',
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'auth' => [$leverKey, '']
                ]
            );
    }

    private function post($body = ''): ResponseInterface
    {
        try {
            $response = $this->client->post($this->endpoint, [
                'json' => $body,
            ]);
        } catch (ClientException $exception) {
            throw $exception;
        }

        return $response;
    }

    private function get(): ResponseInterface
    {
        try {
            $response = $this->client->get($this->endpoint, $this->options);
        } catch (ClientException $exception) {
            throw $exception;
        }

        return $response;
    }


    public function expand(string $expandable)
    {
        return $this->addQueryString('expand', $expandable);
    }

    public function include(string $includable)
    {
        return $this->addQueryString('include', $includable);
    }

    public function addQueryString(string $field, string $value)
    {
        if (!empty($field) && !empty($value)) {
            $this->options['query'][$field] = $value;
        }

        return $this;
    }

    public function leverKey(): string
    {
        return $this->leverKey;
    }

    public function client(): GuzzleClient
    {
        return $this->client;
    }

    public function opportunities()
    {
        $this->endpoint = 'opportunities';
        $this->method = 'get';

        return $this;
    }

    public function fetch(): LazyCollection
    {
        $response = $this->responseToArray($this->{$this->method}());

        if (!array_key_exists('hasNext', $response)) {
            return $response['data'];
        }


        return LazyCollection::make(function () use ($response) {

            do {
                foreach ($response['data'] as $item) {
                    yield $item;
                }

                $nextToken = $response['next'] ?? '';

                $response = $this->responseToArray(
                    $this->addQueryString('offset', $nextToken)->{$this->method}()
                );

            } while ((boolean)$response['hasNext']);

        });
    }

    private function responseToArray(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }


}
