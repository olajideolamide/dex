---
title: "Fingerprinting"
description: "Understand how DEX groups repeating exceptions into a single issue."
weight: 40
---

DEX groups repeat failures into issues using a **fingerprint**.

If two exceptions produce the same fingerprint, they belong to the same issue.

## What goes into a fingerprint

For exceptions, DEX builds a fingerprint from:

- Exception class name
- Exception message (normalized)
- The first few stack frames (file, line, function)

The result is stored as a SHA-1 hash (truncated) in `dex_issues.fingerprint`.

## Message normalization

To keep fingerprints stable, DEX normalizes messages before hashing:

- Trims whitespace
- Collapses multiple spaces
- Replaces plain integers with `{n}` (so `User 123` and `User 456` group together)
- Truncates long strings to a safe maximum

## Why line numbers matter

Stack frames include line numbers, which helps distinguish two errors that share the same message but come from different code paths.

The tradeoff is that large refactors can change fingerprints, because the “top frames” may change.

## Can I customize fingerprinting?

Not currently. Fingerprinting is implemented in the library so issue grouping stays consistent across environments.

If you need more stable grouping for a particular error class, a practical workaround is to:

- Throw a custom exception type for that failure mode, or
- Ensure the exception message contains a stable identifier (without user-specific numbers).

