<?php
namespace XmlSerializer\Deserializer;

use XmlSerializer\Config\Configuration;

class XmlElement extends \SimpleXMLElement
{
    public function getClassName(Configuration $configuration = null)
    {
        if (!$configuration) {
            return '\\stdClass';
        }
        if ($this->getNamespaces()) {
            $className = $configuration->getClassNamespace($this->getXmlNamespace()) . '\\' . $this->getName();
            if (class_exists($className)) {
                return $className;
            }
        }
        if ($configuration->getDefaultXmlNamespace()) {
            $className = $configuration->getClassNamespace($configuration->getDefaultXmlNamespace()) . '\\' . $this->getName();
            if (class_exists($className)) {
                return $className;
            }
        }
        return '\\stdClass';
    }

    public function getXmlNamespace()
    {
        return current($this->getNamespaces());
    }

    /**
     * @return XmlElement[]
     */
    public function getChildrenNodes()
    {
        $children = [];

        if ($this->getDocNamespaces(true, true)) {
            foreach ($this->getDocNamespaces(true, true) as $shortNs => $longNs) {
                /** @var \SimpleXMLElement $child */
                foreach ($this->children($longNs) as $child) {
                    $children[] = $child;
                }
            }
        }

        foreach ($this->children() as $child) {
            $children[] = $child;
        }

        return $children;
    }

    /**
     * @return XmlElement[]
     */
    public function getAttributes()
    {
        $attributes = [];

        if ($this->getDocNamespaces(true, true)) {
            foreach ($this->getDocNamespaces(true, true) as $shortNs => $longNs) {
                /** @var \SimpleXMLElement $child */
                foreach ($this->attributes($longNs) as $attribute) {
                    $attributes[] = $attribute;
                }
            }
        }

        foreach ($this->attributes() as $attribute) {
            $attributes[] = $attribute;
        }

        return $attributes;
    }
}