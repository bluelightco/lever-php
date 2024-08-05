<?php

namespace Bluelightco\LeverPhp\Facades;

use Illuminate\Support\Facades\Facade;
use Bluelightco\LeverPhp\Http\Client\LeverClient;

/**
 * @method static \Psr\Http\Message\ResponseInterface get()
 * @method static \Psr\Http\Message\ResponseInterface post(array $body = [])
 * @method static \Psr\Http\Message\ResponseInterface put(array $body = [])
 * @method static array create(array $body, string $method = 'post')
 * @method static \Psr\Http\Message\ResponseInterface update(array $body)
 * @method static \Psr\Http\Message\ResponseInterface putUpdate(array $body)
 * @method static \Illuminate\Support\LazyCollection|array fetch()
 * @method static \GuzzleHttp\Client getClient()
 * @method static bool hasFiles()
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient users(string $userId = null)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient opportunities(string $opportunityId = null)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient postings(string $postingId = null)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient resumes(string $resumeId = null)
 * @method static \Psr\Http\Message\StreamInterface download()
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient offers()
 * @method static \Bluelightco\LeverPhp\Http.Client\LeverClient stages()
 * @method static array apply(array $body = [])
 * @method static \Bluelightco\LeverPhp\Http.Client\LeverClient notes(string $noteId = null)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient archived()
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient addTags()
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient expand(array|string $expandable)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient performAs(string $userId)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient include($includable)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient sendConfirmationEmail()
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient team(array|string $team)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient department(array|string $department)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient parse()
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient email($email)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient stage($stageId)
 * @method static \Bluelightco\LeverPhp\Http\Client\LeverClient posting($postingId)
 *
 * @see \Bluelightco\LeverPhp\Http\Client\LeverClient
 */
class Lever extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return LeverClient::class;
    }
}
