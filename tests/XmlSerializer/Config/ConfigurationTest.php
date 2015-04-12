<?php
namespace XmlSerializer\Config;


class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    const XMLNS = 'com.example.testcase';

    public function testGetXmlNamespace()
    {
        $config = new Configuration();
        $config->addNamespace(self::XMLNS, 'Acme\\XsdTypes');
        $this->assertFalse($config->getXmlNamespace("FooDuck\\BarService"));
        $this->assertEquals(self::XMLNS, $config->getXmlNamespace('Acme\\XsdTypes\\Level1'));
        $this->assertEquals(self::XMLNS, $config->getXmlNamespace('Acme\\XsdTypes\\Level1\\Level2'));
        $this->assertEquals(self::XMLNS, $config->getXmlNamespace('Acme\\XsdTypes\\Level1\\Level2\\Level3'));
    }
}