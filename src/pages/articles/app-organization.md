---
title: Yii2 projects - An  alternative way to organise them
---

# Yii2 projects - An alternative way to organise them

<div align="right"><a href="https://habrahabr.ru/post/329286/">Russian version</a></div>

<img src="https://cdn.hiqdev.com/hiqdev/3dpuzzle.png" align="right"/>

How does one create a Yii2 project currently? I choose a template project: either basic or advanced, fork it, then edit and commit it, right there. Wham! I've copied and pasted it into my fork.

My project and the template I notice develop separately now. I do not get fixes to the template automatically into my project. And conversely or similarly my improvements that are specifically generated from my tasks will not be accepted into the `yii2-app-basic` template. This certainly poses the first problem with the current situation.

Currently, how does a  Yii2 project evolve? Choose suitable extensions and plug them in with composer. Then I find the example config for the extension in it's README and copy this example into my application config. Oops... I notice I am copying and pasting again! This method causes problems, e.g. in a big project many extensions can be used &mdash; the application config becomes huge and unreadable. This is the second problem.

Both these problems are covered together here because they are closely related. The first one can be solved by separating the reusable code and turning it into an extension. But then you've got a second problem &mdash; this extension needs configuring.

These problems become more acute for repeated projects when you have to deploy many/several similar projects with big/small changes. But removing the copying and pasting of code will not hurt anyone.

I want to share my solution to these outlined problems.

<habracut/>

## Plugins system

So here is the solution: Use a plugins based system &mdash; from the very beginning or outset. So yes, create a project as a plugin, split the project into plugins and assemble the application's  config automatically from these configs or plugins.

I have to slow down here and define a plugin. Yii2 supports extensions and they enable the organization of reusable code and you can plug them into a project with composer. But even the simplest extension needs a config. And the framework doesn't help here much. The author of an extension has two options:

- describe the desired config in the README file and invite programmers to copy and paste it;
- use [bootstrap](http://www.yiiframework.com/doc-2.0/guide-structure-extensions.html#bootstrapping-classes) in your extension that will put the desired config into the application config.

I've criticized the first option above. Now I'll analyse the second:

- bootstrap is run near the start, but the `Application` object by that time has already been created and it's just too late to configure certain things;
- it is particularly difficult to merge with the configuration file of the already created application since it is one progressively large file representing a progressively larger array of key-value pairs. You'll have to work not with a whole config file but with its constituent parts: components separately (and sometimes very non-trivial), aliases separately, DI container, modules, params, `controllerMap`, ... (I tried &mdash; that's not going to work);
- bootstrap is not lazy, it is run on every application request and if you have many such bootstraps they will hurt performance.

After several iterations and trying several different variants I've come to a radical solution &mdash; assemble the config outside of the application before it starts. Hmm, sounds easy and obvious, but actually this concept is not new. It turns out that this concept is especially suited to assembling configs with a composer plugin. It will have convenient access to all the information about project dependencies. This is how [composer-config-plugin](https://github.com/hiqdev/composer-config-plugin) was created.

## The New Composer Config Plugin

The new Composer-config-plugins work quite simply by:

- scanning all the project's dependencies for the `config-plugin` extra option in their `composer.json`;
- merging all the configs according to the description and packages' hierarchy;
- writing the resulting config as PHP files.

To convert an extension to a plugin, list the desired config files in `composer.json` as follows:

```json
    "extra": {
        "config-plugin": {
            "web": "src/config/web.php"
        }
    }
```

This tells the composer-config-plugin to merge the contents of the `src/config/web.php` into the `web` config. And this file should contain just what the plugin needs to be added into the application config, e.g. internationalization config:

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

There can be any number of configs including special ones: `dotenv`, `defines` . `params`. Configs are processed in the following order:

- environment variables &mdash; `dotenv`;
- constants &mdash; `defines`;
- parameters &mdash; `params`;
- all other configs, e.g.: `common`, `console`, `web`, ...

Then the values obtained in the former steps can be used for all the later ones. i.e. environment variables can be used to set constants. Constants and environment variables can be used to set parameters. And the whole set of parameters, constants and environment variables can be used in the configs.

And generally, we're done! The Composer-config-plugin just merges all the config arrays like `yii\helpers\ArrayHelper::merge`. Configs are merged in the right order of course &mdash; considering the  requirements hierarchy &mdash; in the way that every package is merged according to its dependencies with the ability to override its values. i.e. the topmost package has full control over the config. It controls all the values. The plugins only provide default values. On the whole, the process repeats the config assembling process in `yii2-app-advanced` just on a larger scale.

To use the assembled configs in an application simply add these lines to `web/index.php`:

```php
<?php
$config = require hiqdev\composer\config\Builder::path('web');

(new yii\web\Application($config))->run();
```

