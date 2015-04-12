<?php
namespace XmlSerializer\Fixtures\Serialize\First;

class Envelope {
    /**
     * @xmlName Message
     * @var \XmlSerializer\Fixtures\Serialize\Second\Message
     */
    public $message;
}