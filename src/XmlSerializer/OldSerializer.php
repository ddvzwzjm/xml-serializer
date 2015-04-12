<?php
namespace XmlSerializer;
use XmlSerializer\Serializer\Visitor\Visitor;

/**
 * Class Serializer
 *
 * @package Crosslend\ProviderBundle\Services\Sepa
 */
class Serializer
{
    /**
     * @var string
     */
    protected $defaultNs = 'urn:conxml:xsd:container.nnn.002.02';

    /**
     * @var array
     */
    protected $nsMap = [
        "urn:iso:std:iso:20022:tech:xsd:pain.002.002.03" => "ns2",
        "urn:iso:std:iso:20022:tech:xsd:pain.008.002.02" => "ns3",
        "urn:iso:std:iso:20022:tech:xsd:pain.001.002.03" => "ns4",
        "urn:iso:std:iso:20022:tech:xsd:pain.002.003.03" => "ns5",
        "de.xcom.sdd.management.payment.001"             => "ns6",
        "de.xcom.sdd.management.types.001"               => "ns7",
        "de.xcom.sdd.management.masterdata.001"          => "ns8",
        "de.xcom.sdd.management.mandate.001"             => "ns9",
        "urn:iso:std:iso:20022:tech:xsd:pain.001.003.03" => "ns10",
        "urn:iso:std:iso:20022:tech:xsd:pain.008.003.02" => "ns11",
        "de.xcom.sdd.management.report.001"              => "ns12",
        "urn:iso:std:iso:20022:tech:xsd:camt.054.001.02" => "ns13",
        "urn:iso:std:iso:20022:tech:xsd:camt.054.001.04" => "ns13",
        "urn:conxml:xsd:container.nnn.003.02"            => "ns14",
        "de.xcom.sdd.management.elv.migration.001"       => "ns15",
        "urn:conxml:xsd:container.xcom.nnn.00x.02"       => "ns16",
        "urn:iso:std:iso:20022:tech:xsd:camt.053.001.02" => "ns17",
        "urn:iso:std:iso:20022:tech:xsd:camt.053.001.04" => "ns17"
    ];

    protected $classNsMap = [
        "de.xcom.sdd.management.payment.001"             => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Payment",
        "de.xcom.sdd.management.types.001"               => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Types",
        "de.xcom.sdd.management.masterdata.001"          => "Crosslend\\ProviderBundle\\Entity\\Sepa\\MasterData",
        "de.xcom.sdd.management.mandate.001"             => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Mandate",
        "de.xcom.sdd.management.report.001"              => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Report",
        "urn:iso:std:iso:20022:tech:xsd:pain.001.003.03" => "Crosslend\\ProviderBundle\\Entity\\Sepa\\PainCreditTransfer",
        "urn:iso:std:iso:20022:tech:xsd:camt.054.001.02" => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Camt054",
        "urn:iso:std:iso:20022:tech:xsd:camt.054.001.04" => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Camt054",
        "urn:iso:std:iso:20022:tech:xsd:camt.053.001.02" => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Camt053",
        "urn:iso:std:iso:20022:tech:xsd:camt.053.001.04" => "Crosslend\\ProviderBundle\\Entity\\Sepa\\Camt053"
    ];


    /**
     * @param $obj
     *
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function toIso20022Xml($obj)
    {
        $refCls = new \ReflectionClass($obj);
        $xml    = $this->getIso20022Envelope($refCls);

        $this->objToIso20022Xml($xml, $obj);

        return $xml;
    }

    /**
     *
     * @param $xml
     * @param $obj
     *
     * @throws \Exception
     */
    private function objToIso20022Xml(\SimpleXMLElement $xml, $obj)
    {
        $refCls = new \ReflectionClass($obj);

        foreach ($this->getPropsInRightOrder($refCls) as $prop) {
            $value              = $obj->{$prop};
            $reflectionProperty = $refCls->getProperty($prop);
            $xmlType            = $this->_parseAnnotation($reflectionProperty, 'xmlType');

            if (is_object($value)) {
                $reflectionValue = new \ReflectionClass($value);
                $nodeName        = $reflectionProperty->getName();
                $this->objToIso20022Xml(
                    $this->xmlHacks($xml->addChild($nodeName), $reflectionProperty, $reflectionValue),
                    $value
                );
            } elseif (is_string($value) && $xmlType == 'value') {
                $xml[0] = $value;
            } elseif (is_string($value) && $xmlType == 'attribute') {
                $xml->addAttribute($prop, $value);
            } elseif (is_string($value)) {
                $nodeName = $reflectionProperty->getName();
                $xml->addChild($nodeName, $value);
            } elseif (( is_int($value) || is_float($value) ) && $xmlType == 'value') {
                $xml[0] = (string) $value;
            } elseif (is_null($value)) {
                continue;
            } elseif (is_array($value)) {
                $nodeName = $reflectionProperty->getName();
                foreach ($value as $itemValue) {
                    $reflectionValue = new \ReflectionClass($itemValue);
                    $this->objToIso20022Xml(
                        $this->xmlHacks($xml->addChild($nodeName), $reflectionProperty, $reflectionValue),
                        $itemValue
                    );
                }
            } else {
                throw new \Exception("Not implemented yet. Prop: $prop cannot be converted to xml node");
            }
        }
    }

