<?php
declare(strict_types=1);

namespace Edspc\OauthHttpClient\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('edspc_oauth_http_client');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('default_auth')->defaultNull()->end()
                ->arrayNode('auth')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('token_url')->defaultValue('')->end()
                            ->scalarNode('client_id')->defaultValue('')->end()
                            ->scalarNode('client_secret')->defaultValue('')->end()
                            ->scalarNode('cache_service')->defaultValue('edspc.oauth_http_client.cache')->end()
                            ->scalarNode('refresh_token_persistence')->defaultValue('edspc.oauth_http_client.cache')->end()
                        ->end()
                    ->end()
                ->end() // auth
                ->arrayNode('http_services')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('base_uri')->isRequired()->end()
                            ->scalarNode('auth')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end() // services
            ->end()
        ;

        return $treeBuilder;
    }
}
