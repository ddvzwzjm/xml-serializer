<?php

namespace XmlSerializer\Metadata;


use XmlSerializer\Config\Configuration;

/**
 * Class ClassMetadata
 * @package XmlSerializer\Metadata
 */
class ClassMetadata
{
    /**
     * @var
     */
    private $object;
    /**
     * @var \ReflectionClass
     */
    private $reflection;
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct($object, Configuration $configuration = null)
    {
        $this->object        = $object;
        $this->reflection    = new \ReflectionClass($object);
        $this->configuration = $configuration;
    }

    /**
     *
     */
    public function getXmlNamespace()
    {
        $namespace = $this->_parseAnnotation($this->reflection->getDocComment(), 'xmlNamespace');
        if (!$namespace && $this->configuration) {
            $namespace = $this->configuration->getXmlNamespace($this->reflection->getNamespaceName());
        }

        return $namespace;
    }

    /**
     * @return PropertyMetadata[]
     */
    public function getProperties()
    {
        $classProperties = $this->_getClassProperties($this->reflection);

        $propertyList = [];

        foreach ($classProperties as $propertyName) {
            $propertyList[] = new PropertyMetadata(
                $this->reflection->getProperty($propertyName),
                $this->configuration,
                $this
            );
        }

        return $propertyList;
    }

    /**
     * @param $name
     *
     * @return PropertyMetadata
     */
    public function getProperty($name)
    {
        return new PropertyMetadata(
            $this->reflection->getProperty($name),
            $this->configuration,
            $this);
    }

    /**
     * @param $annotations
     * @param $key
     *
     * @return bool
     */
    private function _parseAnnotation($annotations, $key)
    {
        if (!preg_match('/@' . $key . ' (.*)/', $annotations, $m)) {
            return false; // no namespace
        }

        return $m[1];
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function _getClassProperties(\ReflectionClass $class)
    {
        $props = [];
        if ($class->getParentClass()) {
            $props = $this->_getClassProperties($class->getParentClass());

        }
        foreach ($class->getProperties() as $property) {
            if (!in_array($property->getName(), $props)) {
                $props[] = $property->getName();
            }
        }

        return $props;
    }

    public function getXmlNodeName()
    {
        if ($this->getXmlNamespace() !== $this->configuration->getDefaultXmlNamespace()) {
            $alias = $this->configuration->getShortXmlNamespace($this->getXmlNamespace());

            return $alias . ':' . $this->reflection->getShortName();
        }
        else {
            return $this->reflection->getShortName();
        }
    }

    public function getObject()
    {
        return $this->object;
    }
}