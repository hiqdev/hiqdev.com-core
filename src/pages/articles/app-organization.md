---
title: Yii2 projects - An  alternative organization regime
---

# Yii2 projects - An alternative organization regime

<div align="right"><a href="https://habrahabr.ru/post/329286/">Russian version</a></div>

<img src="https://cdn.hiqdev.com/hiqdev/3dpuzzle.png" align="right"/>

How does one create a Yii2 project currently? I choose a template project: either basic or advanced, fork it then edit and commit it right there. Wham! I've copied and pasted it into my fork. My project and the template I notice develop separately now. I do not get fixes to the template automatically into my project. And conversely or similarly my improvements that are specifically generated from my tasks will not be accepted into the `yii2-app-basic` template. This certainly poses the first problem with the current situation.

Currently how does a  Yii2 project evolve now? Choose suitable extensions and plug them in with composer. Then I find the example config for the extension in it's README and copy this example into my application config. Oops... I notice I am copying and pasting again! This method causes problems, e.g. in a big project many extensions can be used &mdash; the application config becomes huge and unreadable. This is the second problem.
Both these problems are covererd together here because they are closely related.
The first one can be solved by separating reusable code and turning it into an extension. But then you've got a second problem &mdash; this extension needs configuring.

These problems become more acute for repeated projects when you have to deploy many/several similar projects with big/small changes. But removing the copying and pasting of code will not hurt anyone.

I want to share my solution for the outlined problems.

<habracut/>

## Plugins system

So here is the solution: Use a plugins based system &mdash; from the very beginning or outset. So yes, create a project as a plugin, split the project into plugins and assemble application config automatically from these configs of plugins.

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

Then values obtained in the former steps could be used for all the later ones.
I.e. environment variables can be used to set constants. Constants and environment variables can be used to set parameters. And the whole set of parameters, constants and environment variables can be used in configs.

And generally we're done! Composer-config-plugin just merges all the config arrays with function analogous to `yii\helpers\ArrayHelper::merge`. Configs are merged in the right order of course &mdash; considering requirements hierarchy &mdash; in the way that every package is merged after all of its dependencies and can override its values. I.e. upmost package has full control over the config and controls all the values and plugins only provide default values. On the whole the process repeats config assembling process in `yii2-app-advanced` just on the larger scale.

To use assembled configs in application simply add these lines to `web/index.php`:

```php
<?php
$config = require hiqdev\composer\config\Builder::path('web');

(new yii\web\Application($config))->run();
```

