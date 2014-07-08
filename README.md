# Dependency Graph

[![Build Status](https://travis-ci.org/MikeRoetgers/dependency-graph.svg)](https://travis-ci.org/MikeRoetgers/dependency-graph)

This is a simple implementation of a dependency graph (directed acyclic graph). Define operations and dependencies between the different operations. The dependency manager keeps track of all dependencies and tells you which operations can be executed. This is especially convenient if you are working with long-running tasks and you want to identify which operations may run in parallel. 
 
## Example

```php
$op1 = new Operation(1);
$op2 = new Operation(2);
$op3 = new Operation(3);
$op4 = new Operation(4);

$dm = new DependencyManager();
$dm->addOperation($op1)->addOperation($op2)->addOperation($op3)->addOperation($op4);

$dm->addDependencyByOperation($op1, $op2);
$dm->addDependencyByOperation($op1, $op3);
$dm->addDependencyByOperation($op2, $op4);
$dm->addDependencyByOperation($op3, $op4);
```
This definition results in the following graph:

```
      1
    /  \
   2    3
    \  /
     4
```

Ask the dependency manager which operations can be executed. When an operation is finished, inform the dependency manager and ask for new available operations.

```php
$operations = $dm->getExecutableOperations(); // 1
$dm->markAsExecuted($op1);
$operations = $dm->getExecutableOperations(); // 2 and 3
$dm->markAsExecuted($op3);
$operations = $dm->getExecutableOperations(); // 2
$dm->markAsExecuted($op2);
$operations = $dm->getExecutableOperations(); // 4
```

More complex graphs are possible.

```
  1     2
  |    / \
  3   4   5
   \ /    |
    6     7
    |
    8
```

## Acyclicity

The graph is acyclic, which means something like this is NOT allowed:

```php
$op1 = new Operation(1);
$op2 = new Operation(2);
$op3 = new Operation(3);

$dm = new DependencyManager();
$dm->addOperation($op1)->addOperation($op2)->addOperation($op3);

$dm->addDependencyByOperation($op1, $op2);
$dm->addDependencyByOperation($op2, $op3);
$dm->addDependencyByOperation($op3, $op1);
```

```
   1
  / \
 2 – 3
```

Cycles will be detected when the graph is initialized. A CycleException will be thrown.

## Working With Tags

You can assign one or multiple tags to operations. Afterwards you can use tags to define dependencies.

```php
$setupOperation1 = new Operation('Setup1');
$setupOperation1->addTag('setup');
$setupOperation2 = new Operation('Setup2');
$setupOperation2->addTag('setup');

$downstreamOperation = new Operation('Downstream');

$dm = new DependencyManager();
$dm->addOperation($setupOperation1)->addOperation($setupOperation2)->addOperation($downstreamOperation);

$dm->addDependencyByTag('setup', $downstreamOperation); // execute all setup operations first
```

```
 Setup1    Setup2
    \       /
     \     /
      \   /
       \ /
    Downstream
```

Of course the other way around is also possible:

```php
$dm->addDependencyByTag($downstreamOperation, 'setup'); // downstream is a dependency for all operations tagged with "setup"
```

```
    Downstream
       / \
      /   \
     /     \
    /       \
 Setup1    Setup2
```