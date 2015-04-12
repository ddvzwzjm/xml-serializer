<?php
namespace XmlSerializer;

use XmlSerializer\Fixtures\Serialize\ComplexNode;
use XmlSerializer\Fixtures\Serialize\First\Envelope;
use XmlSerializer\Fixtures\Serialize\First\MessageParameter;
use XmlSerializer\Fixtures\Serialize\RootNode;
use XmlSerializer\Fixtures\Serialize\Second\Message;
use XmlSerializer\Fixtures\Serialize\Second\SequenceNumber;
use XmlSerializer\Fixtures\Serialize\TextNode;
use XmlSerializer\Metadata\ClassMetadata;

class OldSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function singleNsSerializerProvider()
    {
        $config = new Config\Configuration();

        $config->addNamespace('com.example.testcase', 'XmlSerializer\Fixtures\Serialize');
        $config->setDefaultNamespace('com.example.testcase');

        return [[new OldSerializer($config)]];
    }

    public function multiNsSerializerProvider()
    {
        $config = new Config\Configuration();

        $config->setDefaultNamespace('com.example.first');
        $config->addNamespace('com.example.first', 'XmlSerializer\Fixtures\Serialize\\First');
        $config->addNamespace('com.example.second', 'XmlSerializer\Fixtures\Serialize\\Second');

        return [[new OldSerializer($config)]];
    }

    public function namedNsSerializerProvider()
    {
        // TODO: not implemented yet
    }

    public function partialNsSerializerProvider()
    {
        // TODO: not implemented yet
    }

    /**
     * @dataProvider singleNsSerializerProvider
     * @param OldSerializer $serializer
     */
    public function testTextNode(OldSerializer $serializer)
    {
        $root = new RootNode();
        $root->textNode = new TextNode();
        $root->textNode = 'node_value';

        $this->assertEquals(
            '<textNode>node_value</textNode>',
            $serializer->serialize($root)->children()->asXml()
        );
    }

    /**
     * @dataProvider singleNsSerializerProvider
     * @param OldSerializer $serializer
     */
    public function testComplexNode(OldSerializer $serializer)
    {
        $root = new RootNode();
        $root->complexNode = new ComplexNode();
        $root->complexNode->textNode = 'textValue';
        $root->complexNode->stringNode = 'stringValue';
        $root->complexNode->testAttr = 'testAttirbuteValue';
        $el1 = new TextNode();
        $el1->value = 'element1';
        $el2 = new TextNode();
        $el2->value = 'element2';
        $root->complexNode->arrayTextNodes = [$el1, $el2];

        $this->assertEquals(
            '<complexNode testAttr="testAttirbuteValue"><textNode>textValue</textNode><stringNode>stringValue</stringNode><arrayTextNodes>element1</arrayTextNodes><arrayTextNodes>element2</arrayTextNodes></complexNode>',
            $serializer->serialize($root)->children()->asXml()
        );
    }

    /**
     * @dataProvider multiNsSerializerProvider
     */
    public function testMultipleNsRootNode(OldSerializer $serializer)
    {
        $this->assertStringEqualsFile(
            $this->_fixture('00-RootNode.xml'),
            $serializer->serialize(new Envelope())->asXML()
        );
    }

    /**
     * @dataProvider multiNsSerializerProvider
     */
    public function testMultipleNamespaces(OldSerializer $serializer)
    {
//        $this->markTestSkipped('not ready to run this test yet');
        $envelope = new Envelope();
        $envelope->message = new Message();
        $param = new MessageParameter();
        $param->value = 'paramValue';
        $envelope->message->Parameter = [$param];
        $envelope->message->SequenceNumber = new SequenceNumber();
        $envelope->message->SequenceNumber->value = 1;
        $envelope->message->language = 'en';

        $this->assertStringEqualsFile(
            $this->_fixture('01-MultiNamespace.xml'),
            $serializer->serialize($envelope)->asXML()
        );
    }


    protected function _fixture($name)
    {
        return __DIR__ . '/Fixtures/Serialize/xml/'.$name;
    }
}