# RulesEngine

Proof Of Concept for a PHP Rules engine with Drools inspired rules.

Usage:
```php
        // initialise rules engines with the top of the directory containing namespaced rule files. Ex: __DIR__ . "/../Fixtures/namespaces"
       $rulesEngine = new RulesEngine(__DIR__ . "/../Fixtures/namespaces"[, EventDispatcher $eventDispatcher]);
       // add facts to provide information to the rules engine via class, array, etc.
       $rulesEngine->addFact(
       'SimpleObject', [
            $value => 0,
            $hasPassed => false
       ]);
       
       //Set the Namespace, this loads all rules  in the hierarchy of the namespace.
       $rulesEngine->setNamespace('Org\Test');

        // Optionally pass in a PSR-3 Compliant logger.
       $rulesEngine->setLogger(new Logger());

       // Run the rules on the facts
       $rulesEngine->run();      
```

Sample Rule File (.rf.yml):
```yaml
namespace: Org\Test
rules:
  'Simple Object Rule':
    when:
      # When an evaluation line is prefixed with "<key>:"  the result 
      # is stored as a fact that can be referenced later.
      - obj: 'SimpleObject' 
      - 'obj.value == 42'
    then:
      - 'SimpleObject.hasPassed = true'
  
  'Simple Object Two Rule':
    when:
      'SimpleObjectTwo.getValue() == 5555'
    then:
      - 'SimpleObjectTwo.setString("Woo!")'
```

Note: More examples can be found in the `tests/Fixtures/namespaces` directory. 

Event Listeners:

Several are triggered throughout the lifecycle of a rule.  These events currently exist in the `src/Events` folder and can be listened for by optionally passing the Symfony EventDispatcher to the constructor. Future Versions may allow for the trigger of custom events from within rules.
