<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Command;

use ITakSoydet\AliceFixturesDumperBundle\Dumper\DumperInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application as FrameworkBundleConsoleApplication;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'alice:fixtures:dump', description: 'Dump alice fixtures with faker to static alice fixture.')]
class DumpCommand extends Command
{
    public function __construct(
        private readonly DumperInterface $dumper,
        string  $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Replace all fixtures'
            )
            ->addOption(
                'fixtures-generated-path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Directory where fixtures will be generate'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $environment = $input->getOption('env');
        /** @var FrameworkBundleConsoleApplication $application */
        $application = $this->getApplication();
        $force = $input->getOption('force');
        $path = $input->getOption('fixtures-generated-path');
        $path = $path ?? $application
                ->getKernel()
                ->getContainer()
                ->getParameter('alice_fixtures_dumper.fixtures_generated_path');
        $this->dumper->dump($path, $environment, $force);
        return 0;
    }
}
