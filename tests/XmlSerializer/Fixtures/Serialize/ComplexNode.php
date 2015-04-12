<?php

namespace XmlSerializer\Fixtures\Serialize;


class ComplexNode {
    /**
     * @xmlType attribute
     * @var string
     */
    public $testAttr;

    /**
     * @var TextNode
     */
    public $textNode;

    /**
     * @var string
     */
    public $stringNode;

    /**
     * @var TextNode[]
     */
    public $arrayTextNodes = [];

    public $nullNode = null;
}