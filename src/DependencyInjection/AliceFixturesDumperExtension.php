<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use Hautelook\AliceBundle\HautelookAliceBundle;
use ITakSoydet\AliceFixturesDumperBundle\AliceFixturesDumperBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AliceFixturesDumperExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $missingBundles = [
            DoctrineBundle::class => true,
            FidryAliceDataFixturesBundle::class => true,
            HautelookAliceBundle::class => true,
        ];
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            unset($missingBundles[$bundle]);
            if (!$missingBundles) {
                break;
            }
        }

        if ($missingBundles) {
            throw new LogicException(
                sprintf(
                    'To register "%s", you also need: "%s".',
                    AliceFixturesDumperBundle::class,
                    implode('", "', array_keys($missingBundles))
                )
            );
        }
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configurables = [
            'fixtures_path',
            'root_dirs',
            'fixtures_generated_path',
        ];

        foreach ($configurables as $key) {
            $container->setParameter('alice_fixtures_dumper.' . $key, $config[$key]);
        }
    }
}
