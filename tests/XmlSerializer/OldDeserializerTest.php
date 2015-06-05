<?php
namespace XmlSerializer;


use XmlSerializer\Config\Configuration;

class OldDeserializerTest extends \PHPUnit_Framework_TestCase
{
    public function testDeserializeSimpleXml()
    {
        $config = new Configuration();

        $deserializer = new OldDeserializer($config);
        $object = $deserializer->deserialize('<root><sub>value</sub></root>'); // 01-basic.xml

        $this->assertInstanceOf('\\stdClass', $object);
        $this->assertEquals('value', $object->sub);

    }

    public function multiNsDeserializerProvider()
    {
        $config = new Configuration();
        $config->addNamespace('urn:com.example.first', '\\XmlSerializer\\Fixtures\\Deserialize\\First');
        $config->addNamespace('urn:com.example.second', '\\XmlSerializer\\Fixtures\\Deserialize\\Second');
        $config->setDefaultNamespace('urn:com.example.first');

        $deserializer = new OldDeserializer($config);

        return [
            [$deserializer]
        ];
    }

    /**
     * @dataProvider multiNsDeserializerProvider
     */
    public function testNamespaceRoot(OldDeserializer $deserializer)
    {
        $root = $deserializer->deserialize($this->_fixture('03-ns-mapped'));
        $this->assertInstanceOf('\\XmlSerializer\\Fixtures\\Deserialize\\First\\Root', $root);

    }

    /**
     * @dataProvider multiNsDeserializerProvider
     */
    public function testNamespaceMultipleXml(OldDeserializer $deserializer)
    {
//        $object = $deserializer->deserialize($this->_fixture('04-ns-multiple'));
//        $this->assertInstanceOf('\\XmlSerializer\\Fixtures\\Deserialize\\First\\Root', $object);
//        $this->assertInstanceOf('\\XmlSerializer\\Fixtures\\Deserialize\\First\\Complex', $object->Complex);
//        $this->assertInstanceOf('\\XmlSerializer\\Fixtures\\Deserialize\\Second\\SecondComplex', $object->SecondComplex);

    }


    private function _fixture($name)
    {
        return file_get_contents(__DIR__ . '/Fixtures/Deserialize/xml/'.$name.'.xml');
    }
}