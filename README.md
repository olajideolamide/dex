<p align="center">
    <img src=".github/assets/github_banner-1500.png" alt="DEX - Follow every request. Find what broke.">
</p>

<h1 align="center">DEX</h1>

<p align="center">
    An open-source, self-hosted issue tracker and request monitor for CodeIgniter 4 applications.
</p>

<p align="center">
    <a href="https://packagist.org/packages/olajideolamide/dex"><img alt="Latest Version on Packagist" src="https://img.shields.io/packagist/v/olajideolamide/dex.svg?style=flat-square"></a>
    <a href="LICENSE"><img alt="License" src="https://img.shields.io/badge/license-MIT-DD4814.svg?style=flat-square"></a>
    <a href="composer.json"><img alt="PHP Version" src="https://img.shields.io/badge/php-%3E%3D%208.2-777bb4.svg?style=flat-square"></a>
    <a href="https://codeigniter.com/"><img alt="CodeIgniter 4" src="https://img.shields.io/badge/CodeIgniter-4-DD4814.svg?style=flat-square"></a>
    <a href="https://github.com/olajideolamide/dex/actions/workflows/phpunit.yml"><img alt="PHPUnit" src="https://github.com/olajideolamide/dex/actions/workflows/phpunit.yml/badge.svg?branch=main"></a>
    <a href="https://github.com/olajideolamide/dex/actions/workflows/phpstan.yml"><img alt="PHPStan" src="https://github.com/olajideolamide/dex/actions/workflows/phpstan.yml/badge.svg?branch=main"></a>
    <a href="https://github.com/olajideolamide/dex/actions/workflows/phpcs.yml"><img alt="PHPCS (advisory)" src="https://github.com/olajideolamide/dex/actions/workflows/phpcs.yml/badge.svg?branch=main"></a>
</p>

<p align="center">
    <a href="https://www.dexphp.com/documentation">Get Started</a>
    <span>&nbsp;|&nbsp;</span>
    <a href="https://dex.profusionlabs.org">Demo</a>
    <span>&nbsp;|&nbsp;</span>
    <a href="CONTRIBUTING.md">Contributing</a>
</p>

## About DEX

DEX helps you understand what happened inside a CodeIgniter 4 request without digging through scattered logs or guessing which route, query, span, or breadcrumb caused the break.

It captures errors, groups repeat failures into issues, keeps the useful request context, and shows the lifecycle of a request in a self-hosted dashboard built for CI4 projects. The goal is simple: when something fails, DEX should help you move from "something broke" to "this is where it broke" as quickly as possible.

Use DEX to:

- Track application errors and regressions as issues.
- Inspect request timelines, controller flow, spans, breadcrumbs, and slow work.
- See event volume and issue trends from a clean dashboard.
- Keep monitoring close to your application without shipping every detail to a third-party service.

## Screenshots

<p align="center">
    <img src=".github/assets/issues_screenshot.png" alt="DEX issues dashboard">
</p>

<p align="center">
    <img src=".github/assets/request_lifecycle.png" alt="DEX request lifecycle timeline">
</p>

## Contributing

DEX is open source, and thoughtful contributions are welcome. If you want to fix a bug, improve the dashboard, tighten the telemetry pipeline, or help with documentation, start with the [contribution guide](CONTRIBUTING.md).

Please keep changes focused and easy to review. Small, well-tested improvements are much easier to merge than broad rewrites.

## Need Help?

If you run into a problem, [open a detailed issue](https://github.com/olajideolamide/dex/issues/new) with the CodeIgniter version, PHP version, DEX version, the steps to reproduce it, and any relevant logs or screenshots.

For questions, ideas, or design discussions, start a new [GitHub Discussion](https://github.com/olajideolamide/dex/discussions). If you believe you have found a security issue, please review the [security policy](security.md) before sharing details publicly.
