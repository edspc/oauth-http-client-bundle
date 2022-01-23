<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient\Exception;

class NoAccessTokenException extends \Exception
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(
            'Cannot load access token.',
            null,
            $previous
        );
    }
}
