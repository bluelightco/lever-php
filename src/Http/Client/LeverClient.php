<?php

namespace Bluelightco\LeverPhp\Http\Client;

use Bluelightco\LeverPhp\Http\Middleware\LeverRateStore;
use Bluelightco\LeverPhp\Http\Middleware\QueryStringCleanerMiddleware;
use Bluelightco\LeverPhp\Http\Responses\ApiResponse;
use Exception;
use GrahamCampbell\GuzzleFactory\GuzzleFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;
use Spatie\GuzzleRateLimiterMiddleware\Store;

class LeverClient
{
    const BACKOFF_TIME = 1500;

    private $baseUrl;

    private $endpoint = '';

    private $options = [];

    public function __construct(
        private ?string $apiKey = null,
        private bool $checkUuid = true,
        private bool $checkEnums = true,
        private ?Client $client = null,
        ?Store $store = null
    ) {
        if (! $this->apiKey) {
            $this->apiKey = config('lever-php.api_key');
        }

        if (! $client) {
            $this->baseUrl = config('lever-php.base_url');

            $stack = HandlerStack::create();
            $stack->push(QueryStringCleanerMiddleware::buildQuery());
            $stack->push(RateLimiterMiddleware::perSecond(10, $store ?? new LeverRateStore()));

            $this->client = GuzzleFactory::make(
                [
                    'base_uri' => $this->baseUrl,
                    'headers' => [

                        'Accept' => 'application/json',
                    ],
                    'auth' => [$this->apiKey, ''],
                    'handler' => $stack,
                ],
                self::BACKOFF_TIME
            );
        }

        $this->options = [];

        $this->checkUuid = $checkUuid;
    }

    // API requests

    private function sendRequest($method, $endpoint, array $options = []): ResponseInterface
    {
        try {
            return $this->client->request($method, $endpoint, $options);
        } catch (ClientException $e) {
            $this->handleException($e, $method, $endpoint, $options);
        } catch (Exception $e) {
            $this->handleException($e, $method, $endpoint, $options);
        } finally {
            $this->reset();
        }
    }

    public function get(): ResponseInterface
    {
        return $this->sendRequest('GET', $this->endpoint, $this->options);
    }

    public function post(array $body = []): ResponseInterface
    {
        return $this->sendRequest('POST', $this->endpoint, $this->prepareOptions($body));
    }

    public function put(array $body = []): ResponseInterface
    {
        return $this->sendRequest('PUT', $this->endpoint, $this->prepareOptions($body));
    }

    public function create(array $body, string $method = 'post'): array
    {
        $response = ApiResponse::using($this->$method($body))->toArray();

        return $response;
    }

    public function update(array $body): array
    {
        $response = ApiResponse::using($this->post($body))->toArray();

        return $response;
    }

    public function putUpdate(array $body): array
    {
        $response = ApiResponse::using($this->put($body))->toArray();

        return $response;
    }

    public function fetch(): LazyCollection|array
    {
        $endpoint = $this->endpoint;
        $options = $this->options;

        $response = ApiResponse::using($this->get())->toArray();

        if (! array_key_exists('hasNext', $response)) {
            return $response['data'];
        }

        return LazyCollection::make(function () use ($response, $endpoint, $options) {
            do {
                foreach ($response['data'] as $item) {
                    yield $item;
                }

                $this->endpoint = $endpoint;
                $this->options = $options;
                unset($this->options['offset']);

                if (! empty($response['next'])) {
                    $this->options['query']['offset'] = json_decode(urldecode($response['next']));

                    $response = ApiResponse::using($this->get())->toArray();
                } else {
                    return;
                }
            } while (! empty($response['data']));

            $this->endpoint = $endpoint;
            $this->options = $options;
        });
    }

    private function addParameter(string $field, $value): self
    {
        $value = is_string($value) ? [$value] : $value;
        $this->options['query'][$field] = array_merge($this->options['query'][$field] ?? [], $value);

        return $this;
    }

    private function prepareOptions(array $body): array
    {
        if (isset($this->options['query'])) {
            $this->options['query'] = preg_replace('/%5B[0-9]%5D/', '',
                http_build_query($this->options['query'])
            );
        }

        if (isset($this->options['hasFiles'])) {
            $options = [];

            foreach ($body as $key => $item) {

                // TODO add support for files[] and automate filename and headers fields.
                if (in_array($key, ['file', 'files', 'resumeFile'])) {
                    $options[] = [
                        'name' => $key,
                        'contents' => $item['file'],
                        'filename' => $item['name'],
                        'headers' => ['Content-Type' => $item['type']],
                    ];

                    continue;
                }

                if (is_array($item)) {
                    foreach ($item as $subKey => $subItem) {
                        if (is_numeric($subKey)) {
                            $options[] = ['name' => $key.'[]', 'contents' => $subItem];
                        }

                        if (is_string($subKey)) {
                            $options[] = ['name' => "{$key}[{$subKey}]", 'contents' => $subItem];
                        }
                    }
                }

                if (is_string($item)) {
                    $options[] = ['name' => $key, 'contents' => $item];
                }
            }

            unset($this->options['hasFiles']);

            return array_merge(['multipart' => $options], $this->options);
        }

        return array_merge(['json' => $body], $this->options);
    }

