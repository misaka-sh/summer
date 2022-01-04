<?php


namespace summer\route;


use ReflectionObject;
use ReflectionProperty;

/**
 * 验证
 * @package summer\route
 */
class Validate
{
    /**
     * 属性
     * @var Property[]
     */
    public array $property = [];

    public function __construct(object $object)
    {
        $object = new ReflectionObject($object);
        $properties = $object->getProperties(ReflectionProperty::IS_PUBLIC);
        if (count($properties)) $this->parseProperties($properties);
    }

    /**
     * @param ReflectionProperty[] $properties
     */
    private function parseProperties(array $properties)
    {
        foreach ($properties as $property) {
            $this->property[] = new Property($property);
        }
    }

}