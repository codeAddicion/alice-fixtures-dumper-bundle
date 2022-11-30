<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Loader;

use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterAwareInterface;
use Fidry\AliceDataFixtures\Persistence\PersisterInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Nelmio\Alice\IsAServiceTrait;

class RestartSequencesPersisterLoader implements LoaderInterface, PersisterAwareInterface
{
    use IsAServiceTrait;

    public function __construct(
        private readonly LoaderInterface|PersisterAwareInterface $decorator,
        private readonly EntityManagerInterface $entityManager
    ) {

    }


    public function load(
        array     $fixturesFiles,
        array     $parameters = [],
        array     $objects = [],
        PurgeMode $purgeMode = null
    ): array {
        $objects = $this->decorator->load($fixturesFiles, $parameters, $objects, $purgeMode);
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            if (null === $metadata->sequenceGeneratorDefinition) {
                continue;
            }
            if (count($metadata->identifier) !== 1) {
                continue;
            }
            // получаем текущий максимальный id
            $dql = sprintf(
                "select max(t.%s) as id from %s t",
                $metadata->identifier[0],
                $metadata->rootEntityName
            );
            $id = (int)$this->entityManager->createQuery($dql)->getSingleResult()['id'];

            // устанавливаем на 1 больше
            $sql = sprintf(
                'alter sequence %s restart start with %s',
                $metadata->sequenceGeneratorDefinition['sequenceName'],
                ++$id
            );
            $this->entityManager->getConnection()->executeStatement($sql);
        }

        return $objects;
    }

    public function withPersister(PersisterInterface $persister): self
    {
        $decorator = $this->decorator->withPersister($persister);
        return new self($decorator, $this->entityManager);
    }
}
