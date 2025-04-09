# 🥧 PIE (PHP Installer for Extensions)

> [!WARNING]
> PIE is still in early development. We welcome you to try this out, but please
> exercise caution when using PIE, as things may go wrong! If you do encounter
> issues, please do report an issue!

You will need PHP 8.1 or newer to run PIE, but PIE can install an extension to
any installed PHP version.

If you are an extension maintainer wanting to add PIE support to your extension,
please read [extension-maintainers](./docs/extension-maintainers.md).

## Installing PIE

### Manual installation

- Download `pie.phar` either:
  - [latest stable release](https://github.com/php/pie/releases)
  - [latest unstable nightly](https://php.github.io/pie/pie-nightly.phar)
- Verify the PHAR's source with `gh attestation verify pie.phar --repo php/pie`
- You may then invoke PIE with `php pie.phar <command>`

Further installation details can be found in the [usage](./docs/usage.md) docs.
This documentation assumes you have moved `pie.phar` into your `$PATH`, e.g.
`/usr/local/bin/pie` on non-Windows systems.

## Extensions that support PIE

A list of extensions that support PIE can be found on
[https://packagist.org/extensions](https://packagist.org/extensions).

## Installing an extension using PIE

You can install an extension using the `install` command. For example, to
install the `example_pie_extension` extension, you would run:

```shell
$ pie install example/example-pie-extension
This command may need elevated privileges, and may prompt you for your password.
You are running PHP 8.3.10
Target PHP installation: 8.3.10 nts, on Linux/OSX/etc x86_64 (from /usr/bin/php8.3)
Found package: example/example-pie-extension:1.0.1 which provides ext-example_pie_extension
phpize complete.
Configure complete.
Build complete: /tmp/pie_downloader_66e0b1de73cdb6.04069773/example-example-pie-extension-769f906/modules/example_pie_extension.so
Install complete: /usr/lib/php/20230831/example_pie_extension.so
You must now add "extension=example_pie_extension" to your php.ini
$
```

## Installing all extensions for a project

When in your PHP project, you can install any missing top-level extensions:

```
$ pie install
🥧 PHP Installer for Extensions (PIE), 0.9.0, from The PHP Foundation
You are running PHP 8.3.19
Target PHP installation: 8.3.19 nts, on Linux/OSX/etc x86_64 (from /usr/bin/php8.3)
Checking extensions for your project your-vendor/your-project
requires: curl ✅ Already installed
requires: intl ✅ Already installed
requires: json ✅ Already installed
requires: example_pie_extension ⚠️  Missing

The following packages may be suitable, which would you like to install:
  [0] None
  [1] asgrim/example-pie-extension: Example PIE extension
 > 1
   > 🥧 PHP Installer for Extensions (PIE), 0.9.0, from The PHP Foundation
   > This command may need elevated privileges, and may prompt you for your password.
   > You are running PHP 8.3.19
   > Target PHP installation: 8.3.19 nts, on Linux/OSX/etc x86_64 (from /usr/bin/php8.3)
   > Found package: asgrim/example-pie-extension:2.0.2 which provides ext-example_pie_extension
   ... (snip) ...
   > ✅ Extension is enabled and loaded in /usr/bin/php8.3

Finished checking extensions.
```

## More documentation...

The full documentation for PIE can be found in [usage](./docs/usage.md) docs.
