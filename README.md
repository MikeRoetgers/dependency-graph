# Dependency Graph

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

The graph is acyclic, which means something like this is NOT allowed:

```
   1
  / \
 2 – 3
```

Cycles will be detected when the graph is initialized. A CycleException will be thrown.