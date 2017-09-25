---
title: Yii2 projects allternative organization
date: 2017-09-25 11:40
layout: post
---

[Full article] | [Russian version]

<img src="http://cdn.hiqdev.com/hiqdev/3dpuzzle.png" align="right"/>

How is it supposed to create Yii2 project now? Choose template project: basic or advanced, fork it then edit and commit right there. Wham! You've made copy-pasting! Your project and the template develop separately since now. You will not get fixes to the template. And your improvements that are specific for your tasks will not be accepted into `yii2-app-basic`. This the problem number one.

How is it supposed to evolve Yii2 project? Choose suitable extensions and plug them in with composer. Then you find example config for the extension in it's README and copy this example into your application config. Oops... You cook copypasta again! It can make troubles in many ways, e.g. in a big project many extensions can be used &mdash; application config becomes just huge and unreadable. This is the problem number two.

I cover these problems together because they are closely related.
First one can be solved by separating reusable code and turning it into an extension. But then you've got second problem &mdash; extension needs config.

These problems are most acute for repeated projects when you have to deploy many/several similar projects with big/small changes. But removing copypasta and code reuse never hurt anyone.

I want to share my solution for outlined problems.

[Full article]: /pages/articles/app-organization
[Russian version]: https://habrahabr.ru/post/329286/
