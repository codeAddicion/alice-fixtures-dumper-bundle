<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('alice_fixtures_dumper');

        $rootNode = $treeBuilder->getRootNode();

        $defaultRootDirsValue = [
            '%kernel.project_dir%',
        ];

        $rootNode
            ->children()
            ->arrayNode('fixtures_path')
            ->info('Path(s) to which to look for fixtures relative to the root directory paths.')
            ->beforeNormalization()->castToArray()->end()
            ->defaultValue(['fixtures'])
            ->scalarPrototype()->end()
            ->end()
            ->arrayNode('root_dirs')
            ->info('List of root directories into which to look for the fixtures.')
            ->defaultValue($defaultRootDirsValue)
            ->scalarPrototype()->end()
            ->end()
            ->scalarNode('fixtures_generated_path')
            ->info('Directory where fixtures will be generated.')
            ->defaultValue('%kernel.project_dir%/fixtures/generated')
            ->end()
            ->end();

        return $treeBuilder;
    }

}
