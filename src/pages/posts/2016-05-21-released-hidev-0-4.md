---
layout: post
title: Released HiDev 0.4.0
date: 2016-05-21 15:00
---

[https://github.com/hiqdev/hidev](https://github.com/hiqdev/hidev)

- Changed: redone to `composer-config-plugin`
- Changed: greatly improved functional tests
- Fixed minor issues
- Added sudo modifier
- Added `@root` instead of `@prjdir`
- Added `hidev help`
- Added copying in `FileController`
- Changed `require:` option to `plugins:`
- Added `CommandController`
- Added `dump/internals` action
- Changed to use `hiqdev/composer-extension-plugin` instead of PluginManager
- Added `github/create` and `github/exists` actions
- Changed back to yii2 <- minii, used `asset-packagist.hiqdev.com` repository
- Added loading of project's own bootstrap and config
- Added better defaults when package name is domain
- Changed github `name` -> `full_name` to correspond github api
- Fixed scrutinizer issues
- Added smart vendor require in `hidev/init`
- Fixed `bump` and `bump/release`
- Added easy creation of templated dirs and files with `DirectoryController`
- Fixed `JsonHandler` to parse empty JSON to empty array (died before)

