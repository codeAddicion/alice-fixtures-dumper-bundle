<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Loader;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use ITakSoydet\AliceFixturesDumperBundle\Persister\PersisterInterface;
use Nelmio\Alice\IsAServiceTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DumperLoader implements LoaderInterface
{
    use IsAServiceTrait;

    public function __construct(
        private readonly LoaderInterface $loader,
        private readonly PersisterInterface $persister,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }


    /**
     * {@inheritdoc}
     */
    public function load(
        array     $fixturesFiles,
        array     $parameters = [],
        array     $objects = [],
        PurgeMode $purgeMode = null
    ): array {
        $objects = $this->loader->load($fixturesFiles, $parameters, $objects, $purgeMode);

        $this->persister->persist($objects, $parameters, $purgeMode);


        return $objects;
    }
}
