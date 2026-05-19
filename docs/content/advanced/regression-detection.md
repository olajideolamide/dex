---
title: "Regressions"
description: "How DEX reopens resolved issues when the same fingerprint appears again."
weight: 41
---

DEX issues have a lifecycle. The important statuses are:

- `open` — active issue
- `resolved` — manually marked fixed
- `ignored` — manually suppressed
- `regression` — a resolved issue that reappeared

## What counts as a regression?

A regression happens when:

1. You resolve an issue in the dashboard
2. The same fingerprint is captured again later
3. DEX changes the issue status from `resolved` → `regression`

In the UI you may see this displayed as “Regressed”.

## Why this matters

Regressions are usually the most urgent issues:

- A fix didn’t hold
- A change reintroduced a known bug
- A deployment uncovered a hidden dependency

If you use DEX during releases, regressions make it obvious when something “came back”.

