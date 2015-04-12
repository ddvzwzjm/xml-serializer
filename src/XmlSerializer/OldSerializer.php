<?php
namespace XmlSerializer;

use XmlSerializer\Config\Configuration;
use XmlSerializer\Metadata\ClassMetadata;

/**
 * Class Serializer
 *
 * @package Crosslend\ProviderBundle\Services\Sepa
 */
class OldSerializer
{
    /**
     * @var Configuration
     */
    private $configuration;


    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param $obj
     *
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function serialize($obj)
    {
        $xml = $this->createRootNode(new ClassMetadata($obj, $this->configuration));
        $this->appendObjectProperties($xml, $obj);

        return $xml;
    }

    /**
     * @param ClassMetadata $class
     *
     * @return \SimpleXMLElement
     */
    public function createRootNode(ClassMetadata $class)
    {
        $longNs       = $class->getXmlNamespace();
        $ns           = $this->configuration->getShortXmlNamespace($longNs);
        $rootNodeName = $class->getXmlNodeName();
        $xml          = new \SimpleXMLElement('<' . $rootNodeName . '/>', LIBXML_NOERROR, false, $ns);

        if ($this->configuration->getDefaultXmlNamespace()) {
            $xml->addAttribute('xmlns:xmlns', $this->configuration->getDefaultXmlNamespace());
        }

        $xmlNamespaces = $this->configuration->getAllShortXmlNamespaces();
        if (count($xmlNamespaces) > 1 || ( count($xmlNamespaces) == 1 && key($xmlNamespaces) !== $this->configuration->getDefaultXmlNamespace() )) {
            foreach ($xmlNamespaces as $longNs => $shortNs) {
                if ($longNs === $this->configuration->getDefaultXmlNamespace()) {
                    continue;
                }
                $attrNs = $shortNs ? 'xmlns:xmlns:' . $shortNs : 'xmlns:xmlns';

                if (!$xml->attributes()->{'xmlns:' . $shortNs}) {
                    $xml->addAttribute($attrNs, $longNs);
                }
            }
        }

        return $xml;
    }

    /**
     *
     * @param \SimpleXmlElement $xml
     * @param object $obj
     *
     * @throws \Exception
     */
    private function appendObjectProperties(\SimpleXMLElement $xml, $obj)
    {
        $clsMeta = new ClassMetadata($obj, $this->configuration);

        foreach ($clsMeta->getProperties() as $prop) {
            $propName = $prop->getName();
            $nodeName = "xmlns:" . $prop->getXmlNodeName();
            $value    = $obj->{$propName};

            if (is_object($value)) {
                $this->appendObjectProperties($xml->addChild($nodeName), $value);
            }
            elseif (is_string($value) && $prop->isScalarValue()) {
                $xml[0] = $value;
            }
            elseif (is_string($value) && $prop->isAttribute()) {
                $xml->addAttribute($prop->getName(), $value);
            }
            elseif (is_string($value)) {
                $xml->addChild($nodeName, $value);
            }
            elseif (( is_int($value) || is_float($value) ) && $prop->isScalarValue()) {
                $xml[0] = (string) $value;
            }
            elseif (is_null($value)) {
                continue;
            }
            elseif (is_array($value)) {
                if (count($value) == 0) {
                    continue;
                }
                foreach ($value as $itemValue) {
                    if (is_object($itemValue)) {
                        $this->appendObjectProperties($xml->addChild($nodeName), $itemValue);
                    }
                    else {
                        throw new \Exception("Failed to serialize. Only object node arrays supported");
                    }

                }
            }
            else {
                throw new \Exception("Not implemented yet. Prop: {$propName} cannot be converted to xml node");
            }
        }
    }


    /**
     * This method is called for each object node created.
     * ATTENTION: This function stands only for hacks required to force SEPA accepting generated XML!
     *
     * @param \SimpleXMLElement $xml
     * @param \ReflectionProperty $property
     * @param \ReflectionClass $propValue
     *
     * @return \SimpleXMLElement
     */
    private function xmlHacks(
        \SimpleXMLElement $xml,
        \ReflectionProperty $property,
        \ReflectionClass $propValue
    ) {
        if ($property->getName() == 'MandateInfo') {
            $xml->addAttribute(
                'xsi:type',
                $this->mapNs($this->getXmlNs($property)) . ":" . $propValue->getShortName(),
                "http://www.w3.org/2001/XMLSchema-instance"
            );
        }

        return $xml;
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return string
     */
    private function getNodeNameFromProperty(\ReflectionProperty $property)
    {
        return "xmlns:"
               . $this->mapNs($this->getXmlNs($property))
               . ':'
               . $property->getName();
    }

    /**
     * @param \ReflectionClass $valRefCls
     * @param \ReflectionProperty $refProp
     *
     * @return string
     * @internal param $refClass
     *
     */
    protected function getNodeName(\ReflectionClass $valRefCls, \ReflectionProperty $refProp = null)
    {
        $longNs        = $refProp ? $this->getXmlNs($refProp) : $this->getXmlNs($valRefCls);
        $nodeShortName = $refProp ? $refProp->getName() : $valRefCls->getShortName();
        $nodeName      = "xmlns:" . $this->mapNs($longNs) . ":" . $nodeShortName;

        return $nodeName;
    }


    private function _rootNode_INVALID(ClassMetadata $class)
    {
        $xml = new \SimpleXMLElement('<' . $class->getXmlNodeName() . '/>');

        $xml->addAttribute('xmlns', $class->getXmlNamespace());
        $xml->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        // don't want to store additional mapping, @todo avoid it if possible
//		$xml->addAttribute('xmlns:xsi:schemaLocation', 'urn:iso:std:iso:20022:tech:xsd:pain.001.003.03 pain.001.003.03.xsd');

        return $xml;
    }

    /**
     * @param $ns
     *
     * @return string
     */
    private function mapNs($ns)
    {
        return isset( $this->nsMap[ $ns ] ) ? $this->nsMap[ $ns ] : "";
    }

    /**
     * @param $refCls
     *
     * @return string
     */
    private function getXmlNs($refCls)
    {
        /** @var \ReflectionClass $annotations */
        $annotations = $refCls->getDocComment();
        if (!preg_match('/@xmlNamespace (.*)/', $annotations, $m)) {
            return ""; // no namespace
        }

        return $m[1];
    }

}