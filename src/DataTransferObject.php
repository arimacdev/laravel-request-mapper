<?php
declare(strict_types = 1);

namespace Maksi\RequestMapperL;

use BadMethodCallException;
use ReflectionClass;

/**
 * Class DataTransferObject
 *
 * @package Maksi\RequestMapperL
 */
abstract class DataTransferObject
{
    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function __call(string $name, array $arguments)
    {
        $methodPrefix = substr($name, 0, 3);
        
        if (!$methodPrefix === 'get') {
            throw new BadMethodCallException(
                sprintf('method with name %s not allowed', $name)
            );
        }
        
        $methodSuffix = substr($name, 3);
        
        $class = new ReflectionClass(static::class);
        foreach ($class->getProperties() as $property) {
            if ($property->getName() === $methodSuffix) {
                return $property->getValue($this);
            }
        }
        
        throw new BadMethodCallException(
            sprintf('method with name %s not allowed', $name)
        );
    }
    
}
