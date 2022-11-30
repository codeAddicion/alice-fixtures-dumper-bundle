<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Dumper;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Hautelook\AliceBundle\FixtureLocatorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Dumper implements DumperInterface
{
    use IsAServiceTrait;

    public function __construct(
        private readonly FixtureLocatorInterface $fixtureLocator,
        private readonly LoaderInterface $loader,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }


    public function dump(string $path, string $environment, bool $force = false): void
    {
        $fixtureFiles = $this->fixtureLocator->locateFiles([], $environment);

        $this->logger->info('fixtures found', ['files' => $fixtureFiles]);

        $purgeMode = $force ? PurgeMode::createDeleteMode() : PurgeMode::createNoPurgeMode();
        $fixtures = $this->loader->load($fixtureFiles, ['fixtures_generated_path' => $path], [], $purgeMode);

        $this->logger->info('fixtures loaded', ['count' => count($fixtures)]);
    }

}
