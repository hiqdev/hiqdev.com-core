---
title: Yii2 projects alternative organization
date: 2017-09-25 11:40
layout: post
url: /pages/articles/app-organization
---

<img src="https://cdn.hiqdev.com/hiqdev/3dpuzzle.png" align="right"/>

How does one create a Yii2 project currently? I choose a template project: either basic or advanced, fork it, then edit and commit it, right there. Wham! I've copied and pasted it into my fork.

My project and the template I notice develop separately now. I do not get fixes to the template automatically into my project. And conversely or similarly my improvements that are specifically generated from my tasks will not be accepted into the `yii2-app-basic` template. This certainly poses the first problem with the current situation.

Currently how does a  Yii2 project evolve? Choose suitable extensions and plug them in with composer. Then I find the example config for the extension in it's README and copy this example into my application config. Oops... I notice I am copying and pasting again! This method causes problems, e.g. in a big project many extensions can be used &mdash; the application config becomes huge and unreadable. This is the second problem.

Both these problems are covererd together here because they are closely related. The first one can be solved by separating reusable code and turning it into an extension. But then you've got a second problem &mdash; this extension needs configuring.

These problems become more acute for repeated projects when you have to deploy many/several similar projects with big/small changes. But removing the copying and pasting of code will not hurt anyone.

I want to share my solution to these outlined problems.

[Full article] | [Russian version]

[Full article]: /pages/articles/app-organization
[Russian version]: https://habrahabr.ru/post/329286/
