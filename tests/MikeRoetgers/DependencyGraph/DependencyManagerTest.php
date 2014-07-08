<?php

namespace MikeRoetgers\DependencyGraph;

class DependencyManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleDependencyGraph()
    {
        $op1 = new Operation(1);
        $op2 = new Operation(2);
        $op3 = new Operation(3);
        $op4 = new Operation(4);

        $dm = new DependencyManager();
        $dm->addOperation($op1);
        $dm->addOperation($op2);
        $dm->addOperation($op3);
        $dm->addOperation($op4);

        //      1
        //    /  \
        //   2    3
        //    \  /
        //     4

        $dm->addDependencyByOperation($op1, $op2);
        $dm->addDependencyByOperation($op1, $op3);
        $dm->addDependencyByOperation($op2, $op4);
        $dm->addDependencyByOperation($op3, $op4);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(1, $ops);
        $this->assertContains($op1, $ops);

        $dm->markAsExecuted($op1);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(2, $ops);
        $this->assertContains($op2, $ops);
        $this->assertContains($op3, $ops);

        $dm->markAsExecuted($op2);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(1, $ops);
        $this->assertContains($op3, $ops);

        $dm->markAsExecuted($op3);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(1, $ops);
        $this->assertContains($op4, $ops);
    }

    public function testDependencyGraphWithTwoEntryNodes()
    {
        $op1 = new Operation(1);
        $op2 = new Operation(2);
        $op3 = new Operation(3);
        $op4 = new Operation(4);
        $op5 = new Operation(5);
        $op6 = new Operation(6);
        $op7 = new Operation(7);
        $op8 = new Operation(8);

        $dm = new DependencyManager();
        $dm->addOperation($op1);
        $dm->addOperation($op2);
        $dm->addOperation($op3);
        $dm->addOperation($op4);
        $dm->addOperation($op5);
        $dm->addOperation($op6);
        $dm->addOperation($op7);
        $dm->addOperation($op8);

        //      1     2
        //      |    / \
        //      3   4   5
        //       \ /    |
        //        6     7
        //        |
        //        8

        $dm->addDependencyByOperation($op1, $op3);
        $dm->addDependencyByOperation($op2, $op4);
        $dm->addDependencyByOperation($op2, $op5);
        $dm->addDependencyByOperation($op3, $op6);
        $dm->addDependencyByOperation($op4, $op6);
        $dm->addDependencyByOperation($op5, $op7);
        $dm->addDependencyByOperation($op6, $op8);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(2, $ops);
        $this->assertContains($op1, $ops);
        $this->assertContains($op2, $ops);

        $dm->markAsExecuted($op1);
        $dm->markAsExecuted($op2);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(3, $ops);
        $this->assertContains($op3, $ops);
        $this->assertContains($op4, $ops);
        $this->assertContains($op5, $ops);

        $dm->markAsExecuted($op3);
        $dm->markAsExecuted($op4);
        $dm->markAsExecuted($op5);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(2, $ops);
        $this->assertContains($op6, $ops);
        $this->assertContains($op7, $ops);

        $dm->markAsExecuted($op6);
        $dm->markAsExecuted($op7);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(1, $ops);
        $this->assertContains($op8, $ops);
    }

    public function testUsingTagsToDefineDependencies()
    {
        $op1 = new Operation(1);
        $op2 = new Operation(2);
        $op2->addTag('MyTag');
        $op3 = new Operation(3);
        $op3->addTag('MyTag');
        $op4 = new Operation(4);
        $op4->addTag('MyTag');
        $op5 = new Operation(5);

        $dm = new DependencyManager();
        $dm->addOperation($op1);
        $dm->addOperation($op2);
        $dm->addOperation($op3);
        $dm->addOperation($op4);
        $dm->addOperation($op5);

        //      1
        //    / | \
        //   2  3  4
        //    \ | /
        //      5

        $dm->addDependencyByTag($op1, 'MyTag');
        $dm->addDependencyByTag('MyTag', $op5);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(1, $ops);
        $this->assertContains($op1, $ops);

        $dm->markAsExecuted($op1);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(3, $ops);
        $this->assertContains($op2, $ops);
        $this->assertContains($op3, $ops);
        $this->assertContains($op4, $ops);

        $dm->markAsExecuted($op2);
        $dm->markAsExecuted($op3);
        $dm->markAsExecuted($op4);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(1, $ops);
        $this->assertContains($op5, $ops);
    }

    public function testCycleDetectionWithoutEntryPoint()
    {
        $op1 = new Operation(1);
        $op2 = new Operation(2);
        $op3 = new Operation(3);

        $dm = new DependencyManager();
        $dm->addOperation($op1);
        $dm->addOperation($op2);
        $dm->addOperation($op3);

        //      1
        //    /  \
        //   2 -- 3

        $dm->addDependencyByOperation($op1, $op2);
        $dm->addDependencyByOperation($op2, $op3);
        $dm->addDependencyByOperation($op3, $op1);

        $this->setExpectedException('MikeRoetgers\\DependencyGraph\\Exception\\CycleException');

        $dm->getExecutableOperations();
    }

    public function testCycleDetectionWithCycleWithinGraph()
    {
        $op1 = new Operation(1);
        $op2 = new Operation(2);
        $op3 = new Operation(3);
        $op4 = new Operation(4);

        $dm = new DependencyManager();
        $dm->addOperation($op1);
        $dm->addOperation($op2);
        $dm->addOperation($op3);
        $dm->addOperation($op4);

        //      1
        //      |
        //      2
        //    /  \
        //   3 -- 4

        $dm->addDependencyByOperation($op1, $op2);
        $dm->addDependencyByOperation($op2, $op3);
        $dm->addDependencyByOperation($op3, $op4);
        $dm->addDependencyByOperation($op4, $op2);

        $this->setExpectedException('MikeRoetgers\\DependencyGraph\\Exception\\CycleException');

        $dm->getExecutableOperations();
    }

    public function testMarkAsStartedFunctionality()
    {
        $op1 = new Operation(1);
        $op2 = new Operation(2);
        $op3 = new Operation(3);
        $op4 = new Operation(4);

        $dm = new DependencyManager();
        $dm->addOperation($op1);
        $dm->addOperation($op2);
        $dm->addOperation($op3);
        $dm->addOperation($op4);

        //      1
        //    /  \
        //   2    3
        //    \  /
        //     4

        $dm->addDependencyByOperation($op1, $op2);
        $dm->addDependencyByOperation($op1, $op3);
        $dm->addDependencyByOperation($op2, $op4);
        $dm->addDependencyByOperation($op3, $op4);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(1, $ops);
        $this->assertContains($op1, $ops);

        $dm->markAsStarted($op1);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(0, $ops);

        $dm->markAsExecuted($op1);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(2, $ops);
        $this->assertContains($op2, $ops);
        $this->assertContains($op3, $ops);

        $dm->markAsStarted($op2);

        $ops = $dm->getExecutableOperations();
        $this->assertCount(1, $ops);
        $this->assertContains($op3, $ops);
    }

    public function testGraphIsNotWritableAfterInitializing()
    {
        $op1 = new Operation(1);
        $op2 = new Operation(2);
        $op3 = new Operation(3);
        $op4 = new Operation(4);

        $dm = new DependencyManager();
        $dm->addOperation($op1);
        $dm->addOperation($op2);
        $dm->addOperation($op3);
        $dm->addOperation($op4);

        $dm->addDependencyByOperation($op1, $op2);
        $dm->addDependencyByOperation($op1, $op3);
        $dm->addDependencyByOperation($op2, $op4);
        $dm->addDependencyByOperation($op3, $op4);

        $dm->getExecutableOperations();

        $this->setExpectedException('MikeRoetgers\\DependencyGraph\\Exception\\GraphWriteException');

        $dm->addDependencyByOperation($op1, $op4);
    }
}


































