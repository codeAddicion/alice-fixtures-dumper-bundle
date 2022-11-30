<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Persister;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use InvalidArgumentException;
use Nelmio\Alice\IsAServiceTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Yaml;

class SaveToYamlFilePersister implements PersisterInterface
{
    use IsAServiceTrait;

    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly Yaml $encoder,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }


    public function persist(array $objects, array $parameters, PurgeMode $purgeMode): void
    {
        if (empty($parameters['fixtures_generated_path'])) {
            throw new InvalidArgumentException('Parameter "fixtures_generated_path" should not be blank.');
        }

        if (!is_dir($parameters['fixtures_generated_path'])
            && !mkdir($parameters['fixtures_generated_path'], 0777, true)) {
            throw new RuntimeException(
                sprintf(
                    'There is no existing directory at "%s" and it could not be created.',
                    $parameters['fixtures_generated_path']
                )
            );
        }
        $fullFileName = $parameters['fixtures_generated_path'] . "/fixtures.yaml";

        $data = [];
        $this->ksortRecursive($objects);
        foreach ($objects as $id => $object) {
            $classMetadata = $this->manager->getClassMetadata(get_class($object));
            foreach ($classMetadata->fieldMappings as $fieldName => $fieldMapping) {
                $value = $this->propertyAccessor->getValue($object, $fieldName);
                if (null === $value) {
                    continue;
                }
                if ($value instanceof DateTimeInterface) {
                    $value = $this->parseDateTime($value);
                }
                $data[$classMetadata->name][$id][$fieldName] = $value;
            }
            foreach ($classMetadata->associationMappings as $assocName => $assocMapping) {
                $value = $this->propertyAccessor->getValue($object, $assocName);
                if (null === $value) {
                    continue;
                }
                if ($value instanceof Collection && $value->count() === 0) {
                    continue;
                }
                if ($value instanceof Collection) {
                    $names = [];
                    foreach ($value as $item) {
                        $assocId = array_search($item, $objects);
                        $names [] = sprintf('@%s', $assocId);
                    }
                    $data[$classMetadata->name][$id][$assocName] = $names;
                    continue;
                }
                $assocId = array_search($value, $objects);
                if (false === $assocId) {
                    continue;
                }
                $data[$classMetadata->name][$id][$assocName] = sprintf('@%s', $assocId);
            }
            if (!isset($data[$classMetadata->name][$id])) {
                $data[$classMetadata->name][$id] = null;
            }
            $this->logger->info(sprintf('Persist object %s', $id));
        }
        $this->ksortRecursive($data, 1);


        if (!in_array(
                $purgeMode->getValue(),
                [PurgeMode::createDeleteMode()->getValue(), PurgeMode::createTruncateMode()->getValue()]
            ) && file_exists($fullFileName)) {
            $this->logger->info('Load dump');
            $content = file_get_contents($fullFileName);
            $fixtures = $this->encoder->parse($content);
            if (!empty($fixtures)) {
                $data = array_replace_recursive($data, $this->arrayIntersectKeyRecursive($fixtures, $data));
            }
        }
        $this->ksortRecursive($data);


        $string = $this->encoder->dump($data, 10, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $this->logger->info('Save new dump');

        file_put_contents($fullFileName, $string);
    }

    private function ksortRecursive(&$array, int $level = 0): bool
    {
        foreach ($array as &$value) {
            if (is_array($value) && $level > 0) {
                $this->ksortRecursive($value, --$level);
            }
        }
        return ksort($array, SORT_NATURAL);
    }

    private function parseDateTime(DateTimeInterface $value): string
    {
        return sprintf('<(new \DateTime("%s"))>', $value->format('Y-m-d h:i:s'));
    }

    private function arrayIntersectKeyRecursive(array $array1, array $array2): array
    {
        $array1 = array_intersect_key($array1, $array2);

        foreach ($array1 as $key => &$value) {
            if (is_array($value) && is_array($array2[$key])) {
                $value = $this->arrayIntersectKeyRecursive($value, $array2[$key]);
            }
        }

        return $array1;
    }
}
