# Hypernova-PHP

> PHP client for your [Hypernova service](https://github.com/airbnb/hypernova).

## Getting Started

`composer install wayfair/hypernova-php`

Or, `git clone` the repo somewhere `&& cd hypernova-php && composer install`.

Make a `Renderer`:

```
use \WF\Hypernova\Renderer;

$renderer = new Renderer('http://localhost:3030/batch');
```

Give it some work:

```
$renderer->addJob('myViewId', ['name' => 'my_module_name', 'data' => ['some' => ['props']]);
```

Optionally add a plugin or two (see plugin section):

```
$renderer->addPlugin($myPlugin);
$renderer->addPlugin($myOtherPlugin);
```

Then go get your rendered `Response`:

```
$response = $renderer->render();
```

## Plugin API

This is how you customize client behavior.  Common usecases include:

* Logging request metadata like performance timings
* Error logging
* Injecting/removing props
* Inlining stack traces in development environments
* Stopping requests to the service entirely, letting everything fall back to client rendering

Generally, you will want to implement some subset of the lifecycle hooks; maybe you
want `onError` handling but have no need for `shouldSendRequest`.  For 
developer convenience, you may extend `\WF\Hypernova\Plugin\BasePlugin` which
provides no-op implementations of all of the hooks.

TODO: write up API docs.  Reference https://github.com/airbnb/hypernova-node#plugin-lifecycle-api

#### Contributing:

Fork it, submit a PR.

#### Run tests:

`./vendor/bin/phpunit`
