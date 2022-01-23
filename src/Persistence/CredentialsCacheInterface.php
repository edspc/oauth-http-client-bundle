<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient\Persistence;

use Edspc\OauthHttpClient\Data\Credentials;

interface CredentialsCacheInterface
{
    public function saveToCache(string $serviceMame, Credentials $credentials): void;
    public function loadFromCache(string $serviceMame): ?Credentials;
}
