<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Persister;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;

interface PersisterInterface
{
    public function persist(array $objects, array $parameters, PurgeMode $purgeMode): void;
}
