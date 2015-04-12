<?php
namespace XmlSerializer\Fixtures\Metadata;


class PropertyMeta {

    public $basicNode;

    /**
     * @var \XmlSerializer\Fixtures\Serialize\TextNode
     */
    public $typeNode;

    /**
     * @xmlName NodeHasName
     */
    public $namedNode;

    /**
     * @xmlNamespace com.example.testcase
     */
    public $namespaceNode;

    public $UPPERCASE_NODE;

}