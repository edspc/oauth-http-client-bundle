<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient\Data;

class Credentials
{
    private ?string $accessToken;
    private ?string $refreshToken;
    private ?string $tokenType;
    private ?\DateTimeImmutable $expiresAt;

    public function __construct(
        ?string $accessToken = null,
        ?string $refreshToken = null,
        ?string $tokenType = null,
        ?\DateTimeImmutable $expiresIn = null
    ) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->tokenType = $tokenType;
        $this->expiresAt = $expiresIn;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getTokenType(): ?string
    {
        return $this->tokenType;
    }

    public function setTokenType(string $tokenType): void
    {
        $this->tokenType = $tokenType;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
}
