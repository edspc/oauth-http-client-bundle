<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient;

use Edspc\OauthHttpClient\Data;
use Edspc\OauthHttpClient\Exception\AuthException;
use Edspc\OauthHttpClient\Exception\NoAccessTokenException;
use Edspc\OauthHttpClient\Persistence;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Processor
{
    private HttpClientInterface $httpClient;
    private Data\Config $config;
    private string $serviceName;

    private ?Persistence\CredentialsCacheInterface $credentialsCache;
    private ?Persistence\RefreshTokenInterface $refreshToken;

    public function __construct(
        HttpClientInterface $httpClient,
        Data\Config $config,
        string $serviceName,
        ?Persistence\CredentialsCacheInterface $credentialsCache = null,
        ?Persistence\RefreshTokenInterface $refreshToken = null
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->serviceName = $serviceName;
        $this->credentialsCache = $credentialsCache;
        $this->refreshToken = $refreshToken;
    }

    /**
     * @throws \Edspc\OauthHttpClient\Exception\NoAccessTokenException
     */
    public function getAccessToken(): string
    {
        $credentials = null;

        if ($this->credentialsCache) {
            $credentials = $this->credentialsCache->loadFromCache($this->serviceName);

            if ($credentials && $this->isValid($credentials)) {
                return $credentials->getAccessToken();
            }
        }

        if (!($credentials && $credentials->getRefreshToken()) && $this->refreshToken) {
            $refreshToken = $this->refreshToken->loadToken($this->serviceName);

            if ($refreshToken) {
                $credentials = new Data\Credentials(null, $refreshToken);
            }
        }

        if ($credentials && $credentials->getRefreshToken()) {
            $this->refreshAccessToken($credentials);

            return $credentials->getAccessToken();
        }

        throw new NoAccessTokenException();
    }

    public function refreshAccessToken(Data\Credentials $credentials): void
    {
        $response = $this->processResponse(
            $this->httpClient->request(
                'POST',
                $this->config->getTokenUrl(),
                [
                    'body' => [
                        'grant_type' => 'refresh_token',
                        'client_id' => $this->config->getClientId(),
                        'client_secret' => $this->config->getClientSecret(),
                        'refresh_token' => $credentials->getRefreshToken(),
                    ],
                ]
            )
        );

        $credentials->setAccessToken($response['access_token']);
        $credentials->setExpiresAt($this->getExpireDate($response['expires_in']));

        if ($this->credentialsCache) {
            $this->credentialsCache->saveToCache($this->serviceName, $credentials);
        }
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Edspc\OauthHttpClient\Exception\AuthException
     */
    public function authByGrantToken(string $grantToken): Data\Credentials
    {
        $response = $this->processResponse(
            $this->httpClient->request(
                'POST',
                $this->config->getTokenUrl(),
                [
                    'body' => [
                        'grant_type' => 'authorization_code',
                        'client_id' => $this->config->getClientId(),
                        'client_secret' => $this->config->getClientSecret(),
                        'code' => $grantToken,
                    ],
                ]
            )
        );

        $credentials = new Data\Credentials(
            $response['access_token'],
            $response['refresh_token'],
            $response['token_type'],
            $this->getExpireDate((int) $response['expires_in'])
        );

        if ($this->credentialsCache) {
            $this->credentialsCache->saveToCache($this->serviceName, $credentials);
        }

        if ($this->refreshToken) {
            $this->refreshToken->persistToken($this->serviceName, $credentials->getRefreshToken());
        }

        return $credentials;
    }

    private function isValid(Data\Credentials $credentials): bool
    {
        return $credentials->getAccessToken() && $credentials->getExpiresAt() > new \DateTimeImmutable();
    }

    private function getExpireDate(int $seconds): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('U', (string) (\time()+$seconds));
    }

    /**
     * @throws AuthException
     */
    private function processResponse(ResponseInterface $response): array
    {
        try {
            $data = $response->toArray();

            if (isset($data['error'])) {
                throw new AuthException('Auth API Response: '.$data['error']);
            }

            return $data;
        } catch (\Throwable $exception) {
            throw new AuthException('Error while process auth: '.$exception->getMessage());
        }
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }
}
