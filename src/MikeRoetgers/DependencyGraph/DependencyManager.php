<?php

namespace MikeRoetgers\DependencyGraph;

use MikeRoetgers\DependencyGraph\Exception\CycleException;
use MikeRoetgers\DependencyGraph\Exception\GraphWriteException;

class DependencyManager
{
    /**
     * @var Node[]
     */
    private $nodes = array();

    /**
     * @var bool
     */
    private $graphInitialized = false;

    /**
     * @param Operation $operation
     * @return $this
     * @throws Exception\GraphWriteException
     */
    public function addOperation(Operation $operation)
    {
        if ($this->graphInitialized) {
            throw new GraphWriteException('Graph was already initialized.');
        }
        $this->nodes[$operation->getId()] = new Node($operation);
        return $this;
    }

    /**
     * Given child operation depends on parent operation
     *
     * @param Operation $parentOperation
     * @param Operation $childOperation
     * @throws Exception\GraphWriteException
     */
    public function addDependencyByOperation(Operation $parentOperation, Operation $childOperation)
    {
        if ($this->graphInitialized) {
            throw new GraphWriteException('Graph was already initialized.');
        }

        if ($parentOperation->getId() === $childOperation->getId()) {
            return;
        }

        $this->nodes[$childOperation->getId()]->addDependency($parentOperation->getId());
        $this->nodes[$parentOperation->getId()]->addDependent($childOperation->getId());
    }

    /**
     * One parameter must be a tag, the other one an operation.
     *
     * @param string|Operation $parent
     * @param string|Operation $child
     * @throws \Exception
     */
    public function addDependencyByTag($parent, $child)
    {
        if ($this->graphInitialized) {
            throw new GraphWriteException('Graph was already initialized.');
        }

        if (!($parent instanceof Operation && is_string($child)) && !($child instanceof Operation && is_string($parent))) {
            throw new \Exception('One of $parent and $child must be an Operation object, the other one have to be a string');
        }

        if (is_string($parent)) {
            $tag = $parent;
            $operation = $child;
            $tagIsParent = true;
        } else {
            $tag = $child;
            $operation = $parent;
            $tagIsParent = false;
        }

        foreach ($this->nodes as $node) {
            if ($node->hasTag($tag) && $node->getId() != $operation->getId()) {
                if ($tagIsParent) {
                    $this->addDependencyByOperation($node->getOperation(), $operation);
                } else {
                    $this->addDependencyByOperation($operation, $node->getOperation());
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getExecutableOperations()
    {
        $this->initGraph();
        $list = array();

        foreach ($this->nodes as $node) {
            if (!$node->hasDependenciesLeft() && !$node->isStarted()) {
                $list[] = $node->getOperation();
            }
        }

        return $list;
    }

    /**
     * @return Node[]
     */
    public function getOperations()
    {
        return $this->nodes;
    }

    /**
     * An operation that is marked as started, is not returned when ::getExecutableOperations() is called.
     * But dependency is not fulfilled, so other operations depending on the operation still have to wait.
     *
     * @param Operation $operation
     */
    public function markAsStarted(Operation $operation)
    {
        $this->initGraph();
        $this->nodes[$operation->getId()]->setStarted();
    }

    /**
     * @param Operation $operation
     */
    public function markAsExecuted(Operation $operation)
    {
        $this->initGraph();
        $node = $this->nodes[$operation->getId()];
        foreach ($node->getDependents() as $dependent) {
            $this->nodes[$dependent]->decreaseDependencyCounter();
        }
        unset($this->nodes[$operation->getId()]);
    }

    /**
     * Returns true if all operations are marked as executed
     *
     * @return bool
     */
    public function isFinished()
    {
        $this->initGraph();
        return count($this->nodes) < 1;
    }

    private function initGraph()
    {
        if ($this->graphInitialized) {
            return;
        }
        $this->graphInitialized = true;

        $ops = $this->getExecutableOperations();
        if (empty($ops)) {
            throw new CycleException('Cannot find an entry point to the graph. You built a cycle.');
        }
        foreach ($ops as $op) {
            $this->checkForDependencies($this->nodes[$op->getId()], array());
        }
    }

    private function checkForDependencies(Node $node, array $seen)
    {
        if (in_array($node->getId(), $seen)) {
            throw new CycleException('Detected a cycle. ' . implode(' -> ', $seen) . ' -> ' . $node->getId());
        }
        $seen[] = $node->getId();

        if (!$node->hasDependents()) {
            return $seen;
        }

        $seenLists = array();

        foreach ($node->getDependents() as $dep) {
            $seenLists[] = $this->checkForDependencies($this->nodes[$dep], $seen);
        }

        return $seenLists;
    }
}