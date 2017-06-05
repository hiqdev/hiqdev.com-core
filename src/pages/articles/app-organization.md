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

Самый простой вариант организации проекта такой &mdash; наш проект реквайрит композером фреймворк и *сторонние* расширения (*"сторонними"* я называю не являющиеся частью нашего проекта), т.е. получается такая простая иерархия пакетов (репозиториев):

- project that has grown up from application template;
    - extensions;
    - framework.

Пропускаю все промежуточные варианты организации, проверенные и отброшенные по итогам практической эксплуатации, и перехожу сразу к оптимальной иерархии, которой мы придерживаемся сейчас:

- *"root"*
    - plugins specific for these variant of project;
    - main project;
        - plugins of project;
        - *third-party* plugins;
        - basic project;
            - plugins needed for basic project;
            - framework.

Иерархия отображает кто кого реквайрит, т.е. корень реквайрит основной проект, тот в свою очередь &mdash; базовый проект, а базовый проект &mdash; фреймворк.

&mdash; Воу-воу! Полегче! Что за "корень" и "базовый проект"?

Извиняюсь, всё придумал сам, терминологии подходящей не нашёл, пришлось велосипедить, буду признателен за лучшие варианты.

*"Корнем"* я называю самый внешний пакет, содержащий код, конфиг и другие файлы специфические для данного конкретного варианта реализации вашего проекта &mdash; то, чем этот вариант отличается от основного проекта.  В идеале содержит буквально несколько файлов, об этом ниже.

*"Базовый проект"* это то, во что превращается `yii2-app-basic` в этой схеме.  Т.е. переиспользуемая основа приложения реализующая некоторый базовый функционал и оформленная в виде плагина.  Эта запчасть не обязательна, но очень полезна. Вам не надо её делать самому, она может разрабатываться сообществом как сейчас разрабатывается `yii2-app-basic`.  Мы разрабатываем HiSite, об этом ниже.

Таким образом пакеты образуют иерархию композиции &mdash; более внешний пакет использует внутренний, в основном переиспользуя его поведение, но переопределяя свою специфику: *"корень"* использует и уточняет основной проект, основной проект &mdash; базовый, базовый проект &mdash; фреймворк.

Необходимо уточнить, что речь идёт только об организации кода, т.е. про разделение кода по пакетам/плагинам.  Архитектурное деление проекта на слои, естественно, независимо от деления на пакеты, но они могут дополнять друг друга. Например, доменная логика может быть вынесена в отдельный пакет для переиспользования между разными проектами.

&mdash; Аааа! Нужен пример!

Например, Вы делаете на потоке сайты визитки. Базовый функционал везде одинаковый, но есть фичи за дополнительну плату, например каталог и, естественно, сайты отличаются внешним видом (темой) и кучей параметров.  Это можно организовать в такую иерархию пакетов:

- `business-card-no42.com` &mdash; *"корень"*;
    - `myvendor/yii2-theme-cool` &mdash; плагин, специфичный для данного сайта;
    - `myvendor/business-card-catalog` &mdash; плагин проекта, подключенный на данном сайте;
    - `myvendor/business-card` &mdash; основной проект;
        - `myvendor/business-card-contacts` &mdash; плагин проекта, используемый на всех сайтах;
        - `othervendor/yii2-cool-stuff` &mdash; *сторонний* плагин;
        - `hiqdev/hisite` &mdash; базовый проект;
            - `yiisoft/yii2-swiftmail` &mdash; плагин, необходимый для работы базового проекта;
            - `yiisoft/yii2` &mdash; фреймворк.

Надеюсь, не открыл Америки, и все более менее так и делят свои проекты на части.  Только, наверно, без *"корня"*. Попытаюсь донести его полезность.

## *"Root"*

В *"корне"* достаточно всего пару файлов, которые подлежат копированию из шаблона и настройке под данную инсталяцию проекта. Можно и желательно обойтись всего тремя файлами:

- `.env` &mdash; переменные окружения, например,`ENV=prod`;
- `composer.json` &mdash; тут подключается основной проект и специфичные для него плагины;
- `src/config/params.php` &mdash; явки, пароли, параметры проекта и используемых плагинов.

Пароли можно положить в `.env` и потом использовать их в `params.php` так:

```php
return [
    'db.password' => $_ENV['DB_PASSWORD'],
];
```

Учитывая "легкоусвояемость" `.env` лучшими претендентами на вынос в `.env` являются параметры используемые другими (не PHP) технологиями.

Конечно, можно и нужно класть в *"корень"* некоторый конфиг и даже код, специфичный сугубо для данной инсталяции, не подлежащий копипастингу.  Как только вижу копипасту, страшно её не люблю &mdash; уношу в какой-нибудь плагин.

Остальные файлы и каталоги необходимые для функционирования приложения (`web/assets/`, `web/index.php`) стандартны, их нужно создавать и назначать права "сборщиком" (build tool, task runner) мы велосипедим свой, но это уже совсем другая история.

