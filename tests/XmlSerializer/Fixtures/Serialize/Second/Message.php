<?php
namespace XmlSerializer\Fixtures\Serialize\Second;


class Message {
    /**
     * @var \XmlSerializer\Fixtures\Serialize\First\MessageParameter[]
     */
    public $Parameter;

    /**
     * @var \XmlSerializer\Fixtures\Serialize\Second\SequenceNumber
     */
    public $SequenceNumber;

    /**
     * @xmlNamespace com.example.first
     * @xmlType attribute
     * @var string
     */
    public $language;
}