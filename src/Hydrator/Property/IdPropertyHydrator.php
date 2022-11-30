<?php
/**
 * @author Denis Lazarev <lazarev.d.a.1990@mail.ru>
 * @date 2022.11.30
 */
declare(strict_types=1);

namespace ITakSoydet\AliceFixturesDumperBundle\Hydrator\Property;

use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\HydrationException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InaccessiblePropertyException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InvalidArgumentException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\NoSuchPropertyException;
use ReflectionClass;
use ReflectionProperty;

class IdPropertyHydrator implements PropertyHydratorInterface
{
    use IsAServiceTrait;

    public function __construct(
        private readonly PropertyHydratorInterface $decorator,
        private readonly ObjectManager $objectManager
    ) {
    }

    /**
     * @throws NoSuchPropertyException
     * @throws InaccessiblePropertyException
     * @throws InvalidArgumentException When the typehint does not match for example
     * @throws HydrationException
     */
    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface
    {
        try {
            return $this->decorator->hydrate($object, $property, $context);
        } catch (NoSuchPropertyException $exception) {
            $instance = $object->getInstance();
            $class = $this->objectManager->getMetadataFactory()->getMetadataFor(get_class($instance));
            if (!in_array($property->getName(), $class->getIdentifierFieldNames())) {
                throw $exception;
            }
            $refClass = new ReflectionClass($instance);
            $refProperty = $this->getPropertyRef($refClass, $property->getName());
            if (null === $refProperty) {
                throw $exception;
            }
            // $refProperty->setAccessible(true); // deprecated in 8.1
            $refProperty->setValue($instance, $property->getValue());
            // $refProperty->setAccessible(false); // deprecated in 8.1

            return new SimpleObject($object->getId(), $instance);
        }
    }

    private function getPropertyRef(ReflectionClass $refClass, string $name): ?ReflectionProperty
    {
        if ($refClass->hasProperty($name)) {
            return $refClass->getProperty($name);
        }
        if (false !== ($parentClass = $refClass->getParentClass())) {
            return $this->getPropertyRef($parentClass, $name);
        }
        return null;
    }
}
