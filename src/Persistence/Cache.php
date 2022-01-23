<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient\Persistence;

use Edspc\OauthHttpClient\Data\Credentials;
use Psr\Cache\CacheItemPoolInterface;

class Cache implements CredentialsCacheInterface, RefreshTokenInterface
{
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function saveToCache(string $serviceMame, Credentials $credentials): void
    {
        $item = $this->cacheItemPool->getItem(static::getCacheKey($serviceMame, 'credentials'));

        $item->set($credentials);
        $item->expiresAt($credentials->getExpiresAt());

        $this->cacheItemPool->save($item);
    }

    public function loadFromCache(string $serviceMame): ?Credentials
    {
        $item = $this->cacheItemPool->getItem(static::getCacheKey($serviceMame, 'credentials'));

        if ($item->isHit()) {
            return $item->get();
        }

        return null;
    }

    public function persistToken(string $serviceMame, string $refreshToken): void
    {
        $item = $this->cacheItemPool->getItem(static::getCacheKey($serviceMame, 'token'));
        $item->set($refreshToken);
        $this->cacheItemPool->save($item);
    }

    public function loadToken(string $serviceMame): ?string
    {
        $item = $this->cacheItemPool->getItem(static::getCacheKey($serviceMame, 'token'));

        if ($item->isHit()) {
            return $item->get();
        }

        return null;
    }

    public static function getCacheKey(string $serviceMame, string $type): string
    {
        return \sprintf('edspc.oauth_http_client.cache.%s.%s', $type, $serviceMame);
    }
}
