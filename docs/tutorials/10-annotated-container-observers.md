# Annotated Container Observers

Annotated Container has a bootstrapping observer system that allows you to get access to pertinent information during the creation of your container. This system could be used to gather information about the static analysis process, perform action on the Container post creation, or some other action that might be necessary. Below we'll talk about how to take advantage of this system to bring more complex functionality to applications powered by Annotated Container.

## Registering Observers

 First, you'll need to decide on what type of observer you want to implement. The example will implement all possible interfaces, to show what's possible. You do not have to implement all of them; you can choose any of them to implement.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalyticsObserver;use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedObserver;
use Cspray\AnnotatedContainer\Bootstrap\PostAnalysisObserver;
use Cspray\AnnotatedContainer\Bootstrap\PreAnalysisObserver;
use Cspray\AnnotatedContainer\Profiles;

final class MyContainerObserver implements PreAnalysisObserver, PostAnalysisObserver, ContainerCreatedObserver, ContainerAnalyticsObserver {

    public function notifyPreAnalysis(Profiles $profiles) : void {
        // do something before the configured directories are statically analyzed
    }

    public function notifyPostAnalysis(Profiles $profiles, ContainerDefinition $containerDefinition) : void {
        // do something after the source is analyzed and there's a ContainerDefinition
    }

    public function notifyContainerCreated(Profiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        // do something after the AnnotatedContainer has been created based off of the given ContainerDefinition 
    }
    
    public function notifyAnalytics(ContainerAnalytics $analytics) : void{
        // do something with the information about how long it took to create your container
    }

}
```

This code should live in your app's bootstrapping code, before you call `Cspray\AnnotatedContainer\Bootstrap\Bootstrap::bootstrapContainer()`.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap;

$bootstrap = new Bootstrap();
$bootstrap->addObserver(new MyContainerObserver());
$container = $bootstrap->bootstrapContainer();
```

