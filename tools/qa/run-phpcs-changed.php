<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$phpcsBinary = $projectRoot . '/vendor/squizlabs/php_codesniffer/bin/phpcs';
$rulesetPath = $projectRoot . '/phpcs.xml.dist';

if (! is_file($phpcsBinary)) {
    fwrite(STDERR, "PHPCS binary not found. Run composer install first.\n");
    exit(1);
}

$changedPhpFiles = findChangedPhpFiles($projectRoot);

if ($changedPhpFiles === []) {
    fwrite(STDOUT, "No changed PHP files detected for PHPCS.\n");
    exit(0);
}

fwrite(STDOUT, "Running PHPCS on changed files:\n");
foreach ($changedPhpFiles as $changedPhpFile) {
    fwrite(STDOUT, ' - ' . $changedPhpFile . "\n");
}

$phpBinary = escapeshellarg(PHP_BINARY);
$phpcsCommand = $phpBinary . ' ' . escapeshellarg($phpcsBinary)
    . ' --standard=' . escapeshellarg($rulesetPath)
    . ' --colors'
    . ' ' . implode(' ', array_map('escapeshellarg', $changedPhpFiles));

passthru($phpcsCommand, $exitCode);
exit($exitCode);

function findChangedPhpFiles(string $projectRoot): array
{
    $baseRef = resolveDiffBaseRef();

    if ($baseRef === null) {
        return [];
    }

    $diffCommand = sprintf(
        'git -C %s diff --name-only --diff-filter=ACMR %s HEAD',
        escapeshellarg($projectRoot),
        escapeshellarg($baseRef)
    );

    exec($diffCommand, $filePaths, $exitCode);
    if ($exitCode !== 0) {
        fwrite(STDERR, "Unable to compute changed files for PHPCS.\n");
        return [];
    }

    $allowedPrefixes = ['src/', 'tests/'];
    $normalizedFiles = [];

    foreach ($filePaths as $filePath) {
        $normalizedPath = str_replace('\\', '/', trim($filePath));
        if ($normalizedPath === '') {
            continue;
        }

        if (! str_ends_with($normalizedPath, '.php')) {
            continue;
        }

        $isAllowed = false;
        foreach ($allowedPrefixes as $allowedPrefix) {
            if (str_starts_with($normalizedPath, $allowedPrefix)) {
                $isAllowed = true;
                break;
            }
        }

        if (! $isAllowed) {
            continue;
        }

        $absolutePath = $projectRoot . '/' . $normalizedPath;
        if (! is_file($absolutePath)) {
            continue;
        }

        $normalizedFiles[] = $absolutePath;
    }

    return array_values(array_unique($normalizedFiles));
}

function resolveDiffBaseRef(): ?string
{
    $explicitDiffBase = getenv('DEX_QA_DIFF_BASE');
    if (is_string($explicitDiffBase) && trim($explicitDiffBase) !== '' && trim($explicitDiffBase) !== str_repeat('0', 40)) {
        return trim($explicitDiffBase);
    }

    $defaultBaseBranch = getenv('DEX_QA_BASE_BRANCH');
    $baseBranch = is_string($defaultBaseBranch) && trim($defaultBaseBranch) !== ''
        ? trim($defaultBaseBranch)
        : 'origin/main';

    $mergeBaseCommand = sprintf(
        'git merge-base %s HEAD',
        escapeshellarg($baseBranch)
    );

    $mergeBase = trim((string) shell_exec($mergeBaseCommand));
    if ($mergeBase !== '') {
        return $mergeBase;
    }

    $fallbackBase = trim((string) shell_exec('git rev-parse HEAD~1'));
    return $fallbackBase !== '' ? $fallbackBase : null;
}
