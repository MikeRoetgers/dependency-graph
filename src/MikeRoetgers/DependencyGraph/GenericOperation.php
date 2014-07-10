<?php

namespace MikeRoetgers\DependencyGraph;

class GenericOperation implements Operation
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $tags = array();

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

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