You can find more information and examples as well as ask your questions at GitHub: [hiqdev/composer-config-plugin](https://github.com/hiqdev/composer-config-plugin).

Here is an example of a simple plugin [hiqdev/yii2-yandex-plugin](https://github.com/hiqdev/yii2-yandex-plugin). It shows advantages of this approach. To get Yandex.Metrika counter on your site it is only necessary to require the plugin and provide `yandexMetrika.id` parameter. And that's it! No need to copy-paste anything to your config, no need to add widget into layout &mdash; no need to touch your working code. Plugin is an entire piece of functionality which allows to extend system without making chages to existing code.

<img src="https://cdn.hiqdev.com/hiqdev/shtrih.png" align="right"/>

&mdash; What? One can create a new feature and don't break old ones?!<br>
&mdash; Yes.<br>
&mdash; Awesome! No need to write tests anymore?<br>
&mdash; No... That will not pass...<br>

In total, `composer-config-plugin` provides plugin system and enables reuse of smaller pieces of software. It's time to return to the main question &mdash; how to organize big reusable projects. Once again proposed solution: create project as a system of plugins organized in the proper hierarchy.

## Packages hiararchy

Simpliest project structure is the following &mdash; our project requires framework and third party extensions with composer (I call *third party* those extensions that are not part of our project). So we have this simple packages (repositories) hierarchy:

- project that has grown up from an application template;
    - extensions;
    - framework.

I will not burden you with all the different variants of hierarchy that we've tried and rejected after practical operations. And here is the optimal hierarchy we've finally stick to:

- *"root"*
    - plugins that are specific for this variant of project;
    - main project;
        - plugins of project;
        - *third-party* plugins;
        - basic project;
            - plugins needed for basic project;
            - framework.

Hierarchy displays who requires whom, i.e. *"root"* requires main project, which in turn requires basic project, and basic project requires framework.

&mdash; Wow-wow! Easy! What's a "root" and "basic project"?

Sorry, I've come up to all this myself and didn't find suitable terminology so I had to invent. I'll be grateful for better variants.

I call *"root"* the most external package that containts code, config and other files specifical for this particular installation of your project &mdash; things this installation is different from main project. Ideally it contains just a few files, more about it below.

*"Basic project"* (or basic application) is what `yii2-app-basic` turns into using this approach. I.e. it is reusable application basis implementing some basic functions and arranged as a plugin. You don't have to create *"basic project"* yourself. It can be developped by a community like `yii2-app-basic`. We are developing HiSite, more about it below.

Thus packages form hierarchy of composition. An outer package uses inner one mostly reusing its behavior but redifining own specifics; *"root"* uses and specifies main project and so on: main project uses basic project; basic project &mdash; framework.

It's necessary to clarify that we are talking of code organization only, i.e. how to split code into packages/plugins.
Architectural division of code into layers is independent of division info packages of course. But the can complement each outher.
E.g. domain logic can be taken away into separate package to be reused between different projects.

&mdash; Uh-oh! Example needed!

For example you create a lot of simple business card websites. Basic functions are the same for all sites but you offer paid features e.g. catalog. And sites differ in design and parameters. You could organize your code in packages forming hierarchy this way:

- `business-card-no42.com` &mdash; *"root"*;
    - `myvendor/yii2-theme-cool` &mdash; this site specific plugin;
    - `myvendor/business-card-catalog` &mdash; project plugin, that is enabled on this site;
    - `myvendor/business-card` &mdash; main project;
        - `myvendor/business-card-contacts` &mdash; project plugin used for on all sites;;
        - `othervendor/yii2-cool-stuff` &mdash; *third party* plugin;
        - `hiqdev/hisite` &mdash; basic project;
            - `yiisoft/yii2-swiftmail` &mdash; plugin required for basic project to work;
            - `yiisoft/yii2` &mdash; framework.

Hope I didn't said anything new for you and everybody split their projects more or less in similar way.
Or at least everybody understand that it is the way the code should be split into hierarchy of reusable packages.
If not then you should consider it definetely. Don't put all your code in a single package copied over and over again. DRY!
But I doubt you use "root". Now I'll try to argue its benefits to keep you code DRY.
It splits reusable code from installation specific code.

## *"Root"*

It's enough to put in the "root" just a couple of files tuned for this specific installation of project.
It is possible and preferable to succeed with just three files:

- `.env` &mdash; environment variables, e.g.`ENV=prod`;
- `composer.json` &mdash; require main project and it's specific prlugins;
- `src/config/params.php` &mdash; password and options for project and plugins.

You can put passwords in `.env` and then use it in `params.php` like this:

```php
return [
    'db.password' => $_ENV['DB_PASSWORD'],
];
```

Considering `.env` portability parameters used by other (non PHP) technologies are best candidates to be put to `.env`.

Of course one may and should put some configuration and even code into the *"root"*.
But it has to be very specific for this particular installation and should not be copy pasted between installation.
As soon as I see a copy-pasted code I catch it and move into some plugin.

All the other files and directories needed for application to work, like `web/assets/`, `web/index.php` are standard and they should be created and chmoded with build tool or task runner.
We are reinventing [our own](https://hiqdev.com/packages/hidev) but this is quite another story.

In fact *"root"* is `params-local.php` on steroids. It concentrates difference between specific project installation and generally used code. We create separate repository for "root" and save it to our private git-server, so we can commit there even secrets (but this is holy war topic). All the other packages are publicly available at GitHub. We commit `composer.lock` file into the "root" and it enables us to move the project very easily &mdash; `composer create-project` (I know Docker is even better, but this is a topic for another article).

&mdash; Can you be more specific? Show me the code finally!

## HiSite and Asset Packagist

One of *"basic application"* we develop is **HiSite** [hiqdev/hisite](https://github.com/hiqdev/hisite) &mdash; that's a base for a typical website like `yii2-app-basic` but implemented as plugin that gives all advantages of code reuse over copy-pasting:

- you can base your project upon HiSite and get it's updates as it evolves;
- you can change basic project for another basic project that is compatible but has more features.

*"Root"* template (or skeleton) is here &mdash; [hiqdev/hisite-template](https://github.com/hiqdev/hisite-template).

Hierarchy of dependencies looks like this:

- *"root"* &mdash; [hiqdev/hisite-template](https://github.com/hiqdev/hisite-template);
    - theme plugin &mdash; [hiqdev/yii2-theme-flat](https://github.com/hiqdev/yii2-theme-flat);
        - theming library &mdash; [hiqdev/yii2-thememanager](https://github.com/hiqdev/yii2-thememanager);
    - basic project &mdash; [hiqdev/hisite](https://github.com/hiqdev/hisite);
        - framework &mdash; [yiisoft/yii2](https://github.com/yiisoft/yii2).

In the [README](https://github.com/hiqdev/hisite-template) you can find how to setup the project on your side &mdash; easy enough: `composer create-project` plus configuration settings. Thanks to themes implemented as plugins and use of the theming library [hiqdev/yii2-thememanager]  you can change `yii2-theme-flat` to `yii2-theme-original` then run `composer update` and the site will change it's clothes to other theme. As simple as change single line in `composer.json`.

There is another real working project that can be used as example of this approach and it is completely available at GitHub.
[Asset Packagist](https://asset-packagist.org/) is packagist-compatible repository that enables installation of Bower and NPM packages as native Composer packages.

Hierarchy of dependencies looks like this:

- *"root"* &mdash; [hiqdev/asset-packagist.dev](https://github.com/hiqdev/asset-packagist.dev);
    - theme plugin &mdash; [hiqdev/yii2-theme-original](https://github.com/hiqdev/yii2-theme-original);
    - project &mdash; [hiqdev/asset-packagist](https://github.com/hiqdev/asset-packagist);
        - basic project &mdash; [hiqdev/hisite](https://github.com/hiqdev/hisite);
            - framework &mdash; [yiisoft/yii2](https://github.com/yiisoft/yii2).

You can find more information on how to deploy the project on your site in the [README](https://github.com/hiqdev/asset-packagist.dev) of the *"root"* package.

## Let's sum it up

The topic is huge. I had to skip many details. Hope I've managed to bring the general idea. Once again using defined terminology:

- reuse code as plugins, i.e. code combined with configuration;
- create project as hierarchy of plugins;
- separate reusable part of project from specific installation with use of "root".

We've been using described approach about a year already. We've got best impressions &mdash; our hairs became smooth and silky, we divide and conquer, we create plugins simply and easily -
[100+ already](https://hiqdev.com/pages/packages)
and we are not going to stop. When we need a new functionality &mdash; we create a new plugin.

This approach is more or less suitable for other frameworks and even languages... Oops, I'm going too fast...
That's enough for today. Thank you for your attention. To be continued.

## P.S.

I was inspired to write such a volume of text by a [series](http://fabien.potencier.org/symfony4-compose-applications.html) of [articles](http://fabien.potencier.org/symfony4-monolith-vs-micro.html) of [Fabien Potencier](http://fabien.potencier.org/) (Symfony's creator) about upcoming Symfony Flex.
This new Symfony component will improve bundles system in the direction of automatic configuration which gives:

> new way to create and evolve your applications with ease

(c) Fabien Potencier

So I'm not alone to consider mentioned questions very important for a framework.

## P.P.S

If you want to discuss please open issue in any of the mentioned GitHub repos.