    private function reset(): void
    {
        $this->endpoint = '';
        $this->options = [];
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function hasFiles()
    {
        $this->options['hasFiles'] = true;

        return $this;
    }

    // Principal endpoints

    public function users(?string $userId = null): self
    {
        $this->isValidUuid($userId);

        $this->endpoint = 'users'.($userId ? '/'.$userId : '');

        return $this;
    }

    public function opportunities(?string $opportunityId = null): self
    {
        $this->isValidUuid($opportunityId);

        $this->endpoint = 'opportunities'.($opportunityId ? '/'.$opportunityId : '');

        return $this;
    }

    public function postings(?string $postingId = null): self
    {
        $this->isValidUuid($postingId);

        $this->endpoint = 'postings'.($postingId ? '/'.$postingId : '');

        return $this;
    }

    // Inside endpoints

    public function resumes(?string $resumeId = null): self
    {
        $this->isValidUuid($resumeId);

        $this->endpoint .= '/resumes'.($resumeId ? '/'.$resumeId : '');

        return $this;
    }

    public function download(): StreamInterface
    {
        $this->endpoint .= '/download';

        return $this->get()->getBody();
    }

    public function offers(): self
    {
        $this->endpoint .= '/offers';

        return $this;
    }

    public function stages(): self
    {
        $this->endpoint .= '/stage';

        return $this;
    }

    public function apply(array $body = []): array
    {
        $this->endpoint .= '/apply';

        $application = ApiResponse::using($this->post($body))->toArray();

        return $application;
    }

    public function notes(?string $noteId = null): self
    {
        $this->isValidUuid($noteId);

        $this->endpoint .= '/notes'.($noteId ? '/'.$noteId : '');

        return $this;
    }

    public function archived(): self
    {
        $this->endpoint .= '/archived';

        return $this;
    }

    public function addTags(): self
    {
        $this->endpoint .= '/addTags';

        return $this;
    }

    // Parameters:

    public function expand(array|string $expandable): self
    {
        $this->checkExpandOptions($expandable);

        return $this->addParameter('expand', $expandable);
    }

    public function performAs(string $userId): self
    {
        $this->isValidUuid($userId);

        return $this->addParameter('perform_as', $userId);
    }

    public function include($includable): self
    {
        $this->checkIncludeOptions($includable);

        return $this->addParameter('include', $includable);
    }

    public function sendConfirmationEmail(): self
    {
        return $this->addParameter('send_confirmation_email', 'true');
    }

    public function team(array|string $team): self
    {
        return $this->addParameter('team', $team);
    }

    public function department(array|string $department): self
    {
        return $this->addParameter('department', $department);
    }

    public function parse(): self
    {
        return $this->addParameter('parse', 'true');
    }

    public function email($email): self
    {
        return $this->addParameter('email', $email);
    }

    public function stage($stageId): self
    {
        $this->isValidUuid($stageId);

        return $this->addParameter('stage_id', $stageId);
    }

    public function posting($postingId): self
    {
        $this->isValidUuid($postingId);

        return $this->addParameter('posting_id', $postingId);
    }

    // Helpers

    private function isValidUuid($uuid): bool
    {
        if (! $this->checkUuid || empty($uuid)) {
            return true;
        }

        $isUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;

        if (! $isUuid) {
            throw new \Exception("Invalid UUID: {$uuid}");
        }

        return true;
    }

    private function checkIncludeOptions(array|string $include): bool
    {
        if (! $this->checkEnums) {
            return true;
        }

        $includeOptions = [
            'content',
            'followers',
            'text',
            'groups',
            'fields',
        ];

        if (is_array($include)) {
            foreach ($include as $item) {
                if (! in_array($item, $includeOptions)) {
                    throw new \Exception("Invalid include option: {$item}");
                }
            }
        } else {
            if (! in_array($include, $includeOptions)) {
                throw new \Exception("Invalid include option: {$include}");
            }
        }

        return true;
    }

    private function checkExpandOptions(array|string $expand): bool
    {
        if (! $this->checkEnums) {
            return true;
        }

        $expandOptions = [
            // EEO
            'contact',
            'hiringManager',
            'posting',
            // Offers
            'creator',
            // Opportunities
            'applications',
            'stage',
            'owner',
            'followers',
            'sourcedBy',
            'contact',
            // Postings
            'user',
            'owner',
            'hiringManager',
            'followers',
        ];

        if (is_array($expand)) {
            foreach ($expand as $item) {
                if (! in_array($item, $expandOptions)) {
                    throw new \Exception("Invalid expand option: {$item}");
                }
            }
        } else {
            if (! in_array($expand, $expandOptions)) {
                throw new \Exception("Invalid expand option: {$expand}");
            }
        }

        return true;
    }

    private function handleException(Exception $e, string $method, string $endpoint, array $options = []): void
    {
        Log::error("HTTP $method error: ".$e->getMessage(), [
            'message' => $e->getMessage(),
            'package_context' => [
                'package' => 'Bluelightco\LeverPhp',
                'method' => $method,
                'endpoint' => $endpoint,
                'options' => json_encode($options),
                'response' => ($e instanceof ClientException ? ($e->getResponse() ? $e->getResponse()->getBody()->getContents() : null) : null),
            ],
            'exception' => $e,
        ]);

        throw new RuntimeException("Error executing HTTP $method. Please check the logs for more details.");
    }
}
