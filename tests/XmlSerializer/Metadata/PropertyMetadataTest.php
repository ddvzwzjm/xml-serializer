<?php
namespace XmlSerializer\Fixtures\Metadata;

use XmlSerializer\Config\Configuration;
use XmlSerializer\Metadata\ClassMetadata;
use XmlSerializer\Metadata\PropertyMetadata;

class PropertyMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetXmlNodeName()
    {
        $classMeta = new ClassMetadata(new PropertyMeta());

        $this->assertEquals('basicNode', $classMeta->getProperty('basicNode')->getXmlNodeName());
        $this->assertEquals('NodeHasName', $classMeta->getProperty('namedNode')->getXmlNodeName());
        $this->assertEquals('UPPERCASE_NODE', $classMeta->getProperty('UPPERCASE_NODE')->getXmlNodeName());
    }

    public function testNamespacedNodeName()
    {
        $configuration = new Configuration();
        $configuration->addNamespace('com.example.property', 'XmlSerializer\\Fixtures\\Metadata');

        $classMeta = new ClassMetadata(new PropertyMeta(), $configuration);
        $this->assertEquals('com.example.property', $classMeta->getProperty('basicNode')->getXmlNamespace());
        $this->assertEquals('ns1:basicNode', $classMeta->getProperty('basicNode')->getXmlNodeName());
    }
}