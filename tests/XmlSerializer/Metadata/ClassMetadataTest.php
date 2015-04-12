<?php

namespace XmlSerializer\Fixtures\Metadata;


use XmlSerializer\Fixtures\Serialize\TextNode;
use XmlSerializer\Metadata\ClassMetadata;

class ClassMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testNamespace()
    {
        $node = new TextNode();
        $meta = new ClassMetadata($node);
        $this->assertEquals('com.example.testcase', $meta->getXmlNamespace());
    }
}