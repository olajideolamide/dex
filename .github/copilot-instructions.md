# Copilot Instructions (Dex — CodeIgniter 4 Library)

- You are working in a CodeIgniter 4 (CI4) application library that monitors the performance and logs errors that occur in a CodeIgniter 4 web application. You can think of it like a mini version of Sentry, but built specifically for CI4 and with a focus on simplicity and maintainability.
- The library is designed to be easily integrated into any CI4 project, providing insights into application performance and error tracking without the overhead of a full APM solution.
- Default to simple, maintainable solutions that fit CI4 and this codebase.

### **Core Directive**
- You are an expert AI pair programmer. Your primary goal is to make precise, high-quality, and safe code modifications. You must follow every rule in this document meticulously.


## Core goals
- Keep code readable, predictable, and easy to extend.
- Prefer incremental changes over big rewrites.
- Optimize for correctness and clarity before cleverness.

---

## Quick facts
- Framework: CodeIgniter 4.
- Language: PHP 8.2+, DB: MariaDB/MySQL.

---

## Coding standards
- Follow **PSR-12**.
- Use strict typing where the file/project conventions allow it.
- Prefer explicit names over abbreviations.
- Keep functions small and single-purpose.
- Avoid large argument lists ("long parameter lists"). As a rule of thumb, if a function needs ~5+ arguments or many same-y primitives/arrays, refactor.
  - Prefer a cohesive DTO (e.g., `RequestOverviewInputDTO`) when the inputs represent a stable "shape" and are passed across methods/layers.
  - Use an associative array only when the data is genuinely dynamic or one-off, and document expected keys with PHPDoc.
  - If the long argument list is a symptom that a function is doing too much, restructure: split into smaller private methods or move sections into focused services.
- Prefer early returns over deep nesting.
- Match the style and patterns of the surrounding code.
- Use `try/catch` or `try/except` for operations that can fail.
- Sanitize inputs. Never hardcode secrets. 
- Use CI validation in Controllers for request data.
- PHP Doc for new functions. Comment only complex, non-obvious logic.
- Prefer concrete implementations over abstractions unless the codebase already uses them. Only use interfaces if absolutely neccesary for example in SMS sender, or payment gateway abstractions.

## Architecture rules (must follow)
- App works as controller -> orchestrator (use-case) -> services (helpers) -> repositories (data access) -> models.
- Use DTOs for passing data between layers when appropriate, but avoid overusing them for simple cases.
- interfaces and abstract classes are only used when the codebase already uses them, or when absolutely necessary for example in SMS sender, or payment gateway abstractions. Do not introduce new architectural layers (e.g., “Domain/Infrastructure” split) unless the codebase already uses them.
- Interfaces should end with `Interface`, abstract classes with `Abstract`, and concrete classes with their domain name (e.g., `CampaignService`).


### Controllers (thin)
Controllers should:
- Read input from request, validate/sanitize (lightweight).
- Call an orchestrator/service (the “use-case”).
- Return a response (view/json/redirect).
  Controllers must NOT:
- Contain business logic.
- Run DB queries directly.
- Contain complex branching workflow logic.
  Migrations live in `app/Database/Migrations`.
- Use `php spark migrate` to apply.
  UI must
- Use a minimal, frontend-only stack (Vanilla JS, Modern CSS, HTML, tabler.io template). Avoid unnecessary complexity, libraries, or build tools.
- Always use the tabler framework examples from: https://tabler.io/admin-template/preview
- Use the vertical layout for tabler.
- So if you are tasked for exampled to adding pricing feature, UI can be built using: https://preview.tabler.io/pricing.html as a base.
- There is already a global layout in views/layout.php, with internal pages composing the layout. Use that.

### Orchestrators (use-cases)
Orchestrators should:
- Implement a single business workflow (e.g., “Create Campaign”, “Send Batch”, “Schedule Campaign”).
- Coordinate calls to helper services and repositories.
- Own the “story” of the request.
- Orchestrator file names and class names must include `Orchestrator`.
- Only one orchestrator class per file.
  Orchestrators must NOT:
- Talk to the HTTP layer (no request/response objects).
- Perform raw DB queries (delegate to repositories).

### Services (helper services)
Services should:
- Implement focused domain actions (formatting, validation, scheduling logic, vendor abstraction, etc.).
- Be easy to unit test later (no hidden globals).
- Group related logic into a single service when it belongs to the same domain (e.g., a Contacts service hosting `countContactsByStatus()` instead of a standalone service).
  Services must NOT:
- Become god-classes. Split when responsibilities grow.

### Repositories (data access)
Repositories should:
- Contain DB queries and persistence logic.
- Return simple arrays/entities/DTOs appropriate to the layer.
+ Repository naming: file name and class name must end with `Repository`.
  Repositories must NOT:
- Contain business workflow decisions (keep that in orchestrators).

### DTOs
DTOs may:
- Hold validated data.
- Provide small convenience methods (e.g., computed getters).
  DTOs must NOT:
- Do DB calls.
- Contain heavy business logic.
+ DTO naming: file name and class name must end with `DTO`.

---

## Dependency / Services wiring
- Prefer constructor injection when practical.
- Dependencies must be injected from src/Config/Services NOT from the controllers directly. This keeps wiring clean and allows for easier testing later.
- In CI4, keep wiring clean: do not hide heavy work inside constructors.
- Use CI4 built in service for DI.
- Make the controllers call orchestrators with parameters (if available) using __invoke.
- Avoid static/global access patterns unless the codebase already standardizes it.
- Avoid using CI and its api inside services, repositories, and DTOs. Can only be used in controllers.
+ Service naming: file name and class name must end with `Service`.
+ Exception naming: file name and class name must end with `Exception`.

