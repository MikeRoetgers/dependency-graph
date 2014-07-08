<?php

namespace MikeRoetgers\DependencyGraph;

class Operation
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $tags = array();

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function addTag($tag)
    {
        $this->tags[] = $tag;
    }

    public function hasTag($tag)
    {
        return in_array($tag, $this->tags);
    }
}