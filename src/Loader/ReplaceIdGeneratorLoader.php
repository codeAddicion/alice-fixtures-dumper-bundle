<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Loader;

use Doctrine\Persistence\ObjectManager;
use Fidry\AliceDataFixtures\Bridge\Doctrine\IdGenerator;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Nelmio\Alice\IsAServiceTrait;

class ReplaceIdGeneratorLoader implements LoaderInterface
{
    use IsAServiceTrait;

    public function __construct(
        private readonly LoaderInterface $decorator,
        private readonly ObjectManager $objectManager
    ) {
    }

    public function load(
        array     $fixturesFiles,
        array     $parameters = [],
        array     $objects = [],
        PurgeMode $purgeMode = null
    ): array {
        $objects = $this->decorator->load($fixturesFiles, $parameters, $objects, $purgeMode);
        foreach ($objects as $object) {
            $this->replaceIdGenerator(get_class($object));
        }
        return $objects;
    }

    private function replaceIdGenerator(string $className)
    {
        if (!$this->objectManager->getMetadataFactory()->hasMetadataFor($className)) {
            return;
        }

        $metadata = $this->objectManager->getClassMetadata($className);
        if (null === $metadata->idGenerator) {
            return;
        }

        $newMetadata = clone $metadata;
        if ($newMetadata->idGenerator instanceof IdGenerator) {
            return;
        }
        $newMetadata->setIdGeneratorType(IdGenerator::GENERATOR_TYPE_ALICE);
        $newMetadata->setIdGenerator(new IdGenerator($metadata->idGenerator));

        $this->objectManager->getMetadataFactory()->setMetadataFor($metadata->getName(), $newMetadata);
    }
}