---

## Error handling
- Use domain-specific exceptions for domain failures where appropriate.
- Controllers translate exceptions into HTTP responses/messages.
- Never swallow exceptions silently. Log with context.

---

## Guardrails (important)
- Do not introduce new architecture layers** (e.g., “Domain/Infrastructure” split) unless the codebase already uses them.
- Do not refactor unrelated files** while implementing a small change.
- Do not change public behavior/API** unless explicitly required by the task.
- Keep backward compatibility** where possible; if a breaking change is required, call it out clearly in code comments and/or docs.

### Composer dependencies (use caution)
If you think a new package is needed:
1. Prefer built-in CI4 / standard PHP features first.
2. If still needed, choose a widely-used, well-maintained package.
3. Add the minimal dependency set.
4. Avoid packages that overlap what CI4 already provides.
5. Keep versions sensible and avoid unnecessary updates to unrelated packages.

---

## Database changes (migrations required)
If touching the database in any way:
- Add/update **migrations** (no manual schema changes).
- In migrations always check if the table, column, or index exists before creating/dropping to ensure reversibility and avoid issues in production.
- Ensure migrations are reversible where possible.
- Update seeders only if needed.
- If there’s an existing pattern for table naming / indexes, follow it.

### “Coverage” note (even though tests are off for now)
- No formal testing is required right now.
- But if you touch DB logic, leave the code in a state that can be covered later:
    - Keep queries inside repositories.
    - Keep services/orchestrators deterministic.
    - Avoid hard-coded globals; prefer injectable dependencies.

---

## Security & privacy
- Never log secrets (tokens, credentials).
- Mask PII in logs when practical.
- Validate and normalize phone numbers before use.
- Be careful with user-supplied content included in messages (avoid injection in templates).

---

## Performance / reliability
- This is a monitoring library, so reliability and low overhead are important.
- Avoid N+1 DB queries.
- Prefer batching patterns and idempotent operations when applicable.
- For async/queued behavior, keep the orchestration clean and re-runnable.

## Definition of done (for any change)
- Code follows PSR-12 and existing project conventions.
- Minimal, targeted diff (no drive-by refactors).
- Controllers stay thin.
- DB changes include migrations.
- Comments added only where they clarify intent or non-obvious logic.


#### Log, Don’t Implement, Unscoped Ideas
- If you identify a potential improvement outside the task's scope, add it as a code comment.
- **Example:** `// NOTE: This function could be further optimized by caching results.`

#### Implement the Fix Incrementally
- Tackle the plan one step at a time. Before editing,  always read the file (max 2000 lines for context) to ensure your changes are safe. After completing a step, check it off the list, show me the update, and move straight to the next one. 
- When asked to implement a new feature or fix a bug, first create a high-level plan with steps to implement it. Share the plan with me for approval before starting any code changes. The changes will be done step by step. each step is presented to me for approval before moving to the next step. List files that will change in the plan. 
- **Example Plan for Adding a New Feature:**
    1. Create a new Orchestrator class to handle the business logic.
    2. Add a new method in the relevant Service for any domain-specific actions.
    3. Update the Controller to call the new Orchestrator.
    4. Add necessary migrations if database changes are required.
    5. Write unit tests for the new Orchestrator and Service methods.



#### Testing (syntax-only)
*   Only run syntax checks (e.g., `php -l`).
*   Do not run PHPUnit, `spark test`, or other test suites unless explicitly requested.

#### Research on the Internet
- After investigating the codebase, and not finding a clear path of implementation, Research on Google. Because your internal knowledge can be out of date, Use Google to verify your approach for any third-party packages or APIs. Iform me of your research, for instance: "I'm going to quickly Google the documentation for that library to ensure I'm using it correctly."*


#### Preserve Existing Behavior
- Ensure your changes are surgical and do not alter existing functionalities or APIs.
- Maintain the project's existing architectural and coding patterns.

#### Handle Ambiguity Safely
- If a request is unclear, state your assumption and proceed with the most logical interpretation.

#### Forbidden Actions (Unless Explicitly Permitted)
- Do not perform global refactoring.
- Do not change formatting or run a linter on an entire file.

#### Ensure Reversibility
- Write changes in a way that makes them easy to understand and revert.
- Avoid cascading or tightly coupled edits that make rollback difficult.

### Views (display-only)
Views should:
- Only render already-prepared data.
- Use control flow (`if`, `foreach`) only for conditional display and iterating pre-shaped rows.
- Call simple, side-effect-free helpers needed for rendering (e.g., `esc()`, `dex_view()`, `dex_view_name()`, `dex_route_prefix()`, `site_url()`).

Views must NOT:
- Define functions/closures.
- Call complex helpers that prepare/format/derive data that could have been produced by a service (e.g., `dex_time_ago()`, bytes/ms formatting, parsing user agents, computing derived metrics).
- Contain data-prep/business logic (aggregation, bucketing, sorting, normalization, fallbacks).

Data prep rules:
- Services (or helpers called by services) must prepare view data fully, including display-ready strings.
- Helpers should prefer returning arrays/strings that views can render. Returning HTML is acceptable only for already-established helper patterns relied on elsewhere; for new helpers, avoid returning HTML when practical.
