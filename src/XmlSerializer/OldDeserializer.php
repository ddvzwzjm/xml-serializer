<?php
namespace XmlSerializer;


use XmlSerializer\Config\Configuration;
use XmlSerializer\Deserializer\XmlElement;
use XmlSerializer\Metadata\ClassMetadata;

class OldDeserializer
{

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration;
    }

    /**
     *
     * @return mixed
     */
    public function deserialize($xmlString)
    {
        /** @var XmlElement $xml */
        $xml = simplexml_load_string($xmlString, 'XmlSerializer\\Deserializer\\XmlElement'); // todo: move XmlElement class name into configuration

        return $this->xmlToObj($xml);
    }

    /**
     * @param XmlElement $xml
     * @param string $rootClassName
     *
     * @return mixed
     * @throws \Exception
     *
     */
    private function xmlToObj(XmlElement $xml, $rootClassName = '')
    {
        if (!$rootClassName) {
            $rootClassName = $xml->getClassName($this->configuration); // todo: use node name index instead
        }

        $document = new $rootClassName();
        $clsMeta  = new ClassMetadata($document);
        $refCls   = new \ReflectionClass($document);

        foreach ($xml->getChildrenNodes() as $child) {
            $prop = $child->getName();
            if (count($child->getChildrenNodes()) > 0) {
                if (isset( $document->{$prop} ) && !is_array($document->{$prop})) {
                    $document->{$prop} = [$document->{$prop}];
                }
                else {
                    if ($clsMeta->hasProperty($child->getName())) {
                        $propMeta  = $clsMeta->getProperty($child->getName());
                        $propClass = $propMeta->getClass();
                    }
                    else {
                        $propClass = '\\stdClass';
                    }

                    if (isset( $document->{$prop} ) && is_array($document->{$prop})) {
                        $document->{$prop}[] = $this->xmlToObj($child, $propClass);
                    }
                    else {
                        $document->{$prop} = $this->xmlToObj($child, $propClass);
                    }
                }
            }
            elseif (count($child->getAttributes()) > 0) {
                if ($clsMeta->hasProperty($child->getName()) && $clsMeta->hasClassProperty($child->getName())) {
                    $propClassName  = $clsMeta->getProperty($child->getName())->getClass(); // $this->parseVarAnnotation($refCls->getProperty($child->getName()));
                }
                else {
                    $propClassName = '\\stdClass';
                }

                $document->{$prop}        = new $propClassName;
                $document->{$prop}->value = $child->__toString();
                /** @var XMLElement $attr */
                foreach ($child->getAttributes() as $attr) {
                    $attrName                       = $attr->getName();
                    $document->{$prop}->{$attrName} = $attr->__toString();
                }
            }
            else {
                $document->{$prop} = $child->__toString();
            }
        }

        return $document;
    }
}