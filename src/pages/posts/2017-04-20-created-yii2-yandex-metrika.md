---
title: Created yii2-yandex-metrika
date: 2017-04-20 20:07
layout: post
---

https://hiqdev.com/packages/yii2-yandex-metrika

Provides really easy adding [Yandex.Metrika] counter to site.
Even easier then adding a widget into layout.

Works by adding Behavior to the Application View.
Behavior listens to [EVENT_END_BODY] and echos the counter script.

[Yandex.Metrika]: https://metrika.yandex.ru
[EVENT_END_BODY]: http://www.yiiframework.com/doc-2.0/yii-web-view.html#EVENT_END_BODY-detail
