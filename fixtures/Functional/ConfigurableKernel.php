<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Tests\Functional;

use ITakSoydet\AliceFixturesDumperBundle\AliceFixturesDumperBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

class ConfigurableKernel extends Kernel
{
    /**
     * @var list<Bundle>
     */
    private array $addedBundles = [];

    public function registerBundles(): iterable
    {
        return array_merge(
            [
                new FrameworkBundle(),
                new AliceFixturesDumperBundle(),
            ],
            $this->addedBundles
        );
    }

    public function addBundle(Bundle $bundle): self
    {
        $this->addedBundles[] = $bundle;

        return $this;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yaml');
    }
}