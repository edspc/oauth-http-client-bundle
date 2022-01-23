<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient;

use Symfony\Component\HttpClient\ScopingHttpClient;

class HttpClient extends ScopingHttpClient implements OAuthHttpClientInterface
{

}
