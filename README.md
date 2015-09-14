# RestBundle

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/RestBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/RestBundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Innmind/RestBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/RestBundle/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Innmind/RestBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/RestBundle/build-status/master)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d541a4bc-55bb-4907-9d5d-81dfa839563d/big.png)](https://insight.sensiolabs.com/projects/d541a4bc-55bb-4907-9d5d-81dfa839563d)

Wrapper for the `innmind/rest-server` library allowing you to easily expose a REST L3 API.

This bundle offer a complete integration of the library in a symfony project. It also add a new feature called server capabilities; put simply, it expose a route `OPTIONS *` that will output the list of routes exposed via this bundle. The goal being to allow client discovery, you could imagine a REST client that could prefetch all the resources definitions so it could know in advance if the resources it will try to send match the definitions.

## Installation

```sh
composer require innmind/rest-bundle
```

Enable the bundle by adding the following line in your `app/AppKernel.php` of your project:

```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Innmind\RestBundle\InnmindRestBundle,
        );
        // ...
    }
    // ...
}
```

Then specify your resources in the configuration under:

```yaml
innmind_rest:
    server:
        collections: [] #same configuration as the rest server library
        prefix: /api/prefix #optional
```

## Storage

To define a storage you can create a service having either `innmind_rest.server.storage.abstract.doctrine` or `innmind_rest.server.storage.abstract.neo4j` as parent. Then you need to specify the first argument to construct the service, being an instance of an entity manager (a doctrine or neo4j one); and flag the service with the tag `innmind_rest.server.storage`, the bundle will look for the attribute `alias` on this tag to use as reference afterward (name used to specify storage on your resources).

## Formats

As allowed formats are handled via encoders, you declare new ones with a tag on the encoder service you want to add.

Example of the built-in `json` format:

```yaml
innmind_rest.encoder.json:
    class: Innmind\Rest\Server\Serializer\Encoder\JsonEncoder
    tags:
        - { name: serializer.encoder }
        - { name: innmind_rest.server.format, format: json, mime: application/json, priority: 10 }
```

## Events

In most cases the only event you'll want to alter will be [`Events::RESPONSE`](https://github.com/Innmind/rest-server/blob/master/Events.php#L18) or `Events::{STORAGE}_READ_QUERY_BUILDER` (`STORAGE` can be `DOCTRINE` or `NEO4J`) to add restriction on the query like for example the user being connected.

You can look at [`Events.php`](https://github.com/Innmind/rest-server/blob/master/Events.php) to review all the events you have at your disposition.

**Note**: the event `REQUEST` is not used in this bundle, instead it relies on the symfony `KernelEvents::REQUEST`.
