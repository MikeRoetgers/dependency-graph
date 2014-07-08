<?php

namespace MikeRoetgers\DependencyGraph;

class Node
{
    /**
     * @var Operation
     */
    private $operation;

    /**
     * @var array
     */
    private $dependents = array();

    /**
     * @var int
     */
    private $dependencyCounter = 0;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * @param Operation $operation
     */
    public function __construct(Operation $operation)
    {
        $this->operation = $operation;
    }

    public function addDependency()
    {
        $this->dependencyCounter++;
    }

    public function addDependent($id)
    {
        $this->dependents[] = $id;
    }

    public function decreaseDependencyCounter()
    {
        $this->dependencyCounter--;
    }

    public function hasDependenciesLeft()
    {
        return $this->dependencyCounter > 0;
    }

    public function hasDependents()
    {
        return count($this->dependents) > 0;
    }

    public function getDependents()
    {
        return $this->dependents;
    }

    public function getId()
    {
        return $this->operation->getId();
    }

    public function hasTag($tag)
    {
        return $this->operation->hasTag($tag);
    }

    /**
     * @return Operation
     */
    public function getOperation()
    {
        return $this->operation;
    }

    public function setStarted($started = true)
    {
        $this->started = $started;
    }

    public function isStarted()
    {
        return $this->started;
    }
}