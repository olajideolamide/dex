### CI4 Insight (Dex) Development Guidelines

This document provides essential information for advanced developers working on the CI4 Insight (Dex) project.

#### 1. Build & Configuration

- **Dependencies**: The project uses Composer for dependency management. Run `composer install` to set up the development environment.
- **Requirements**: PHP 8.2+ and CodeIgniter 4.5+.
- **Configuration**: The library expects a configuration object (typically an instance of `Dex\Config\Dex`). In a standard CodeIgniter 4 application, this is usually managed via the `Config` namespace.

#### 2. Testing

The project uses PHPUnit for testing.

##### Configuration
- **Bootstrap**: Tests use `tests/bootstrap.php`, which handles Composer autoloading and conditionally bootstraps the CodeIgniter 4 framework.
- **Environment Variables**:
    - `MINISENTRY_USE_CI_BOOTSTRAP`: Set to `1` in `phpunit.xml.dist` to ensure the CodeIgniter 4 system bootstrap is loaded.
- **Stubs**: When running tests outside a full CI4 application, `tests/bootstrap.php` stubs `Config\Modules` to prevent failures during service discovery.

##### Running Tests
To run the full suite:
```bash
vendor/bin/phpunit
```

To run a specific test file:
```bash
vendor/bin/phpunit tests/Support/FingerprintTest.php
```

##### Adding New Tests
- Place tests in the `tests/` directory, following the directory structure of `src/`.
- Use the `Dex\Tests` namespace.
- Example of a simple test:
```php
<?php

namespace Dex\Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testBasicAssertion(): void
    {
        $this->assertTrue(true);
    }
}
```

#### 3. Additional Development Information

- **Code Style**: 
    - Follow PSR-12 coding standards.
    - Use strict types (`declare(strict_types=1);`) in all new files.
    - Maintain consistency with existing naming conventions (camelCase for methods and variables, PascalCase for classes).
- **Architecture**:
    - The project makes heavy use of Traits (see `src/Concerns`) to organize functionality within the main `Dex` class.
    - Adapters (see `src/Adapters`) are used to bridge the library with CodeIgniter 4 specific components (Cache, Request, Router).
- **Internal Guards**: Many methods use the `InternalGuardTrait` to ensure they only execute when the library is properly initialized, preventing errors during the CI4 boot process or in CLI mode if not configured.
