<?php

namespace XmlSerializer\Fixtures\Deserialize\First;


use XmlSerializer\Fixtures\Deserialize\Second\SecondComplex;

class Root {
    /**
     * @var Complex
     */
    public $Complex;
    /**
     * @var SecondComplex
     */
    public $SecondComplex;
}