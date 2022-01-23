<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient\Persistence;

interface RefreshTokenInterface
{
    public function persistToken(string $serviceMame, string $refreshToken): void;
    public function loadToken(string $serviceMame): ?string;
}
