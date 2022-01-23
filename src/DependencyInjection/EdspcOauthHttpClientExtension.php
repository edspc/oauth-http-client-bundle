<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient\DependencyInjection;

use Edspc\OauthHttpClient\ClientFactory;
use Edspc\OauthHttpClient\Command\Auth;
use Edspc\OauthHttpClient\Data\Config;
use Edspc\OauthHttpClient\HttpClient;
use Edspc\OauthHttpClient\OAuthHttpClientInterface;
use Edspc\OauthHttpClient\Persistence\Cache;
use Edspc\OauthHttpClient\Processor;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class EdspcOauthHttpClientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['default_auth'])) {
            return;
        }

        $this->registerServices($container, $config);
    }

    private function registerServices(ContainerBuilder $container, array $config): void
    {
        $container->register('edspc.oauth_http_client.cache', Cache::class)
                  ->setAutoconfigured(true)
                  ->setAutowired(true)
                  ->setPublic(false)
        ;

        foreach ($config['auth'] as $name => $auth) {
            if ('default' === $name) {
                throw new \RuntimeException('edspc.oauth_http_client: name `default` for auth is reserved.');
            }
            $container->setDefinition(
                'edspc.oauth_http_client.processor.'.$name,
                $this->getAuthProcessor($name, $auth)
            );
        }

        $container->setAlias(
            'edspc.oauth_http_client.processor.default',
            'edspc.oauth_http_client.processor.'.$config['default_auth']
        );

        foreach ($config['http_services'] as $name => $service) {
            $authProcessor = $service['auth'] ?? 'default';

            $container->register($name, HttpClient::class)
                      ->setFactory([ClientFactory::class, 'create'])
                      ->setArguments(
                          [
                              new Reference('http_client'),
                              new Reference('edspc.oauth_http_client.processor.'.$authProcessor),
                              $service['base_uri'],
                          ]
                      )
            ;

            $container->registerAliasForArgument($name, OAuthHttpClientInterface::class);
        }

        $container->register('edspc.oauth_http_client.auth_command', Auth::class)
                  ->setArgument('$locator', new ServiceLocatorArgument(new TaggedIteratorArgument('edspc.oauth_http_client.processor', 'key')))
                  ->addTag('console.command')
        ;
    }

    private function getAuthProcessor(string $name, array $config): Definition
    {
        $processor = new Definition(Processor::class, [
            '$httpClient' => new Reference('http_client'),
            '$config' => $this->getAuthConfig($config),
            '$serviceName' => $name,
            '$credentialsCache' => new Reference($config['cache_service']),
            '$refreshToken' => new Reference($config['refresh_token_persistence']),
        ]);

        $processor->setPublic(false);
        $processor->addTag('edspc.oauth_http_client.processor', ['key' => $name]);

        return $processor;
    }

    private function getAuthConfig(array $config): Definition
    {
        $configDef = new Definition(Config::class, [
            '$tokenUrl' => $config['token_url'],
            '$clientId' => $config['client_id'],
            '$clientSecret' => $config['client_secret'],
        ]);

        $configDef->setPublic(false);

        return $configDef;
    }

    public function getAlias()
    {
        return 'edspc_oauth_http_client';
    }
}