You can find more information and examples as well as ask your questions at GitHub: [hiqdev/composer-config-plugin](https://github.com/hiqdev/composer-config-plugin).

Here is an example of a simple plugin [hiqdev/yii2-yandex-plugin](https://github.com/hiqdev/yii2-yandex-plugin). It shows the advantages of this approach. To get Yandex.Metrika counter on your site it is only necessary to require the plugin and provide the `yandexMetrika.id` parameter. And that's it!

No need to:
- copy-paste anything to your config.
- add widgets into the layout.
- touch your working code.

The Plugin is an entire piece of functionality which allows you to extend the system without making changes to existing code.

<img src="https://cdn.hiqdev.com/hiqdev/shtrih.png" align="right"/>

&mdash; What? One can create a new feature and not break old ones?!<br>
&mdash; Yes.<br>
&mdash; Awesome! No need to write tests anymore?<br>
&mdash; No... That will not pass...<br>

In summary, the `composer-config-plugin` provides a plugin system  enabling the reuse of smaller pieces of software.

It's time to return to the main question &mdash; how to organize big reusable projects. Once again the proposed solution: Create a  project as a system of plugins organized in the proper hierarchy.

## Packages hierarchy

The simplest project structure is the following &mdash; our project requires a framework and third-party extensions with composer (I call *third-party* those extensions that are not part of our project). So we have this simple package (repositories) hierarchy:

- A project that has grown up from an application template including;
    - extensions, and a,
    - framework.

I will not burden you with all the different variants of this hierarchy that we've tried and rejected after practical operations. So  here is the optimal hierarchy we've finally decided to stick to:

- *"root"*
    - plugins that are specific for this variant of the project;
    - main project;
        - plugins of the project;
        - *third-party* plugins;
        - basic project;
            - plugins needed for the basic project;
            - framework.

This Hierarchy displays who, in a coding sense, `requires` whom, i.e. *"root"* `requires` the  main project, which in turn  `requires` the basic project, and the basic project then `requires` the framework.

&mdash; Wow-wow! Easy! What's a "root" and "basic project"?

Sorry, I've come up with all of this myself and perhaps didn't use suitable terms so I have had to improvise or invent a few terms. I'll be grateful for your suggestion of better variants of these terms.

I call *"root"* the most external package that contains code, the config and other files specifically for this particular installation and that are unique to your project &mdash; things this installation distinguishes it from the main project. Ideally it contains just a few files. More about it below.

*"Basic project"* (or basic application) is what `yii2-app-basic` turns into or develops into using this approach. i.e. it is a reusable base application that implements some basic functions arranged as a plugin. You don't have to create *"basic project"* yourself. It can be developed by a community like `yii2-app-basic`. We are developing HiSite according to this method. More about it below.

Thus packages form the hierarchy of the composition. An outer package uses the inner one mostly by reusing its behavior but redefining its own specifics; *"root"* uses and specifies the  main project and so on: main project uses the basic project; basic project &mdash; framework.

It's necessary to clarify that we are talking of code organization only, i.e. how to split code into packages/plugins.
Architectural division of code into layers is independent of division info packages of course. But they can complement each other.
e.g. domain logic can be separated into separate packages to be reused between different projects.

&mdash; Uh-oh! An Example is needed!

For example, you create a lot of simple business card websites. Basic functions are the same for all sites but you offer paid features e.g. a catalog. And sites differ in design and parameters. You could organize your code in packages forming a hierarchy this way:

- `business-card-no42.com` &mdash; *"root"*;
    - `myvendor/yii2-theme-cool` &mdash; this site specific plugin;
    - `myvendor/business-card-catalog` &mdash; project plugin, that is enabled on this site;
    - `myvendor/business-card` &mdash; main project;
        - `myvendor/business-card-contacts` &mdash; project plugin used for on all sites;;
        - `othervendor/yii2-cool-stuff` &mdash; *third party* plugin;
        - `hiqdev/hisite` &mdash; basic project;
            - `yiisoft/yii2-swiftmail` &mdash; plugin required for basic project to work;
            - `yiisoft/yii2` &mdash; framework.

I hope I have not covered or said anything new to you and that everybody can split their projects more or less in a similar way.
Or at least everybody understands the way the code is split into a hierarchy of reusable packages.
If not then you should consider this carefully. Don't put all your code into a single package copied over and over again. DRY!
But I doubt you will use "root". Now I'll try to argue its benefits to keep your code DRY.
It separates reusable code from installation-specific code.

## *"Root"*

It's adequate to put in the "root" just a couple of files fine tuned for the specific installation of this project.
It is possible and preferable to succeed with just three files:

- `.env` &mdash; environment variables, e.g.`ENV=prod`;
- `composer.json` &mdash; require the main project and it's specific plugins;
- `src/config/params.php` &mdash; password and options for project and plugins.

You can put passwords in `.env` and then use it in `params.php` like this:

```php
return [
    'db.password' => $_ENV['DB_PASSWORD'],
];
```

Considering `.env` portability parameters used by other (non PHP) technologies are the best candidates to be converted to `.env`.

Of course one may and should put some configuration and even code into the *"root"*.
But it has to be very specific for this particular installation and should not need to be copied or pasted between installations.
As soon as I see reusable copy-pasted code, I catch it and move it into some plugin.

All the other files and directories needed for an application to work, like `web/assets/`, `web/index.php` are standard and they should be created and chmoded with a  build tool or task runner.
We are reinventing [our own](https://hiqdev.com/packages/hidev) but this is quite another story.

In fact *"root"* is a `params-local.php` on steroids. It emphasizes the difference between a specific project installation and generally used code. We create a separate repository for the "root" and save it to our private git-server, so we can commit there even secrets (but this is a contentious topic). All the other packages are publicly available at GitHub. We commit the `composer.lock` file into the "root" and it enables us to move the project very easily &mdash; `composer create-project` (I know Docker is even better, but this is a topic for another article).

&mdash; Can you be more specific? Please show me the final code!

## HiSite and Asset Packagist

One  *"basic application"* we developed is **HiSite** [hiqdev/hisite](https://github.com/hiqdev/hisite) &mdash; that's a base for a typical website like `yii2-app-basic` but implemented as a plugin it gives all the advantages of code reuse over copy-pasting:

- you can base your project upon HiSite and get it's updates as it evolves;
- you can change or adapt a basic project from another basic project that is compatible but has more features.

*"Root"* template (or skeleton) is here &mdash; [hiqdev/hisite-template](https://github.com/hiqdev/hisite-template).

Hierarchy of dependencies looks like this:

- *"root"* &mdash; [hiqdev/hisite-template](https://github.com/hiqdev/hisite-template);
    - theme plugin &mdash; [hiqdev/yii2-theme-flat](https://github.com/hiqdev/yii2-theme-flat);
        - theming library &mdash; [hiqdev/yii2-thememanager](https://github.com/hiqdev/yii2-thememanager);
    - basic project &mdash; [hiqdev/hisite](https://github.com/hiqdev/hisite);
        - framework &mdash; [yiisoft/yii2](https://github.com/yiisoft/yii2).

In the [README](https://github.com/hiqdev/hisite-template) you can find out how to setup the project on your side &mdash; Simply: `composer create-project` plus configuration settings. Thanks to themes implemented as plugins and the use of the theming library [hiqdev/yii2-thememanager]  you can change `yii2-theme-flat` to `yii2-theme-original` then run `composer update` and the site will change it's clothes to the other theme. Similarily as simple as changing a  single line in `composer.json`.

There is another real working project that can be used as an example of this approach and it is completely available at GitHub.
[Asset Packagist](https://asset-packagist.org/) is a packagist-compatible repository that enables the installation of Bower and NPM packages as native Composer packages.

The Hierarchy of dependencies looks like this:

- *"root"* &mdash; [hiqdev/asset-packagist.dev](https://github.com/hiqdev/asset-packagist.dev);
    - theme plugin &mdash; [hiqdev/yii2-theme-original](https://github.com/hiqdev/yii2-theme-original);
    - project &mdash; [hiqdev/asset-packagist](https://github.com/hiqdev/asset-packagist);
        - basic project &mdash; [hiqdev/hisite](https://github.com/hiqdev/hisite);
            - framework &mdash; [yiisoft/yii2](https://github.com/yiisoft/yii2).

You can find more information on how to deploy the project on your site in the [README](https://github.com/hiqdev/asset-packagist.dev) of the *"root"* package.

## Let's sum it up

This topic is huge. I had to skip many details. I hope I've managed to give you the general idea. Once again using defined terminology:

- reuse code as plugins, i.e. code combined with the configuration;
- create a project as a hierarchy of plugins;
- separate the reusable part of the project from the specific installation with the use of "root".

We've been using this described approach for about a year already. We've created our best impression &mdash; our hairs became smooth and silky, we divided and conquered, we now create plugins simply and easily -
[100+ already](https://hiqdev.com/pages/packages)
and we are not going to stop. When we need a new functionality &mdash; we create a new plugin.

This approach is more or less suitable for other frameworks and even languages... Oops, I'm going too fast...
That's enough for today. Thank you for your attention. To be continued.

## P.S.

I was inspired to write such a volume of text by a [series](http://fabien.potencier.org/symfony4-compose-applications.html) of [articles](http://fabien.potencier.org/symfony4-monolith-vs-micro.html) of [Fabien Potencier](http://fabien.potencier.org/) (Symfony's creator) about upcoming Symfony Flex.
This new Symfony component will improve the bundles system in the direction of automatic configuration which gives:

> a new way to create and evolve your applications with ease

(c) Fabien Potencier

So I'm not alone in promoting the mentioned questions as being very important for a framework!

## P.P.S

If you want to discuss please open an issue in any of the mentioned GitHub repos.
