<?php

namespace MikeRoetgers\DependencyGraph;

interface Operation
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $tag
     * @return void
     */
    public function addTag($tag);

    /**
     * @param string $tag
     * @return bool
     */
    public function hasTag($tag);
}