    /**
     * @param $obj
     *
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function toSepaXml($obj)
    {
        $refCls = new \ReflectionClass($obj);
        $xml    = $this->getXcomEnvelope($refCls);

        $this->objToXml($xml, $obj);

        return $xml;
    }

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
     * @param \ReflectionClass $obj
     *
     * @return array
     */
    private function getPropsInRightOrder(\ReflectionClass $obj)
    {
        $props = [];
        if ($obj->getParentClass()) {
            $props = $this->getPropsInRightOrder($obj->getParentClass());

        }
        foreach ($obj->getProperties() as $property) {
            if ( ! in_array($property->getName(), $props)) {
                $props[] = $property->getName();
            }
        }

        return $props;
    }

    /**
     * @param $xml
     * @param $obj
     *
     * @throws \Exception
     */
    private function objToXml($xml, $obj)
    {
        $refCls = new \ReflectionClass($obj);

        foreach ($this->getPropsInRightOrder($refCls) as $prop) {
            $value              = $obj->{$prop};
            $reflectionProperty = $refCls->getProperty($prop);

            if (is_object($value)) {
                $reflectionValue = new \ReflectionClass($value);
                $nodeName        = $this->getNodeName($reflectionValue, $reflectionProperty);
                $this->objToXml(
                    $this->xmlHacks($xml->addChild($nodeName), $reflectionProperty, $reflectionValue),
                    $value
                );
            }
            elseif (is_string($value)) {
                $nodeName = $this->getNodeNameFromProperty($reflectionProperty);
                $xml->addChild($nodeName, $value);
            }
            elseif (is_null($value)) {
                continue;
            }
            else {
                throw new \Exception("Not implemented yet");
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

    /**
     * @param \ReflectionClass $class
     *
     * @return \SimpleXMLElement
     */
    private function getXcomEnvelope(\ReflectionClass $class)
    {
        $longNs       = $this->getXmlNs($class);
        $ns           = $this->mapNs($longNs);
        $rootNodeName = $ns . ':' . $class->getShortName();
        $xml          = new \SimpleXMLElement('<' . $rootNodeName . '/>', LIBXML_NOERROR, false, $ns);

        $xml->addAttribute('xmlns:xmlns', $this->defaultNs);

        foreach ($this->nsMap as $longNs => $shortNs) {
            $attrNs = $shortNs ? 'xmlns:xmlns:' . $shortNs : 'xmlns:xmlns';

            if ( ! $xml->attributes()->{'xmlns:' . $shortNs}) {
                $xml->addAttribute($attrNs, $longNs);
            }
        }

        return $xml;
    }

    private function getIso20022Envelope(\ReflectionClass $class)
    {
        $rootNodeName = $class->getShortName();
        $xml          = new \SimpleXMLElement('<' . $rootNodeName . '/>');

        $xml->addAttribute('xmlns', $this->getXmlNs($class));
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
        if ( ! preg_match('/@xmlNamespace (.*)/', $annotations, $m)) {
            return ""; // no namespace
        }

        return $m[1];
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

    private function _parseAnnotation($refProp, $key)
    {
        /** @var \ReflectionClass $annotations */
        $annotations = $refProp->getDocComment();
        if ( ! preg_match('/@' . $key . ' (.*)/', $annotations, $m)) {
            return false; // no namespace
        }

        return $m[1];
    }

}