<?php
/**
 * Created by PhpStorm.
 * User: alexerm
 * Date: 4/11/15
 * Time: 12:48
 */

namespace XmlSerializer;


class OldDeserializer {
    /**
     * @param \SimpleXMLElement $xml
     *
     * @return mixed
     */
    public function fromSepaXml(\SimpleXMLElement $xml)
    {
        return $this->xmlToObj($xml);
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param \ReflectionProperty $refProp
     *
     * @return mixed
     */
    private function xmlToObj(\SimpleXMLElement $xml, \ReflectionProperty $refProp = null)
    {
        if ($refProp) {
            $rootClassName = $this->parseVarAnnotation($refProp);
        } else {
            $rootClassName = '\\' . $this->classNsMap[ current($xml->getNamespaces()) ] . '\\' . $xml->getName();
        }

        $document = new $rootClassName();
        $refCls  = new \ReflectionClass($document);

        foreach ($xml->getDocNamespaces(true, true) as $shortNs => $longNs) {
            /** @var \SimpleXMLElement $child */
            foreach ($xml->children($longNs) as $child) {
                $prop = $child->getName();
                if ($child->count() > 0) {
                    $refProp = $refCls->getProperty($child->getName());
                    if (isset( $document->{$prop} ) && ! is_array($document->{$prop})) {
                        $document->{$prop} = [$document->{$prop}];
                    }
                    if (isset( $document->{$prop} ) && is_array($document->{$prop})) {
                        $document->{$prop}[] = $this->xmlToObj($child, $refProp);
                    } else {
                        $document->{$prop} = $this->xmlToObj($child, $refProp);
                    }
                } elseif ($child->attributes($shortNs)->count() > 0) {
                    $propClassName = $this->parseVarAnnotation($refCls->getProperty($child->getName()));
                    $document->{$prop} = new $propClassName;
                    $document->{$prop}->value = $child->__toString();
                    /** @var \SimpleXMLElement $attr */
                    foreach ($child->attributes($shortNs) as $attr) {
                        $attrName = $attr->getName();
                        $document->{$prop}->{$attrName} = $attr->__toString();
                    }
                } else {
                    $document->{$prop} = $child->__toString();
                }
            }
        }

        return $document;
    }

    /**
     * Shitty implementation for annotation parsing
     * @return string
     */
    private function parseVarAnnotation(\ReflectionProperty $refProp)
    {
        /** @var \ReflectionClass $annotations */
        $annotations = $refProp->getDocComment();
        if ( ! preg_match('/@var (.*)/', $annotations, $m)) {
            return false; // no namespace
        }

        return $m[1];
    }
}