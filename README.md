# Hypernova-PHP [![Build Status](https://travis-ci.org/wayfair/hypernova-php.svg?branch=master)](https://travis-ci.org/wayfair/hypernova-php) [![codecov](https://codecov.io/gh/wayfair/hypernova-php/branch/master/graph/badge.svg)](https://codecov.io/gh/wayfair/hypernova-php)


> PHP client for your [Hypernova service](https://github.com/airbnb/hypernova).

## Why Hypernova?

The broader question tends to be "how do I Server-Side Render my React app?"  You may have this as a business requirement (e.g. SEO) or just want to give users the fastest initial render possible.

Assuming you have a PHP backend (why are you here, otherwise?), generally you will want to stand up a node.js service to do the rendering for you.  You could _try_ [phpv8js](https://github.com/phpv8/v8js) but I believe it is contraindicated for production use at any scale.  That's just my opinion, do your own research :grin:

So then - write your own node.js service, or use one off the shelf.  Writing your own node.js service isn't terrifically hard - you could reasonably stand up a thing that would render react components for you in ~20 lines of code.  We personally went with hypernova because it's lightweight, pluggable (see the plugin system), performant (see the clever bytecode caching in `createVM`), and has nice client-side fallback behavior in case the service has issues.

## Getting Started

`composer require wayfair/hypernova-php`

Make a `Renderer`:

```
use \WF\Hypernova\Renderer;

$renderer = new Renderer('http://localhost:3030/batch');
```

Give it some work:

```
$renderer->addJob('myViewId', ['name' => 'my_module_name', 'data' => ['some' => ['props']]]);
```

Optionally add a plugin or two (see plugin section):

```
$renderer->addPlugin($myPlugin);
$renderer->addPlugin($myOtherPlugin);
```

Then go get your rendered `Response`:

```
$response = $renderer->render();

echo $response->results['myViewId']->html;
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

See the [js client docs](https://github.com/airbnb/hypernova-node#plugin-lifecycle-api) for full descriptions of the available hooks.

#### Contributing:

Fork it, submit a PR.

#### Run tests:

`composer test`