По сути, *"корень"* &mdash; это `params-local.php` на стероидах.  В нём концентрируется отличие конкретной инсталяции проекта от общего переиспользуемого кода. Мы создаём репозиторий под корень и храним его на нашем приватном git-сервере, поэтому комитим туда даже секреты (но это холиварная тема).  Все остальные пакеты &mdash; в публичном доступе на GitHub.  Мы комитим `composer.lock` в корне, поэтому перенос проекта на другой сервер делается просто `composer create-project` (я знаю &mdash; Docker получше будет, но об этом в следующий раз).

&mdash; А можно ещё конкретнее? Покажите мне код наконец!

## HiSite and Asset Packagist

Одно из *"базовых приложений"*, которые мы развиваем &mdash; **HiSite** [hiqdev/hisite](https://github.com/hiqdev/hisite) &mdash; это основа для типичного сайта, как `yii2-app-basic,` только сделанная как плагин, что даёт все преимущества переиспользования кода над копипастингом:

- можно основать свой проект на HiSite и получать его обновления;
- можно со временем заменить базовый проект на другой, совместимый, но, например, с большим функционалом.

Шаблон *"корня"* для проекта на HiSite здесь &mdash; [hiqdev/hisite-template](https://github.com/hiqdev/hisite-template).

Иерархия зависимостей выглядит так:

- *"корень"* &mdash; [hiqdev/hisite-template](https://github.com/hiqdev/hisite-template);
    - плагин темы &mdash; [hiqdev/yii2-theme-flat](https://github.com/hiqdev/yii2-theme-flat);
        - библиотека тем &mdash; [hiqdev/yii2-thememanager](https://github.com/hiqdev/yii2-thememanager);
    - базовый проект &mdash; [hiqdev/hisite](https://github.com/hiqdev/hisite);
        - фреймворк &mdash; [yiisoft/yii2](https://github.com/yiisoft/yii2).

В [README](https://github.com/hiqdev/hisite-template) корня описано как поднять проект у себя &mdash; `composer create-project` плюс настройка конфигурации.  Благодаря реализации тем как плагинов и библиотеке тем [hiqdev/yii2-thememanager] в `composer.json` корня можно поменять `yii2-theme-flat` на `yii2-theme-original` запустить `composer update` и сайт переоденется в новую тему. Вот так просто.

Ещё один реальный рабочий проект, подходящий в качестве примера, сделанный, используя этот подход и полностью доступный на GitHub &mdash; [Asset Packagist](https://asset-packagist.org) &mdash; packagist-совместимый репозиторий, который позволяет устанавливать Bower и NPM пакеты как нативные composer пакеты.

Hierarchy of ependencies looks like this:

- *"root"* &mdash; [hiqdev/asset-packagist.dev](https://github.com/hiqdev/asset-packagist.dev);
    - theme plugin &mdash; [hiqdev/yii2-theme-original](https://github.com/hiqdev/yii2-theme-original);
    - project &mdash; [hiqdev/asset-packagist](https://github.com/hiqdev/asset-packagist);
        - basic project &mdash; [hiqdev/hisite](https://github.com/hiqdev/hisite);
            - framework &mdash; [yiisoft/yii2](https://github.com/yiisoft/yii2).

You can find more information on how to deploy the project on your site in the [README](https://github.com/hiqdev/asset-packagist.dev) of the *"root"* package.

## Let's sum it up

Тема обширная, множество подробностей пришлось опустить.  Надеюсь получилось донести общую идею. Ещё раз, используя введенную терминологию:

- переиспользуем код в виде плагинов, т.е. код вместе с конфигурацией;
- создаём проект как иерархию плагинов;
- отделяем переиспользуемую часть проекта от конкретной инсталяции с помощью "корня".

Мы используем описанный подход около года, впечатления самые положительные &mdash; волосы стали мягкие и шелковистые: разделяем и властвуем, клепаем плагины легко и непринуждённо, [100+](https://hiqdev.com/packages) и останавливаться не собираемся, нужен новый функционал &mdash; делаем новый плагин.

Подход, в той или иной мере, применим для других фреймворков и даже языков...  Ой, Остапа понесло... На сегодня хватит!  Спасибо за внимание.  Продолжение следует.

## P.S.

На написание таких объёмов текста сподвигла [серия](http://fabien.potencier.org/symfony4-compose-applications.html) [статей](http://fabien.potencier.org/symfony4-monolith-vs-micro.html) [Фабьена Потенсьера](http://fabien.potencier.org/) (автора Symfony) про грядущий Symfony 4.  Стыдно сказать, не до конца понял как именно всё работает, но уловил идеи и цели: система бандлов будет доработана в сторону их автоматической конфигурации, что в итоге даёт:

> new way to create and evolve your applications with ease

(c) Fabien Potencier

So, I'm not alone to evaluate mentioned questions very important for a framework.

Я люблю Yii. Давайте сделаем в Yii лучше!

