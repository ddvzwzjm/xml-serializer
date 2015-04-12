<?php

namespace XmlSerializer;


class Serializer {
    /** @var Visitor[] */
    private $visitors;

    public function addVisitor(Visitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    public function removeVisitor(Visitor $removeVisitor)
    {
        foreach ($this->visitors as $i => $visitor) {
            if ($removeVisitor == $visitor) {
                unset($this->visitors[$i]);
                $this->visitors = array_values($this->visitors);
                return true;
            }
        }

        return false;
    }

    public function getVisitors()
    {
        return $this->visitors;
    }


    public function serialize($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Invalid parameters: $object is not an object');
        }




        return '<xml></xml>';
    }
}