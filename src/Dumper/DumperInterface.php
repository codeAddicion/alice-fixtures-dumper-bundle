<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Dumper;

interface DumperInterface
{
    public function dump(string $path, string $environment, bool $force = false): void;
}
