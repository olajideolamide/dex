<script>
    const dexIssuesInitialData = <?= json_encode([
            'issues' => $issues ?? [],
            'summary' => $summary ?? [],
            'chart' => $chart ?? [],
            'pagination' => $pagination ?? [],
            'filters' => $filters ?? [],
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const dexIssuesDataUrl = <?= json_encode($dataUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const dexIssuesDetailBaseUrl = <?= json_encode($detailBaseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    const dexIssuesState = {
        status: dexIssuesInitialData.filters.status || 'all',
        q: dexIssuesInitialData.filters.q || '',
        page: dexIssuesInitialData.pagination.page || 1,
        perPage: dexIssuesInitialData.pagination.perPage || 25,
        data: dexIssuesInitialData,
        loading: false,
        searchTimer: null,
    };

    const dexIssueDialogState = {
        issueId: null,
        occurrenceId: null,
        shellAbortController: null,
        eventAbortController: null,
        tabAbortController: null,
        activeTab: 'overview',
        lastFocus: null,
    };

    function dexIssuesEscapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function dexIssuesStatusBadgeColor(status) { 
        return { 
            open: 'danger', 
            regressed: 'warning', 
            regression: 'warning',
            resolved: 'success', 
            ignored: 'secondary', 
        }[status] || 'secondary'; 
    } 
 
    function dexIssuesDialogStatusChipClass(status) { 
        return { 
            open: 'dex-chip dex-chip--open', 
            regressed: 'dex-chip dex-chip--regressed', 
            regression: 'dex-chip dex-chip--regressed',
            resolved: 'dex-chip dex-chip--resolved', 
            ignored: 'dex-chip dex-chip--ignored', 
        }[status] || 'dex-chip'; 
    } 

    function dexIssuesCloseActionMenus() {
        document.querySelectorAll('.dex-v2 [data-dex-action-menu]').forEach((menu) => {
            const trigger = menu.querySelector('[data-dex-action-menu-trigger]');
            const panel = menu.querySelector('.dex-action-menu__panel');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
            if (panel) {
                panel.hidden = true;
            }
        });
    }

    function dexIssuesToggleActionMenu(menu) {
        const trigger = menu.querySelector('[data-dex-action-menu-trigger]');
        const panel = menu.querySelector('.dex-action-menu__panel');
        if (!trigger || !panel) {
            return;
        }

        const willOpen = panel.hidden;
        dexIssuesCloseActionMenus();

        if (willOpen) {
            panel.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            const firstItem = panel.querySelector('.dex-action-menu__item:not([disabled])');
            if (firstItem) {
                firstItem.focus({preventScroll: true});
            }
        }
    }

    function dexIssuesTrendColor(issue) {
        if ((issue.events24h || 0) === 0) {
            return '#9ca3af';
        }

        return (issue.status === 'regressed' || issue.status === 'regression') ? '#d97706' : '#e84c1e'; 
    } 

    function dexIssuesEvents24hClass(issue) {
        if ((issue.events24h || 0) === 0) {
            return 'dex-issues-v2__metric--muted';
        }

        if ((issue.events24h || 0) > 30) {
            return 'dex-issues-v2__metric--hot';
        }

        return 'dex-issues-v2__metric--normal';
    }

    function dexIssuesLevelStripColor(level) {
        return {fatal: '#be123c', error: '#c2410c', warning: '#a16207'}[level] || '#c2410c';
    }

    function dexMaxCharacters(subject, max) {
        const s = subject == null ? '' : String(subject);
        const n = Number(max) || 0;

        // If max is 0 or negative, return empty string
        if (n <= 0) {
            return '';
        }

        // If the string fits, return it unchanged
        if (s.length <= n) {
            return s;
        }

        // If max is too small to reserve space for '...', just slice and append '...'
        if (n <= 3) {
            return s.slice(0, n) + '...';
        }

        // Normal case: reserve 3 chars for '...'
        return s.slice(0, n - 3) + '...';
    }

    function dexIssuesAgeClass(issue) {
        if (issue.status === 'resolved') {
            return 'age-done';
        }

        const numeric = parseInt(String(issue.age || '0'), 10);
        return numeric > 10 ? 'age-old' : 'age-recent';
    }

    function dexIssuesBuildSparkline(data, color) {
        const values = Array.isArray(data) && data.length ? data : new Array(24).fill(0);
        const width = 76;
        const height = 30;
        const max = Math.max(...values, 1);
        const points = values.map((value, index) => `${(index / (values.length - 1)) * width},${height - (value / max) * (height - 2) - 1}`).join(' ');
        const gradientId = `dex-spark-grad-${Math.random().toString(36).slice(2, 8)}`;

        return `<svg class="sparkline" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
            <defs>
                <linearGradient id="${gradientId}" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="${color}" stop-opacity="0.32"></stop>
                    <stop offset="100%" stop-color="${color}" stop-opacity="0"></stop>
                </linearGradient>
            </defs>
            <polygon points="${points} ${width},${height} 0,${height}" fill="url(#${gradientId})"></polygon>
            <polyline points="${points}" fill="none" stroke="${color}" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round"></polyline>
        </svg>`;
    }

    function dexIssuesBuildRow(issue) {
        const trendColor = dexIssuesTrendColor(issue);
        const spark = dexIssuesBuildSparkline(issue.trend || [], trendColor);
        const method = issue.method ? `<span class="method-pill method-${dexIssuesEscapeHtml(issue.method)}">${dexIssuesEscapeHtml(issue.method)}</span>` : '';
        const route = issue.route ? `<span class="text-secondary dex-issues-v2__route">${dexIssuesEscapeHtml(dexMaxCharacters(issue.route, 30))}</span>` : '';


        return `<tr data-id="${issue.id}">
        <td class="dex-issues-v2 issue-border issue-border-${issue.status} dex-issues-v2__td-main">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <span class="dex-issues-v2__issue-class">${dexIssuesEscapeHtml(dexMaxCharacters(issue.cls, 30))}:</span>
                    <span>${dexIssuesEscapeHtml(dexMaxCharacters(issue.message, 50))}</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">${method}${route}</div>
            </td>
            <td class="text-end dex-ws-nowrap">
                <div class="dex-issues-v2__metric dex-issues-v2__metric--total">${Number(issue.events || 0).toLocaleString()}</div>
                <div class="dex-issues-v2__metric-sub">total</div>
            </td>
            <td class="text-end dex-ws-nowrap">
                <div class="dex-issues-v2__metric dex-issues-v2__metric--24h ${dexIssuesEvents24hClass(issue)}">${Number(issue.events24h || 0).toLocaleString()}</div>
                <div class="dex-issues-v2__metric-sub">events</div>
            </td>
            <td>${spark}</td>
            <td class="text-end dex-ws-nowrap dex-issues-v2__last-seen">${dexIssuesEscapeHtml(issue.lastSeen || '-')}</td>
            <td class="text-end dex-ws-nowrap dex-issues-v2__td-age"><span class="age-chip ${dexIssuesAgeClass(issue)}">${dexIssuesEscapeHtml(issue.age || '-')}</span></td>
            <td class="td-chevron">&rsaquo;</td>
        </tr>`;
    }

    function dexIssuesRenderRows() {
        const tbody = document.getElementById('issuesTbody');
        const issues = dexIssuesState.data.issues || [];

        if (dexIssuesState.loading) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="8">Loading issues...</td></tr>';
            return;
        }

        if (!issues.length) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="8">No issues match your filters.</td></tr>';
            return;
        }

        tbody.innerHTML = issues.map(dexIssuesBuildRow).join('');
    }

    function dexIssuesDialogOverlay() {
        return document.getElementById('dexIssueOverlay');
    }

    function dexIssuesDialogContent() {
        return document.getElementById('dexIssueOverlayContent');
    }

    function dexIssuesDialogPanel() {
        return dexIssuesDialogOverlay().querySelector('.dex-issue-overlay__panel');
    }

    function dexIssuesDialogPaneId(tab) {
        return {
            overview: 'ms-tab-overview',
            stack: 'ms-tab-stack',
            lifecycle: 'ms-tab-lifecycle',
            http: 'ms-tab-http',
            tags: 'ms-tab-tags',
            raw: 'ms-tab-raw',
            metrics: 'ms-tab-metrics',
        }[tab] || 'ms-tab-overview';
    }

    function dexIssuesCopyText(text) {
        if (!text) {
            return;
        }

        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(text);
            return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
        } catch (error) {
            // ignore copy fallback errors
        }
        document.body.removeChild(textarea);
    }

    function dexIssuesSetDialogOpen(isOpen) {
        const overlay = dexIssuesDialogOverlay();
        overlay.hidden = !isOpen;
        overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        document.body.style.overflow = isOpen ? 'hidden' : '';

        if (isOpen) {
            requestAnimationFrame(() => dexIssuesDialogPanel().focus());
        }
    }

    function dexIssuesSetDialogBody(html) {
        const content = dexIssuesDialogContent();
        content.innerHTML = html;
        content.scrollTop = 0;
    }

    function dexIssuesDialogLoading(message = 'Loading issue…') {
        dexIssuesSetDialogBody(`<div class="dex-issue-overlay__loading">${dexIssuesEscapeHtml(message)}</div>`);
    }

    function dexIssuesDialogError(message) {
        dexIssuesSetDialogBody(`<div class="dex-issue-overlay__error">${dexIssuesEscapeHtml(message)}</div>`);
    }

    function dexIssuesDialogEventContent() {
        return dexIssuesDialogContent().querySelector('[data-dex-issue-event-content]');
    }

    function dexIssuesCloseDialog() {
        if (dexIssueDialogState.shellAbortController) {
            dexIssueDialogState.shellAbortController.abort();
            dexIssueDialogState.shellAbortController = null;
        }

        if (dexIssueDialogState.eventAbortController) {
            dexIssueDialogState.eventAbortController.abort();
            dexIssueDialogState.eventAbortController = null;
        }

        if (dexIssueDialogState.tabAbortController) {
            dexIssueDialogState.tabAbortController.abort();
            dexIssueDialogState.tabAbortController = null;
        }

        dexIssuesSetDialogOpen(false);
        dexIssueDialogState.issueId = null;
        dexIssueDialogState.occurrenceId = null;
        dexIssueDialogState.activeTab = 'overview';

        if (dexIssueDialogState.lastFocus instanceof HTMLElement) {
            dexIssueDialogState.lastFocus.focus();
        }
    }

    async function dexIssuesOpenDialog(issueId, occurrenceId = null, trigger = null) {
        dexIssueDialogState.issueId = Number(issueId);
        dexIssueDialogState.occurrenceId = occurrenceId ? Number(occurrenceId) : null;
        dexIssueDialogState.activeTab = 'overview';
        dexIssueDialogState.lastFocus = trigger instanceof HTMLElement ? trigger : document.activeElement;

        if (dexIssueDialogState.shellAbortController) {
            dexIssueDialogState.shellAbortController.abort();
        }

        if (dexIssueDialogState.eventAbortController) {
            dexIssueDialogState.eventAbortController.abort();
            dexIssueDialogState.eventAbortController = null;
        }

        if (dexIssueDialogState.tabAbortController) {
            dexIssueDialogState.tabAbortController.abort();
            dexIssueDialogState.tabAbortController = null;
        }

        dexIssueDialogState.shellAbortController = new AbortController();
        dexIssuesSetDialogOpen(true);
        dexIssuesDialogLoading();

        const url = new URL(`${dexIssuesDetailBaseUrl}/${issueId}/dialog`, window.location.origin);
        if (occurrenceId) {
            url.searchParams.set('occ', String(occurrenceId));
        }

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: dexIssueDialogState.shellAbortController.signal,
            });

            if (!response.ok) {
                throw new Error(response.status === 404 ? 'Issue not found.' : 'Failed to load issue details.');
            }

            dexIssuesSetDialogBody(await response.text());
            dexIssuesInitDialogShell();
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            dexIssuesDialogError(error.message || 'Failed to load issue details.');
        }
    }

    function dexIssuesLoadDialogSections(sections) {
        return sections.reduce((promise, section) => {
            return promise.then(() => dexIssuesLoadDialogSection(section));
        }, Promise.resolve());
    }

    async function dexIssuesPaginateDialog(issueId, occurrenceId, trigger = null) {
        const shell = dexIssuesDialogContent().querySelector('[data-dex-issue-shell]');
        const currentEventContent = dexIssuesDialogEventContent();
        if (!shell || !currentEventContent) {
            return dexIssuesOpenDialog(issueId, occurrenceId, trigger);
        }

        dexIssueDialogState.lastFocus = trigger instanceof HTMLElement ? trigger : document.activeElement;

        if (dexIssueDialogState.eventAbortController) {
            dexIssueDialogState.eventAbortController.abort();
        }

        if (dexIssueDialogState.tabAbortController) {
            dexIssueDialogState.tabAbortController.abort();
            dexIssueDialogState.tabAbortController = null;
        }

        dexIssueDialogState.eventAbortController = new AbortController();
        currentEventContent.querySelectorAll('.dex-event-pager__nav-btn').forEach((button) => {
            button.disabled = true;
        });

        const url = new URL(`${shell.dataset.dexIssueDialogUrl}/event`, window.location.origin);
        url.searchParams.set('occ', String(occurrenceId));

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: dexIssueDialogState.eventAbortController.signal,
            });

            if (!response.ok) {
                throw new Error(response.status === 404 ? 'Issue not found.' : 'Failed to load event.');
            }

            const markup = await response.text();
            const temp = document.createElement('div');
            temp.innerHTML = markup.trim();
            const nextEventContent = temp.firstElementChild;

            if (!nextEventContent) {
                throw new Error('Failed to load event.');
            }

            currentEventContent.replaceWith(nextEventContent);

            const nextOccurrenceId = Number(nextEventContent.dataset.dexIssueOccurrence || occurrenceId) || null;
            shell.dataset.dexIssueOccurrence = nextOccurrenceId ? String(nextOccurrenceId) : '';
            dexIssueDialogState.occurrenceId = nextOccurrenceId;

            await dexIssuesLoadDialogSections(['stack', 'lifecycle']);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            currentEventContent.querySelectorAll('.dex-event-pager__nav-btn').forEach((button) => {
                button.disabled = Number(button.dataset.dexIssuePaginate || 0) <= 0;
            });
        } finally {
            dexIssueDialogState.eventAbortController = null;
        }
    }

    function dexIssuesInitBreadcrumbFilter(root) {
        const input = root.querySelector('#ms-bc-filter');
        const list = root.querySelector('#ms-bc-list');
        if (!input || !list || input.dataset.dexBound === '1') {
            return;
        }

        input.dataset.dexBound = '1';
        input.addEventListener('input', () => {
            const query = (input.value || '').trim().toLowerCase();
            list.querySelectorAll('[data-search]').forEach((node) => {
                const haystack = (node.getAttribute('data-search') || '').toLowerCase();
                node.style.display = !query || haystack.includes(query) ? '' : 'none';
            });
        });
    }

    function dexIssuesInitSpanFilter(root) {
        const input = root.querySelector('#ms-span-filter');
        const table = root.querySelector('#ms-span-table');
        if (!input || !table || input.dataset.dexBound === '1') {
            return;
        }

        input.dataset.dexBound = '1';
        input.addEventListener('input', () => {
            const query = (input.value || '').trim().toLowerCase();
            table.querySelectorAll('[data-search]').forEach((node) => {
                const haystack = (node.getAttribute('data-search') || '').toLowerCase();
                node.style.display = !query || haystack.includes(query) ? '' : 'none';
            });
        });
    }

    /* ─── Code highlighter (vanilla, ~one-dark themed) ────────────── */

    const DEX_HL_KEYWORDS_PHP = new Set([
        'abstract','and','array','as','break','callable','case','catch','class','clone','const',
        'continue','declare','default','do','echo','else','elseif','empty','enddeclare','endfor',
        'endforeach','endif','endswitch','endwhile','enum','extends','final','finally','fn',
        'for','foreach','function','global','goto','if','implements','include','include_once',
        'instanceof','insteadof','interface','isset','list','match','namespace','new','or','print',
        'private','protected','public','readonly','require','require_once','return','self','static',
        'switch','throw','trait','try','use','var','while','xor','yield','from','parent','this',
        'true','false','null'
    ]);

    const DEX_HL_TYPES_PHP = new Set([
        'int','integer','string','bool','boolean','float','double','void','mixed','object',
        'iterable','never','self','static','parent','array','callable'
    ]);

    const DEX_HL_KEYWORDS_JS = new Set([
        'await','break','case','catch','class','const','continue','debugger','default','delete',
        'do','else','enum','export','extends','finally','for','function','if','import','in',
        'instanceof','let','new','of','return','static','super','switch','this','throw','try',
        'typeof','var','void','while','with','yield','async','true','false','null','undefined'
    ]);

    const DEX_HL_KEYWORDS_SQL = new Set([
        'select','from','where','and','or','not','in','is','null','insert','into','values',
        'update','set','delete','create','alter','drop','table','column','primary','key','foreign',
        'references','join','left','right','inner','outer','on','as','order','by','asc','desc',
        'group','having','limit','offset','distinct','union','all','case','when','then','else',
        'end','if','exists','count','sum','avg','min','max','like','between','default','index',
        'unique','constraint','using','add','modify','rename','to','schema','database','use',
        'show','grant','revoke','begin','commit','rollback','transaction'
    ]);

    function dexIssuesDetectLanguage(frameEl) {
        const pathEl = frameEl.querySelector('.dex-frame__path');
        const path = pathEl ? (pathEl.textContent || '').toLowerCase() : '';
        if (/\.(?:m?js|cjs|jsx|ts|tsx)(?::|$)/.test(path)) return 'js';
        if (/\.sql(?::|$)/.test(path)) return 'sql';
        return 'php';
    }

    function dexIssuesEscapeHl(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function dexIssuesHighlightLine(line, lang) {
        const stash = [];
        const placeholderBase = 0xE000; // BMP private-use area (safe marker bytes during regex passes)
        const put = (cls, text) => {
            const idx = stash.length;
            stash.push('<span class="dex-tok-' + cls + '">' + dexIssuesEscapeHl(text) + '</span>');
            return '\u0001' + String.fromCharCode(placeholderBase + idx) + '\u0002';
        };

        const keywords = lang === 'js'  ? DEX_HL_KEYWORDS_JS
                       : lang === 'sql' ? DEX_HL_KEYWORDS_SQL
                       :                  DEX_HL_KEYWORDS_PHP;

        let source = line;

        // Comments
        if (lang === 'sql') {
            source = source.replace(/--[^\n]*|\/\*[\s\S]*?\*\//g, (match) => put('com', match));
        } else {
            source = source.replace(/\/\*[\s\S]*?\*\/|\/\/[^\n]*|#(?!\[)[^\n]*/g, (match) => put('com', match));
        }

        // Strings (double, single, and SQL backtick identifiers as strings)
        source = source.replace(/"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'/g, (match) => put('str', match));

        // Variables (PHP)
        if (lang === 'php') {
            source = source.replace(/\$[A-Za-z_][A-Za-z0-9_]*/g, (match) => put('var', match));
        }

        // Numbers
        source = source.replace(/\b(?:0[xX][0-9a-fA-F]+|\d+\.?\d*(?:[eE][-+]?\d+)?)\b/g, (match) => put('num', match));

        // Member access: ->name or ::name
        source = source.replace(/(->|::)([A-Za-z_][A-Za-z0-9_]*)/g, (_, op, name) => put('op', op) + put('attr', name));

        // Function calls: identifier followed by (
        source = source.replace(/([A-Za-z_\\][A-Za-z0-9_\\]*)(?=\s*\()/g, (_, name) => {
            const lo = name.toLowerCase();
            if (keywords.has(lo)) {
                return put('kw', name);
            }
            return put('fn', name);
        });

        // Plain identifiers (keywords, types, class-cased)
        source = source.replace(/[A-Za-z_\\][A-Za-z0-9_\\]*/g, (match) => {
            const lo = match.toLowerCase();
            if (keywords.has(lo)) return put('kw', match);
            if (lang === 'php' && DEX_HL_TYPES_PHP.has(lo)) return put('cls', match);
            if (/^[A-Z]/.test(match)) return put('cls', match);
            return match;
        });

        // Final escape of remaining text, then re-inject stashed spans
        return dexIssuesEscapeHl(source).replace(/\u0001([\uE000-\uF8FF])\u0002/g, (_, marker) => {
            const idx = marker.charCodeAt(0) - placeholderBase;
            return stash[idx] || '';
        });
    }

    function dexIssuesHighlightCode(root) {
        root.querySelectorAll('.dex-frame').forEach((frame) => {
            if (frame.dataset.dexHighlighted === '1') {
                return;
            }
            frame.dataset.dexHighlighted = '1';

            const lang = dexIssuesDetectLanguage(frame);
            frame.querySelectorAll('.dex-code-line__src').forEach((node) => {
                const text = node.textContent || '';
                if (text === '') {
                    return;
                }
                node.innerHTML = dexIssuesHighlightLine(text, lang);
            });
        });
    }

    function dexIssuesInitFrameFilter(root) {
        root.querySelectorAll('.dex-frame__head').forEach((header) => {
            if (header.dataset.dexBound === '1') {
                return;
            }

            header.dataset.dexBound = '1';
            header.addEventListener('click', () => {
                header.parentElement.classList.toggle('is-open');
            });
        });

        const buttons = root.querySelectorAll('[data-frame-filter]');
        if (!buttons.length) {
            return;
        }

        buttons.forEach((button) => {
            if (button.dataset.dexBound === '1') {
                return;
            }

            button.dataset.dexBound = '1';
            button.addEventListener('click', () => {
                buttons.forEach((item) => item.classList.remove('active'));
                button.classList.add('active');
                const mode = button.getAttribute('data-frame-filter') || 'all';
                root.querySelectorAll('.dex-frame').forEach((item) => {
                    const kind = item.getAttribute('data-kind') || 'vendor';
                    item.style.display = mode === 'all' || kind === 'inapp' ? '' : 'none';
                });
            });
        });
    }

    function dexIssuesInitLifecycle(root) {
        const list = root.querySelector('[data-dex-lifecycle-list]');
        if (!list || list.dataset.dexBound === '1') {
            return;
        }

        list.dataset.dexBound = '1';

        const toggle = root.querySelector('[data-dex-lifecycle-toggle]');
        const toggleButtons = toggle ? toggle.querySelectorAll('[data-dex-lifecycle-toggle-action]') : [];

        function setItemExpanded(item, expanded) {
            const details = item.querySelector('[data-dex-lifecycle-item-details]');
            const button = item.querySelector('[data-dex-lifecycle-item-toggle]');
            if (!details || !button) {
                return;
            }

            if (expanded) {
                item.classList.remove('is-collapsed');
                details.hidden = false;
                button.setAttribute('aria-expanded', 'true');
                return;
            }

            item.classList.add('is-collapsed');
            details.hidden = true;
            button.setAttribute('aria-expanded', 'false');
        }

        function setAllExpanded(expanded) {
            list.querySelectorAll('[data-dex-lifecycle-item]').forEach((item) => setItemExpanded(item, expanded));
        }

        function setToggleActive(mode) {
            toggleButtons.forEach((button) => {
                button.classList.toggle('active', (button.dataset.dexLifecycleToggleAction || '') === mode);
            });
        }

        setAllExpanded(false);
        setToggleActive('collapse');

        list.addEventListener('click', (event) => {
            const button = event.target.closest('[data-dex-lifecycle-item-toggle]');
            if (button) {
                const item = button.closest('[data-dex-lifecycle-item]');
                if (!item) {
                    return;
                }

                event.preventDefault();
                const expanded = item.classList.contains('is-collapsed');
                setItemExpanded(item, expanded);
                return;
            }

            const item = event.target.closest('[data-dex-lifecycle-item]');
            if (!item) {
                return;
            }

            if (event.target.closest('[data-dex-lifecycle-item-details]')) {
                return;
            }

            if (event.target.closest('a, button, input, textarea, select, label')) {
                return;
            }

            event.preventDefault();
            const expanded = item.classList.contains('is-collapsed');
            setItemExpanded(item, expanded);
        });

        if (toggleButtons.length) {
            toggleButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const mode = button.dataset.dexLifecycleToggleAction || 'collapse';
                    const expandAll = mode === 'expand';
                    setAllExpanded(expandAll);
                    setToggleActive(mode);
                });
            });
        }
    }

    function dexIssuesDrawDialogVolumeChart(canvas, values) {
        if (!canvas || !canvas.offsetWidth) {
            return;
        }

        const data = Array.isArray(values) && values.length ? values : new Array(24).fill(0);
        const dpr = window.devicePixelRatio || 1;
        const width = canvas.offsetWidth;
        const height = 140;
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.height = `${height}px`;

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);

        const padding = {top: 10, bottom: 6, left: 30, right: 8};
        const max = Math.max(...data, 1);
        const gap = 4;
        const chartHeight = height - padding.top - padding.bottom;
        const barWidth = Math.max(4, ((width - padding.left - padding.right) - gap * (data.length - 1)) / data.length);

        [0, Math.round(max / 2), max].forEach((value) => {
            const y = padding.top + chartHeight - (value / max) * chartHeight;
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(width - padding.right, y);
            ctx.strokeStyle = 'rgba(15,18,25,0.06)';
            ctx.lineWidth = 1;
            ctx.setLineDash([2, 4]);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.fillStyle = '#9398a2';
            ctx.font = "10px ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace";
            ctx.textAlign = 'right';
            ctx.fillText(String(value), padding.left - 6, y + 3);
        });

        const radius = Math.min(2, barWidth / 2);

        data.forEach((value, index) => {
            const x = padding.left + index * (barWidth + gap);
            const barHeight = (value / max) * chartHeight;
            const y = padding.top + chartHeight - barHeight;
            const isPeak = value === max && value > 0;
            const gradient = ctx.createLinearGradient(0, y, 0, padding.top + chartHeight);
            gradient.addColorStop(0, '#f56432');
            gradient.addColorStop(0.55, '#e84c1e');
            gradient.addColorStop(1, 'rgba(200, 52, 26, 0.65)');
            ctx.fillStyle = gradient;

            if (isPeak) {
                ctx.shadowColor = 'rgba(232, 76, 30, 0.35)';
                ctx.shadowBlur = 8;
                ctx.shadowOffsetY = -1;
            }

            ctx.beginPath();
            const h = Math.max(1, barHeight);
            const r = Math.min(radius, h);
            ctx.moveTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.lineTo(x + barWidth - r, y);
            ctx.quadraticCurveTo(x + barWidth, y, x + barWidth, y + r);
            ctx.lineTo(x + barWidth, y + h);
            ctx.lineTo(x, y + h);
            ctx.closePath();
            ctx.fill();

            if (isPeak) {
                ctx.shadowColor = 'transparent';
                ctx.shadowBlur = 0;
                ctx.shadowOffsetY = 0;
            }
        });
    }

    function dexIssuesInitMetrics(root) {
        const jsonNode = root.querySelector('[data-dex-issue-metrics]');
        if (!jsonNode || jsonNode.dataset.dexRendered === '1') {
            return;
        }

        jsonNode.dataset.dexRendered = '1';

        let metrics;
        try {
            metrics = JSON.parse(jsonNode.textContent || '{}');
        } catch (error) {
            return;
        }

        const chart = root.querySelector('[data-dex-volume-chart]');
        if (chart) {
            dexIssuesDrawDialogVolumeChart(chart, metrics.hourCounts || []);

            if (!chart.dataset.dexResizeBound) {
                chart.dataset.dexResizeBound = '1';
                let resizeFrame = 0;
                const onResize = () => {
                    window.cancelAnimationFrame(resizeFrame);
                    resizeFrame = window.requestAnimationFrame(() => {
                        dexIssuesDrawDialogVolumeChart(chart, metrics.hourCounts || []);
                    });
                };
                window.addEventListener('resize', onResize, { passive: true });
            }
        }

        const statNode = dexIssuesDialogContent().querySelector('[data-dex-issue-occ24h]');
        if (statNode) {
            statNode.textContent = Number(metrics.occ24h || 0).toLocaleString();
            const statSubNode = statNode.nextElementSibling;
            if (statSubNode) {
                statSubNode.textContent = 'from recent volume';
            }
        }
    }

    function dexIssuesInitDialogPane(root) {
        dexIssuesInitBreadcrumbFilter(root);
        dexIssuesInitSpanFilter(root);
        dexIssuesInitFrameFilter(root);
        dexIssuesInitLifecycle(root);
        dexIssuesHighlightCode(root);
        dexIssuesInitMetrics(root);
    }

    function dexIssuesInitDialogShell() {
        const content = dexIssuesDialogContent();
        const shell = content.querySelector('[data-dex-issue-shell]');
        if (!shell) {
            return;
        }

        dexIssueDialogState.issueId = Number(shell.dataset.dexIssueId || 0);
        dexIssueDialogState.occurrenceId = Number(shell.dataset.dexIssueOccurrence || 0) || null;
        dexIssuesLoadDialogSections(['metrics', 'stack', 'lifecycle']);
    }

    async function dexIssuesLoadDialogSection(tab) {
        const content = dexIssuesDialogContent();
        const shell = content.querySelector('[data-dex-issue-shell]');
        if (!shell) {
            return;
        }

        const pane = content.querySelector(`[data-dex-issue-lazy="${tab}"]`);
        if (!pane) {
            return;
        }

        if (pane.dataset.dexLoaded === '1') {
            dexIssuesInitDialogPane(pane);
            return;
        }

        if (dexIssueDialogState.tabAbortController) {
            dexIssueDialogState.tabAbortController.abort();
        }

        dexIssueDialogState.tabAbortController = new AbortController();
        pane.innerHTML = '<div class="py-4 text-muted">Loading…</div>';

        const url = new URL(`${shell.dataset.dexIssueDialogUrl}/tab/${tab}`, window.location.origin);
        if (dexIssueDialogState.occurrenceId) {
            url.searchParams.set('occ', String(dexIssueDialogState.occurrenceId));
        }

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: dexIssueDialogState.tabAbortController.signal,
            });

            if (!response.ok) {
                throw new Error('Failed to load section.');
            }

            const markup = await response.text();
            const temp = document.createElement('div');
            temp.innerHTML = markup.trim();
            const nextPane = temp.firstElementChild;

            if (nextPane) {
                pane.replaceWith(nextPane);
                nextPane.dataset.dexLoaded = '1';
                dexIssuesInitDialogPane(nextPane);
            } else {
                pane.innerHTML = markup;
                pane.dataset.dexLoaded = '1';
                dexIssuesInitDialogPane(pane);
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            pane.innerHTML = `<div class="dex-loading dex-loading--danger">${dexIssuesEscapeHtml(error.message || 'Failed to load section.')}</div>`;
        }
    }

    async function dexIssuesUpdateIssueStatus(issueId, action) {
        const shell = dexIssuesDialogContent().querySelector('[data-dex-issue-shell]');
        if (!shell) {
            return;
        }

        const urlMap = {
            resolve: shell.dataset.dexIssueResolveUrl,
            ignore:  shell.dataset.dexIssueIgnoreUrl,
        };
        const triggerMap = {
            resolve: shell.querySelector('[data-dex-issue-resolve]'),
            ignore:  shell.querySelector('[data-dex-issue-ignore]'),
        };

        const url = urlMap[action];
        const trigger = triggerMap[action];
        if (!url) {
            return;
        }

        if (trigger) {
            trigger.disabled = true;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(response.status === 404 ? 'Issue not found.' : `Failed to ${action} issue.`);
            }

            const payload = await response.json();
            const nextStatus = payload.issue?.status || (action === 'resolve' ? 'resolved' : 'ignored');
            const badge = shell.querySelector('[data-dex-issue-status-badge]');
            if (badge) {
                badge.textContent = nextStatus.charAt(0).toUpperCase() + nextStatus.slice(1);
                badge.className = dexIssuesDialogStatusChipClass(nextStatus);
            }

            const resolveBtn = triggerMap.resolve;
            const ignoreBtn = triggerMap.ignore;
            if (resolveBtn) {
                resolveBtn.disabled = (nextStatus === 'resolved');
            }
            if (ignoreBtn) {
                ignoreBtn.disabled = (nextStatus === 'ignored');
            }

            if (dexIssuesState.data.summary && payload.summary) {
                dexIssuesState.data.summary = {
                    ...dexIssuesState.data.summary,
                    ...payload.summary,
                };
                dexIssuesRenderSummary();
            }

            const issue = (dexIssuesState.data.issues || []).find((item) => Number(item.id) === Number(issueId));
            if (issue) {
                issue.status = nextStatus;
            }

            dexIssuesCloseActionMenus();
            dexIssuesRenderRows();
            dexIssuesLoad();
        } catch (error) {
            if (trigger) {
                trigger.disabled = false;
            }
        }
    }

    async function dexIssuesResolveIssue(issueId) {
        return dexIssuesUpdateIssueStatus(issueId, 'resolve');
    }

    async function dexIssuesIgnoreIssue(issueId) {
        return dexIssuesUpdateIssueStatus(issueId, 'ignore');
    }

    function dexIssuesRenderSummary() {
        const summary = dexIssuesState.data.summary || {};
        document.getElementById('summary-total').textContent = Number(summary.totalIssues || 0).toLocaleString();
        document.getElementById('summary-open').textContent = Number(summary.openIssues || 0).toLocaleString();
        document.getElementById('summary-regressed').textContent = Number(summary.regressedIssues || 0).toLocaleString();
        document.getElementById('summary-events').textContent = Number(summary.events24h || 0).toLocaleString();

        const trendNode = document.getElementById('summary-trend');
        if (summary.eventsTrendPct === null || summary.eventsTrendPct === undefined) {
            trendNode.textContent = 'No baseline';
            trendNode.style.color = 'var(--muted)';
        } else {
            const pct = Math.round(Number(summary.eventsTrendPct));
            trendNode.textContent = `${pct >= 0 ? '+' : '-'}${Math.abs(pct)}%`;
            trendNode.style.color = pct >= 0 ? '#16a34a' : '#dc2626';
        }

        document.getElementById('count-all').textContent = Number(summary.totalIssues || 0).toLocaleString();
        document.getElementById('count-open').textContent = Number(summary.openIssues || 0).toLocaleString();
        document.getElementById('count-regressed').textContent = Number(summary.regressedIssues || 0).toLocaleString();
        document.getElementById('count-resolved').textContent = Number(summary.resolvedIssues || 0).toLocaleString();
        document.getElementById('count-ignored').textContent = Number(summary.ignoredIssues || 0).toLocaleString();
    }

    function dexIssuesRenderPagination() {
        const pagination = dexIssuesState.data.pagination || {};
        const total = Number(pagination.total || 0);
        const from = Number(pagination.from || 0);
        const to = Number(pagination.to || 0);
        const page = Number(pagination.page || 1);
        const pages = Number(pagination.pages || 1);

        document.getElementById('showingCount').textContent = total === 0
            ? 'Showing 0 issues'
            : `Showing ${from}-${to} of ${total} issues`;
        document.getElementById('pageInfo').textContent = `Page ${page} of ${pages}`;
        document.getElementById('prevPage').disabled = dexIssuesState.loading || !pagination.hasPrev;
        document.getElementById('nextPage').disabled = dexIssuesState.loading || !pagination.hasNext;
    }

    function dexIssuesRenderTabs() {
        document.querySelectorAll('#filterTabs .filter-tab').forEach((button) => {
            button.classList.toggle('active', button.dataset.filter === dexIssuesState.status);
        });
    }

    function dexIssuesRenderChartLabels() {
        const labelsNode = document.getElementById('chartLabels');
        const labels = [];
        const now = new Date();
        const currentHour = now.getHours();

        for (let offset = 24; offset >= 0; offset -= 2) {
            const hour = (currentHour - offset + 24) % 24;
            labels.push(`${String(hour).padStart(2, '0')}:00`);
        }

        labelsNode.innerHTML = labels.map((label) => `<span>${label}</span>`).join('');
    }

    function dexIssuesDrawChart() {
        const canvas = document.getElementById('volumeChart');
        if (!canvas || !canvas.offsetWidth) {
            return;
        }

        const chart = dexIssuesState.data.chart || {};
        const data = Array.isArray(chart.hourly) ? chart.hourly : new Array(24).fill(0);
        const dpr = window.devicePixelRatio || 1;
        canvas.width = canvas.offsetWidth * dpr;
        canvas.height = 110 * dpr;
        canvas.style.height = '110px';

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);

        const width = canvas.offsetWidth;
        const height = 110;
        const padding = {top: 8, bottom: 4, left: 28, right: 4};
        const max = Math.max(...data, 1);
        const x = (index) => padding.left + (index / (data.length - 1)) * (width - padding.left - padding.right);
        const y = (value) => height - padding.bottom - (value / max) * (height - padding.top - padding.bottom) * 0.92;

        [0, Math.round(max / 2), max].forEach((value) => {
            const yPos = y(value);
            ctx.beginPath();
            ctx.moveTo(padding.left, yPos);
            ctx.lineTo(width - padding.right, yPos);
            ctx.strokeStyle = 'rgba(15,18,25,0.05)';
            ctx.lineWidth = 1;
            ctx.setLineDash(value === 0 ? [] : [2, 4]);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.fillStyle = '#9398a2';
            ctx.font = "10px ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace";
            ctx.fillText(value, 2, yPos + 3);
        });

        const gradient = ctx.createLinearGradient(0, 0, 0, height);
        gradient.addColorStop(0, 'rgba(232, 76, 30, 0.28)');
        gradient.addColorStop(0.5, 'rgba(232, 76, 30, 0.10)');
        gradient.addColorStop(1, 'rgba(232, 76, 30, 0)');

        ctx.beginPath();
        ctx.moveTo(x(0), y(data[0] || 0));
        data.forEach((value, index) => {
            if (index > 0) {
                ctx.lineTo(x(index), y(value));
            }
        });
        ctx.lineTo(x(data.length - 1), height - padding.bottom);
        ctx.lineTo(x(0), height - padding.bottom);
        ctx.closePath();
        ctx.fillStyle = gradient;
        ctx.fill();

        ctx.shadowColor = 'rgba(232, 76, 30, 0.28)';
        ctx.shadowBlur = 6;
        ctx.shadowOffsetY = 1;
        ctx.beginPath();
        ctx.moveTo(x(0), y(data[0] || 0));
        data.forEach((value, index) => {
            if (index > 0) {
                ctx.lineTo(x(index), y(value));
            }
        });
        ctx.strokeStyle = '#e84c1e';
        ctx.lineWidth = 2;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.stroke();
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetY = 0;

        const peakIndex = data.indexOf(max);
        if (peakIndex >= 0 && max > 0) {
            const px = x(peakIndex);
            const py = y(max);
            ctx.beginPath();
            ctx.arc(px, py, 4, 0, Math.PI * 2);
            ctx.fillStyle = '#fff';
            ctx.fill();
            ctx.beginPath();
            ctx.arc(px, py, 2.5, 0, Math.PI * 2);
            ctx.fillStyle = '#e84c1e';
            ctx.fill();
        }
    }

    function dexIssuesRender() {
        if ('summary' in dexIssuesState.data) {
            dexIssuesRenderSummary();
        }
        dexIssuesRenderTabs();
        dexIssuesRenderRows();
        dexIssuesRenderPagination();
        if ('chart' in dexIssuesState.data) {
            dexIssuesRenderChartLabels();
            dexIssuesDrawChart();
        }
    }

    async function dexIssuesLoad() {
        dexIssuesState.loading = true;
        dexIssuesRenderRows();
        dexIssuesRenderPagination();

        const params = new URLSearchParams({
            status: dexIssuesState.status,
            q: dexIssuesState.q,
            page: String(dexIssuesState.page),
            per_page: String(dexIssuesState.perPage),
        });

        try {
            const response = await fetch(`${dexIssuesDataUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load issues');
            }

            const payload = await response.json();
            dexIssuesState.data = {
                ...dexIssuesState.data,
                ...payload,
                issues: Array.isArray(payload.issues) ? payload.issues : (dexIssuesState.data.issues || []),
                pagination: payload.pagination || dexIssuesState.data.pagination || {},
            };
        } catch (error) {
            dexIssuesState.data = {
                ...dexIssuesState.data,
                issues: [],
                pagination: {
                    page: dexIssuesState.page,
                    pages: dexIssuesState.page,
                    total: 0,
                    from: 0,
                    to: 0,
                    hasPrev: false,
                    hasNext: false,
                },
            };
        } finally {
            dexIssuesState.loading = false;
            dexIssuesRender();
        }
    }

    document.getElementById('filterTabs').addEventListener('click', (event) => {
        const button = event.target.closest('[data-filter]');
        if (!button || dexIssuesState.loading) {
            return;
        }

        dexIssuesState.status = button.dataset.filter;
        dexIssuesState.page = 1;
        dexIssuesLoad();
    });

    document.getElementById('searchInput').addEventListener('input', (event) => {
        dexIssuesState.q = event.target.value;
        dexIssuesState.page = 1;
        clearTimeout(dexIssuesState.searchTimer);
        dexIssuesState.searchTimer = setTimeout(() => {
            dexIssuesLoad();
        }, 250);
    });

    document.getElementById('prevPage').addEventListener('click', () => {
        const pagination = dexIssuesState.data.pagination || {};
        if (dexIssuesState.loading || !pagination.hasPrev) {
            return;
        }

        dexIssuesState.page -= 1;
        dexIssuesLoad();
    });

    document.getElementById('nextPage').addEventListener('click', () => {
        const pagination = dexIssuesState.data.pagination || {};
        if (dexIssuesState.loading || !pagination.hasNext) {
            return;
        }

        dexIssuesState.page += 1;
        dexIssuesLoad();
    });

    document.getElementById('issuesTbody').addEventListener('click', (event) => {
        const row = event.target.closest('tr[data-id]');
        if (!row) {
            return;
        }

        dexIssuesOpenDialog(row.dataset.id, null, row);
    });

    dexIssuesDialogOverlay().addEventListener('click', (event) => {
        const closeButton = event.target.closest('[data-dex-issue-close]');
        if (closeButton) {
            dexIssuesCloseActionMenus();
            dexIssuesCloseDialog();
            return;
        }

        const menuTrigger = event.target.closest('[data-dex-action-menu-trigger]');
        if (menuTrigger) {
            event.stopPropagation();
            const menu = menuTrigger.closest('[data-dex-action-menu]');
            if (menu) {
                dexIssuesToggleActionMenu(menu);
            }
            return;
        }

        const copyButton = event.target.closest('.ms-copy');
        if (copyButton) {
            const text = copyButton.getAttribute('data-copy') || copyButton.textContent || '';
            dexIssuesCopyText(text.trim());
            const label = copyButton.querySelector('span');
            const originalLabel = label ? label.textContent : '';
            copyButton.classList.add('is-success');
            if (label) {
                label.textContent = 'Copied!';
            }
            setTimeout(() => {
                copyButton.classList.remove('is-success');
                if (label) {
                    label.textContent = originalLabel;
                }
                dexIssuesCloseActionMenus();
            }, 800);
            return;
        }

        const ignoreButton = event.target.closest('[data-dex-issue-ignore]');
        if (ignoreButton) {
            const issueId = Number(ignoreButton.dataset.dexIssueIgnore || 0);
            if (issueId > 0) {
                dexIssuesIgnoreIssue(issueId);
            }
            return;
        }

        if (!event.target.closest('[data-dex-action-menu]')) {
            dexIssuesCloseActionMenus();
        }

        const paginateButton = event.target.closest('[data-dex-issue-paginate]');
        if (paginateButton && !paginateButton.disabled) {
            const targetOccurrenceId = Number(paginateButton.dataset.dexIssuePaginate || 0);
            const shell = dexIssuesDialogContent().querySelector('[data-dex-issue-shell]');
            const issueId = Number(shell?.dataset.dexIssueId || 0);
            if (issueId > 0 && targetOccurrenceId > 0) {
                dexIssuesPaginateDialog(issueId, targetOccurrenceId, paginateButton);
            }
            return;
        }

        const resolveButton = event.target.closest('[data-dex-issue-resolve]');
        if (resolveButton) {
            const issueId = Number(resolveButton.dataset.dexIssueResolve || 0);
            if (issueId > 0) {
                dexIssuesResolveIssue(issueId);
            }
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const openMenu = document.querySelector('.dex-v2 [data-dex-action-menu] .dex-action-menu__panel:not([hidden])');
        if (openMenu) {
            dexIssuesCloseActionMenus();
            const trigger = openMenu.parentElement?.querySelector('[data-dex-action-menu-trigger]');
            if (trigger) {
                trigger.focus();
            }
            return;
        }

        if (!dexIssuesDialogOverlay().hidden) {
            dexIssuesCloseDialog();
        }
    });

    window.addEventListener('resize', dexIssuesDrawChart);
    window.addEventListener('DOMContentLoaded', dexIssuesRender);
</script>
