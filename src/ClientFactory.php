<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient;

use Symfony\Component\HttpClient\HttpClientTrait;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientFactory
{
    /**
     * @throws \Edspc\OauthHttpClient\Exception\NoAccessTokenException
     */
    public static function create(HttpClientInterface $client, Processor $processor, string $baseUrl): HttpClient
    {
        $options = new HttpOptions();
        $options->setAuthBearer($processor->getAccessToken());
        $options->setBaseUri($baseUrl);

        $regexp = self::getRegexp($baseUrl);

        return new HttpClient($client, [$regexp => $options->toArray()], $regexp);
    }

    private static function getRegexp(string $baseUri)
    {
        $urlResolver = new class() {
            use HttpClientTrait {
                parseUrl as public;
                resolveUrl as public;
            }
        };

        return \preg_quote(
            \implode('', $urlResolver::resolveUrl($urlResolver::parseUrl('.'), $urlResolver::parseUrl($baseUri)))
        );
    }
}
