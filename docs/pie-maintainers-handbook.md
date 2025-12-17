---
title: PIE Maintainers Handbook
order: 3
---
# PIE Maintainers Handbook

## Branching strategy

Since 1.3.0, we operate a branch per minor release, with an `x` for the patch
version, for example, 1.3 series branch is named `1.3.x`, 1.4 is named `1.4.x`
and so on. This allows releasing patch versions on older releases if a bug is
found, for example.

### New features

New feature branches should be based on the latest trunk (i.e. the default
branch). Once merged, that feature will be part of the next minor release.

### Bugfixes/Patches

Bugfixes/patches should be based on the oldest supported or desired patch
version. For example, if a bug affects the 1.3 series, a PR should be made
from the feature branch to the `1.3.x` branch.

## Release process

Make sure you have the latest version of the trunk to be released, for example,
one of:

```shell
# Using git reset (note: discards any local commits on `1.3.x`)
git checkout 1.3.x && git fetch upstream && git reset --hard upstream/1.3.x
# or, using git pull (note: use `--ff-only` to avoid making merge commits)
git checkout 1.3.x && git pull --ff-only upstream 1.3.x
```

Prepare a changelog, set the version and milestone to be released, e.g.:

```shell
PIE_VERSION=1.3.0
PIE_MILESTONE=$PIE_VERSION
```

> [!TIP]
> For pre-releases, you can set the version/milestone to be different, e.g.:
>
> ```shell
> PIE_VERSION=1.3.0-alpha.2
> PIE_MILESTONE=1.3.0
> ```
>
> This will tag/release with the `1.3.0-alpha.2` version, but will generate the
> changelog based on the `1.3.0` milestone in GitHub.

Then generate the changelog file:

```shell
composer require --dev -W jwage/changelog-generator --no-interaction
vendor/bin/changelog-generator generate --user=php --repository=pie --milestone=$PIE_MILESTONE > CHANGELOG-$PIE_VERSION.md
git checkout -- composer.*
composer install
```

Check you are happy with the contents of the changelog. Create a signed tag:

```shell
git tag -s $PIE_VERSION -F CHANGELOG-$PIE_VERSION.md
git push upstream $PIE_VERSION
```

The release pipeline will run, which will create a **draft** release, build the
PHAR file, and attach it. You must then go to the draft release on GitHub,
verify everything is correct, and publish the release.

```shell
rm CHANGELOG-$PIE_VERSION.md
```

### Minor or Major releases: updating branches

Once a minor or major release is made, a new trunk should be created. For
example, if you just released `1.3.0` from the `1.3.x` branch, you should then
create a new `1.4.x` branch, and set that as the default.
