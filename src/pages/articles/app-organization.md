---
title: Yii2 projects allternative organization
---

# Yii2 projects allternative organization

<img src="http://cdn.hiqdev.com/hiqdev/3dpuzzle.png" align="right"/>

How is it supposed to create Yii2 project now? Choose template project: basic or advanced, fork it then edit and commit right there. Wham! You've made copy-pasting! Your project and the template develop separately since now. You will not get fixes to the template. And your improvements that are specific for your tasks will not be accepted into `yii2-app-basic`. This the problem number one.

How is it supposed to evolve Yii2 project? Choose suitable extensions and plug them in with composer. Then you find example config for the extension in it's README and copy this example into your application config. Oops... You cook copypasta again! It can make troubles in many ways, e.g. in a big project many extensions can be used &mdash; application config becomes just huge and unreadable. This is the problem number two.

How are these probles related? You solve first with separating reusable code and turning it into an extension. And you've got second problem &mdash; extension needs config.

These problems become most acute for repeated projects when you have to deploy many/several similar projects with big/small changes. But removing copypasta and code reuse never hurt anyone.

I want to share my solution for outlined problems.

<habracut/>

## Plugins system

So here is the solution: use plugins system &mdash; from the very beginning create a project as a plugin, split project into plugins and assemble application config automatically from configs of plugins.

I have to slow down here and explain what do I call plugin. Yii2 supports extensions and they enable organization of reusable code and plug it in to a project with composer. But any simplest extension needs a config. And the framework doesn't help here much. Author of an extension has two options:

- describe desired config in the README file and propose programmer to copy-paste it;
- use [bootstrap](http://www.yiiframework.com/doc-2.0/guide-structure-extensions.html#bootstrapping-classes) in your extension that will put desired config into application config.

I've criticized first option above. Now I'll take second:

- bootstrap is run quite early, but `Application` object is already created and it's just too late to configure certain things;
- it is rather difficult to merge with config of already created application, you'll have to work not with a whole config bit in parts: components separately (and very non trivial), aliases separately, DI container, modules, params, `controllerMap`, ... (I tried &mdash; that's not going to work);
- bootstrap is not lazy, it is run on every application request and if you have many such bootstraps they will hurt performance.

After several iterations and tried different variants I've come to a radical solution &mdash; assemble config outside of application before it starts. Hmm, sounds easy and obvious, but best idea just doesn't come first. It turned out that it is most suitable to assemble config with a composer plugin it has convenient access to all information about project dependencies. This is how [composer-config-plugin](https://github.com/hiqdev/composer-config-plugin) was created.

## Composer Config Plugin

Composer-config-plugin works rather simply:

- it scans all the project's dependecies for `config-plugin` extra option in their `composer.json`;
- it merges all the configs according to the description and packages' hierarchy;
- it writes resulting config as PHP files.

To convert an extension to plugin list desired config files in `composer.json` like this:

```json
    "extra": {
        "config-plugin": {
            "web": "src/config/web.php"
        }
    }
```

It tells composer-config-plugin to merge `src/config/web.php` contents into `web` config. And this file should contain just what plugin needs to be added into application config, e.g. internationalization config:

```php
<?php

return [
    'components' => [
        'i18n' => [
            'translations' => [
                'my-category' => [
                    'class' => \yii\i18n\PhpMessageSource::class,
                    'basePath' => '@myvendor/myplugin/messages',
                ],
            ],
        ],
    ],
];
```

There can be any number of configs including special ones: `dotenv`, `defines` и `params`. Configs are processed in the following order:

- environment variables &mdash; `dotenv`;
- constants &mdash; `defines`;
- parameters &mdash; `params`;
- all other configs, e.g.: `common`, `console`, `web`, ...

In the way that values obtained in the former steps could be used for all the latter ones.
I.e. environment variables can be used to set constants. Constants and environment variables can be used to set parameters. And the whole set of parameters, constants and environment variables can be used in configs.

And generally we're done! Composer-config-plugin just merges all the config arrays with function analogous to `yii\helpers\ArrayHelper::merge`. Configs are merged in the right order of course &mdash; considering requirements hierarchy &mdash; in the way that every package to be merged after all of its dependencies and could override its values. I.e. upmost package has full control over the config and controls all the values and plugins only provide default values. On the whole the process repeats config assembling process in `yii2-app-advanced` just on the larger scale.

To use assembled configs in application simply add these lines to `web/index.php`:

```php
$config = require hiqdev\composer\config\Builder::path('web');

(new yii\web\Application($config))->run();
```

You can find more information and examples as well as ask your questions at GitHub: [hiqdev/composer-config-plugin](https://github.com/hiqdev/composer-config-plugin).

Here is an example of a simple plugin [hiqdev/yii2-yandex-plugin](https://github.com/hiqdev/yii2-yandex-plugin). It shows advantages of this approach. To get Yandex.Metrika counter on your site it is only necessary to require the plugin and provide `yandexMetrika.id` parameter. And that's it! No need to copy-paste anything to your config, no need to add widget into layout &mdash; no need to touch your working code. Plugin is an entire piece of functionality which allows to extend system without making chages to existing code.

<img src="http://cdn.hiqdev.com/hiqdev/shtrih.png" align="right"/>

&mdash; What? One can create a new feature and don't break old ones?!<br>
&mdash; Yes.<br>
&mdash; Awesome! No need to write tests anymore?<br>
&mdash; No... That will not pass...<br>

In total, `composer-config-plugin` provides plugin system and enables reuse of smaller pieces of software. It's time to return to the main question &mdash; how to organize big reusable projects. Once again proposed solution: create project as a system of plugins organized in the proper hierarchy.

## Packages hiararchy
