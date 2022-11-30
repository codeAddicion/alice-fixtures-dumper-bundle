<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Tests\DependencyInjection;

use ITakSoydet\AliceFixturesDumperBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers Configuration
 */
class ConfigurationTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'fixtures_path' => ['fixtures'],
            'root_dirs' => ['%kernel.project_dir%'],
            'fixtures_generated_path' => '%kernel.project_dir%/fixtures/generated',
        ];

        $actual = $processor->processConfiguration($configuration, []);

        self::assertEquals($expected, $actual);
    }

    public function testDefaultValuesCanBeOverridden(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();


        $expected = [
            'fixtures_path' => ['/root/path/fixtures'],
            'root_dirs' => ['/root/path'],
            'fixtures_generated_path' => '/new/fixtures/generated/path',
        ];

        $actual = $processor->processConfiguration(
            $configuration,
            [
                'alice_fixtures_dumper' => [
                    'fixtures_path' => ['/root/path/fixtures'],
                    'root_dirs' => ['/root/path'],
                    'fixtures_generated_path' => '/new/fixtures/generated/path',
                ],
            ]
        );

        self::assertEquals($expected, $actual);
    }

    public function testSingleFixturePathIsConvertedToArray(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $expected = [
            'fixtures_path' => ['/root/path/fixtures'],
            'root_dirs' => ['/root/path'],
            'fixtures_generated_path' => '/new/fixtures/generated/path',
        ];

        $actual = $processor->processConfiguration(
            $configuration,
            [
                'hautelook_alice' => [
                    'fixtures_path' => '/root/path/fixtures',
                    'root_dirs' => ['/root/path'],
                    'fixtures_generated_path' => '/new/fixtures/generated/path',
                ],
            ]
        );

        self::assertEquals($expected, $actual);
    }
}
