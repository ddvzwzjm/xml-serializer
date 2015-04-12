<?php

namespace XmlSerializer\Metadata;


use XmlSerializer\Config\Configuration;

class PropertyMetadata
{
    const XML_NAME_ANNOTATION = 'xmlName';
    const XML_TYPE_ANNOTATION = 'xmlType';
    const XML_TYPE_ATTRIBUTE = 'attribute';
    const XML_TYPE_VALUE = 'value';

    /**
     * @var \ReflectionProperty
     */
    private $property;
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var ClassMetadata
     */
    private $parentClass;

    public function __construct(\ReflectionProperty $property, Configuration $configuration = null, ClassMetadata $parentClass = null)
    {
        $this->property      = $property;
        $this->configuration = $configuration;
        $this->parentClass   = $parentClass;
    }

    public function getName()
    {
        return $this->property->getName();
    }

    public function getXmlNamespace()
    {
        $namespace = $this->_parseAnnotation($this->property->getDocComment(), 'xmlNamespace');
        if (!$namespace && $this->configuration) {
            $value = $this->getValue();
            if (is_object($this->getValue())) {
                $classMeta = new ClassMetadata($this->getValue(), $this->configuration);
                $namespace = $classMeta->getXmlNamespace();
            }
            elseif (is_array($value) && count($value) > 0) {
                $classMeta = new ClassMetadata($this->getValue()[0], $this->configuration);
                $namespace = $classMeta->getXmlNamespace();
            }
            else {
                $classNamespace = $this->property->getDeclaringClass()->getNamespaceName();
                $namespace      = $this->configuration->getXmlNamespace($classNamespace);
            }
        }

        return $namespace;
    }

    public function getXmlNodeName()
    {
        $xmlName = $this->_parseAnnotation($this->property->getDocComment(), self::XML_NAME_ANNOTATION);
        if (!$xmlName) {
            $xmlName = $this->property->getName();
        }

        if ($this->configuration && $this->getXmlNamespace() !== $this->configuration->getDefaultXmlNamespace()) {
            $alias = $this->configuration->getShortXmlNamespace($this->getXmlNamespace());
            if ($alias) {
                $xmlName = $alias . ':' . $xmlName;
            }
        }

        return $xmlName;
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

    public function isAttribute()
    {
        return $this->_parseAnnotation($this->property->getDocComment(), self::XML_TYPE_ANNOTATION) == self::XML_TYPE_ATTRIBUTE;
    }

    public function isComplexNode()
    {
        return !$this->isAttribute() && !$this->isScalarValue();
    }

    public function isScalarValue()
    {
        return $this->_parseAnnotation($this->property->getDocComment(), self::XML_TYPE_ANNOTATION) == self::XML_TYPE_VALUE
               || $this->property->getName() == 'value';
    }

    public function getValue()
    {
        if (!$this->parentClass) {
            throw new \Exception('Invalid method call. Object reference is not found - property value cannot be obtained');
        }

        return $this->property->getValue($this->parentClass->getObject());
    }
}