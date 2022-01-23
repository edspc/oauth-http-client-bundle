<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient\Data;

class Config
{
    private string $tokenUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct(string $tokenUrl, string $clientId, string $clientSecret)
    {
        $this->tokenUrl = $tokenUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getTokenUrl(): string
    {
        return $this->tokenUrl;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }
}
