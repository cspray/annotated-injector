# Service Factories

Sometimes you may need to have more control over how a service is constructed. Perhaps it requires some post-construction initialization or really needs the result of some service and not the service itself. Whatever reason you might have there's a straight-forward way to have your own code construct a service instead of relying on the Container to do it.

We're going to use the same example from `/docs/tutorials/01-getting-started.md` and introduce our own factory for constructing the service. For clarity, here's the code from that tutorial.

```php
<?php

use Cspray\AnnotatedContainer\Attribute\Service;

// This is the Service that was defined in the README
#[Service]
interface BlobStorage {

    public function store(string $identifier, string $contents) : void;
    
    public function retrieve(string $identifier) : ?string;

}

// This is the new Service we're adding
#[Service]
interface BlobStorageEventEmitter {

    public function onStore(callable $listener) : void;
    
    public function onRetrieve(callable $listener) : void;
    
    public function emitStoreEvent(string $identifier) : void;
    
    public function emitRetrieveEvent(string $identifier) : void;

}

#[Service]
class StandardBlobStorageEventEmitter implements BlobStorageEventEmitter {

    private array $storeListeners = [];
    private array $retrieveListeners = [];

    public function onStore(callable $listener) : void {
        $this->storeListeners[] = $listener;
    }
    
    public function onRetrieve(callable $listener) : void {
        $this->retrieveListeners[] = $listener;
    }
    
    public function emitStoreEvent(string $identifier) : void {
        foreach ($this->storeListeners as $storeListener) {
            $storeListener($identifier);
        } 
    }
    
    public function emitRetrieveEvent(string $identifier) : void {
        foreach ($this->retrieveListeners as $retrieveListener) {
            $retrieveListener($identifier);
        }
    }

}

#[Service]
class FilesystemStorage implements BlobStorage {

    public function __construct(private BlobStorageEventEmitter $emitter) {}
    
    public function store(string $identifier, string $contents) : void {
        file_put_contents($identifier, $contents);
        $this->emitter->emitStoreEvent($identifier);
    }
    
    public function retrieve(string $identifier) : ?string {
        $contents = file_get_contents($identifier) ?? null;
        $this->emitter->emitRetrieveEvent($identifier);
        return $contents;
    }

}

```

## Our Service Factory

When we need to construct a service ourselves it is called a _service delegate_; the Container delegates the construction of the Service to the class method that's been annotated. The factory can depend on other services, either in the constructor or the method that's called to create the service. The factory needs to be constructable by the Container; providing the factory at runtime is not currently supported, so we need to be able to construct the factory ourselves. As long as your factory only depends on services or values that the Container can inject you should be good to go.

In the code our factory is gonna be responsible for adding listeners on the `BlobStorageEventEmitter` before it is passed to the constructed BlobStorage. Let's look at some code!

```php
<?php

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class BlobStorageFactory {

    public function __construct(private BlobStorageEventEmitter $emitter) {}

    #[ServiceDelegate]
    public function create() : BlobStorage {
        $this->emitter->onStore(fn() { doSomething() });
        $this->emitter->onRetrieve(fn() { doSomethingElse() });
        return new FilesystemStorage($this->emitter);
    }

}
```

And that's all there is to it! Notice that our factory is not a service itself, though it could be. Realistically, however service factory instances tend to be 1-to-1 with their corresponding service and there's no real value in having the factory stored in the container. The container knows by the annotated method what this classes intended purpose is and will construct the object and invoke the annotated method when the service is constructed. The method itself can also declare dependencies that the Container knows how to inject and they will be provided at method execution time.
