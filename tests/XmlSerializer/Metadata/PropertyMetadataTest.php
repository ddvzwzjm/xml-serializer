<?php
namespace XmlSerializer\Fixtures\Metadata;


use XmlSerializer\Fixtures\Serialize\RootNode;
use XmlSerializer\Metadata\PropertyMetadata;

class PropertyMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetXmlNodeName()
    {
        $property = new \ReflectionProperty('RootNode', 'textNode');
        $metadata = new PropertyMetadata($property);
        $this->assertEquals('textNode')
    }
}