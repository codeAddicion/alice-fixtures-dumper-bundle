<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Tests\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use Fidry\AliceDataFixtures\Loader\FileResolverLoader;
use Hautelook\AliceBundle\HautelookAliceBundle;
use Hautelook\AliceBundle\Locator\EnvDirectoryLocator;
use Hautelook\AliceBundle\Locator\EnvironmentlessFilesLocator;
use ITakSoydet\AliceFixturesDumperBundle\Command\DumpCommand;
use ITakSoydet\AliceFixturesDumperBundle\Dumper\Dumper;
use ITakSoydet\AliceFixturesDumperBundle\Hydrator\Property\IdPropertyHydrator;
use ITakSoydet\AliceFixturesDumperBundle\Loader\DumperLoader;
use ITakSoydet\AliceFixturesDumperBundle\Loader\ReplaceIdGeneratorLoader;
use ITakSoydet\AliceFixturesDumperBundle\Loader\RestartSequencesPersisterLoader;
use ITakSoydet\AliceFixturesDumperBundle\Persister\SaveToYamlFilePersister;
use ITakSoydet\AliceFixturesDumperBundle\Tests\Functional\AppKernel;
use ITakSoydet\AliceFixturesDumperBundle\Tests\Functional\ConfigurableKernel;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \ITakSoydet\AliceFixturesDumperBundle\AliceFixturesDumperBundle
 * @covers \ITakSoydet\AliceFixturesDumperBundle\DependencyInjection\Configuration
 * @covers \ITakSoydet\AliceFixturesDumperBundle\DependencyInjection\AliceFixturesDumperExtension
 */
class AliceFixturesDumperBundleTest extends KernelTestCase
{

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    public function testCannotBootWithoutDependencyBundles(): void
    {
        $kernel = new ConfigurableKernel('ConfigurableKernel0', true);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            sprintf(
                'To register "%s", you also need: "%s", "%s", "%s".',
                'ITakSoydet\AliceFixturesDumperBundle\AliceFixturesDumperBundle',
                'Doctrine\Bundle\DoctrineBundle\DoctrineBundle',
                'Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle',
                'Hautelook\AliceBundle\HautelookAliceBundle',
            )
        );
        $kernel->boot();
    }

    public function testCannotBootWithoutDoctrineBundle(): void
    {
        $kernel = new ConfigurableKernel('ConfigurableKernel0', true);
        $kernel->addBundle(new FidryAliceDataFixturesBundle());
        $kernel->addBundle(new HautelookAliceBundle());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            sprintf(
                'To register "%s", you also need: "%s"',
                'ITakSoydet\AliceFixturesDumperBundle\AliceFixturesDumperBundle',
                'Doctrine\Bundle\DoctrineBundle\DoctrineBundle',
            )
        );
        $kernel->boot();
    }

    public function testCannotBootWithoutFidryAliceDataFixturesBundle(): void
    {
        $kernel = new ConfigurableKernel('ConfigurableKernel0', true);
        $kernel->addBundle(new DoctrineBundle());
        $kernel->addBundle(new HautelookAliceBundle());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            sprintf(
                'To register "%s", you also need: "%s"',
                'ITakSoydet\AliceFixturesDumperBundle\AliceFixturesDumperBundle',
                'Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle',
            )
        );
        $kernel->boot();
    }

    public function testCannotBootWithoutHautelookAliceBundle(): void
    {
        $kernel = new ConfigurableKernel('ConfigurableKernel0', true);
        $kernel->addBundle(new DoctrineBundle());
        $kernel->addBundle(new FidryAliceDataFixturesBundle());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            sprintf(
                'To register "%s", you also need: "%s"',
                'ITakSoydet\AliceFixturesDumperBundle\AliceFixturesDumperBundle',
                'Hautelook\AliceBundle\HautelookAliceBundle',
            )
        );
        $kernel->boot();
    }

    public function testServiceRegistration(): void
    {
        self::bootKernel(['environment' => 'public', 'debug' => true]);
        $container = self::getContainer();
        self::assertInstanceOf(
            DumpCommand::class,
            $container->get('alice_fixtures_dumper.command.dump')
        );

        self::assertInstanceOf(
            Dumper::class,
            $container->get('alice_fixtures_dumper.dumper')
        );

        self::assertInstanceOf(
            EnvironmentlessFilesLocator::class,
            $container->get('alice_fixtures_dumper.locator.environmentless')
        );

        self::assertInstanceOf(
            EnvDirectoryLocator::class,
            $container->get('alice_fixtures_dumper.locator.env_directory')
        );

        self::assertInstanceOf(
            FileResolverLoader::class,
            $container->get('alice_fixtures_dumper.data_fixtures.loader')
        );

        self::assertInstanceOf(
            DumperLoader::class,
            $container->get('alice_fixtures_dumper.data_fixtures.dumper_loader')
        );

        self::assertInstanceOf(
            ReplaceIdGeneratorLoader::class,
            $container->get('alice_fixtures_dumper.loader.replace_id_generator')
        );

        self::assertInstanceOf(
            RestartSequencesPersisterLoader::class,
            $container->get('alice_fixtures_dumper.doctrine.restart_sequences_persister_loader')
        );

        self::assertInstanceOf(
            Yaml::class,
            $container->get('alice_fixtures_dumper.yaml_decoder')
        );

        self::assertInstanceOf(
            SaveToYamlFilePersister::class,
            $container->get('alice_fixtures_dumper.persister.persister_to_yaml_file')
        );

        self::assertInstanceOf(
            IdPropertyHydrator::class,
            $container->get('alice_fixtures_dumper.hydrator.property.id_property_hydrator')
        );
    }
}