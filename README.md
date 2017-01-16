## PHP client for hypernova

Towards making a useful PHP client for the Hypernova platform:

https://github.com/airbnb/hypernova/blob/master/docs/client-spec.md

Example might look like:

```
$renderer = new Renderer('http://path/to/service');
$renderer->addJob('myView', ['name' => 'myFirstComponent', 'data' => ['some' => 'props']]);
$renderer->addJob('myOtherView', ['name' => 'mySecondComponent', 'data' => ['some' => 'props']]);

var_dump($renderer->render()); // inspect the shape of the results
```

Naming of the public API is still in flux, this thing really isn't a "renderer."  Naming things is hard.


Contributing:

Fork it, submit a PR.  TODO: contributing guide

Using it:

`composer install` (not in packagist yet, I'll get around to it)

Run tests:

`./vendor/bin/phpunit`
