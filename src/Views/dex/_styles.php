<!-- Tabler CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/tabler-icons.min.css"/>
<style>
    /* ─── DEX Brand Tokens ─────────────────────────────────────────── */
    :root {
        /* Brand */
        --dex-accent:        #e84c1e;
        --dex-accent-soft:   #f56432;
        --dex-accent-lt:     #fff1ed;
        --dex-accent-lt-2:   #ffe4d8;
        --dex-accent-dark:   #c8341a;
        --dex-accent-rgb:    232, 76, 30;
        --dex-accent-grad:   linear-gradient(135deg, #f56432 0%, #e84c1e 55%, #c8341a 100%);
        --dex-accent-ring:   0 0 0 3px rgba(232, 76, 30, .18);

        /* Surfaces */
        --dex-dark:          #0f1219;
        --dex-dark-nav:      #0d1017;
        --dex-dark-nav-2:    #161a23;
        --dex-surface:       #ffffff;
        --dex-surface-soft:  #fbfbfd;
        --dex-bg:            #f5f5f8;
        --dex-bg-tint:       #f0eef1;

        /* Lines & text */
        --dex-border:        #e7e7ec;
        --dex-border-md:     #d6d7dd;
        --dex-border-strong: #c1c3cb;
        --dex-text:          #15171c;
        --dex-text-soft:     #2a2c33;
        --dex-muted:         #65676f;
        --dex-muted-soft:    #9398a2;

        /* Shape */
        --dex-radius-sm:     6px;
        --dex-radius:        10px;
        --dex-radius-lg:     14px;
        --dex-radius-pill:   999px;

        /* Elevation */
        --dex-shadow-1:      0 1px 2px rgba(15, 18, 25, .04), 0 1px 3px rgba(15, 18, 25, .04);
        --dex-shadow-2:      0 2px 4px rgba(15, 18, 25, .05), 0 6px 18px rgba(15, 18, 25, .06);
        --dex-shadow-3:      0 8px 24px rgba(15, 18, 25, .10), 0 16px 48px rgba(15, 18, 25, .10);
        --dex-shadow-accent: 0 6px 20px rgba(232, 76, 30, .22);

        /* Semantic */
        --dex-success:       #16a34a;
        --dex-success-lt:    #e8f9ee;
        --dex-success-bd:    #bbf7d0;
        --dex-warning:       #d97706;
        --dex-warning-lt:    #fff5e0;
        --dex-warning-bd:    #fde68a;
        --dex-danger:        #dc2626;
        --dex-danger-lt:     #fdecec;
        --dex-danger-bd:     #fecaca;
        --dex-info:          #2563eb;
        --dex-info-lt:       #e6efff;
        --dex-info-bd:       #c7d8ff;

        /* HTTP method colors */
        --dex-m-get-bg:      #e6efff;
        --dex-m-get-fg:      #1d4ed8;
        --dex-m-post-bg:     #e8f9ee;
        --dex-m-post-fg:     #15803d;
        --dex-m-put-bg:      #fff5e0;
        --dex-m-put-fg:      #b45309;
        --dex-m-patch-bg:    #fff1ed;
        --dex-m-patch-fg:    #c2410c;
        --dex-m-delete-bg:   #fdecec;
        --dex-m-delete-fg:   #b91c1c;
        --dex-m-other-bg:    #eef0f3;
        --dex-m-other-fg:    #4b5563;
    }

    /* ─── Base ─────────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }

    html, body {
        height: 100%;
    }

    body {
        color: var(--dex-text);
        background:
            radial-gradient(1100px 380px at 50% -120px, rgba(232, 76, 30, .045), rgba(232, 76, 30, 0) 70%),
            var(--dex-bg);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
        margin: 0;
    }

    ::selection {
        background: none;
    }

    .page {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .page-wrapper {
        flex: 1;
    }

    /* ─── Navbar ───────────────────────────────────────────────────── */
    .dex-navbar {
        background:
            linear-gradient(180deg, var(--dex-dark-nav-2) 0%, var(--dex-dark-nav) 100%);
        border-bottom: 1px solid rgba(255, 255, 255, .06);
        position: sticky;
        top: 0;
        z-index: 900;
        box-shadow: 0 1px 0 rgba(0, 0, 0, .25), 0 6px 18px rgba(0, 0, 0, .12);
    }

    .dex-navbar::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg,
            rgba(232, 76, 30, 0) 0%,
            rgba(232, 76, 30, .55) 28%,
            rgba(232, 76, 30, .55) 72%,
            rgba(232, 76, 30, 0) 100%);
        opacity: .55;
        pointer-events: none;
    }

    .dex-navbar { position: sticky; }

    .dex-navbar__inner {
        display: flex;
        align-items: center;
        height: 56px;
        gap: 16px;
    }

    .dex-navbar__brand {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        flex-shrink: 0;
    }

    .dex-navbar__logo-mark {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        filter: drop-shadow(0 2px 6px rgba(232, 76, 30, .45));
    }

    .dex-navbar__wordmark {
        font-size: 16px;
        font-weight: 800;
        letter-spacing: .02em;
        color: #fff;
        line-height: 1;
        background: linear-gradient(180deg, #ffffff 0%, #d8dae1 110%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .dex-navbar__center {
        flex: 1;
        display: flex;
        align-items: center;
        padding-left: 4px;
    }

    .dex-navbar__section-tag {
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .42);
        border-left: 1px solid rgba(255, 255, 255, .12);
        padding-left: 12px;
        margin-left: 4px;
    }

    .dex-navbar__actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }

    .dex-navbar__link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        font-size: 12.5px;
        font-weight: 500;
        color: rgba(255, 255, 255, .68);
        text-decoration: none;
        border-radius: 7px;
        transition: color .15s, background .15s, transform .15s;
    }

    .dex-navbar__link:hover {
        color: #fff;
        background: rgba(255, 255, 255, .08);
    }

    .dex-navbar__link--gh {
        border: 1px solid rgba(255, 255, 255, .14);
        color: rgba(255, 255, 255, .82);
        margin-left: 4px;
        background: rgba(255, 255, 255, .03);
    }

    .dex-navbar__link--gh:hover {
        border-color: rgba(255, 255, 255, .28);
        background: rgba(255, 255, 255, .08);
        color: #fff;
        transform: translateY(-1px);
    }

    @media (max-width: 580px) {
        .dex-navbar__section-tag { display: none; }
        .dex-navbar__link:not(.dex-navbar__link--gh) { display: none; }
        .dex-navbar__inner { height: 48px; }
    }

    /* ─── Footer ───────────────────────────────────────────────────── */
    .dex-footer {
        border-top: 1px solid var(--dex-border);
        background: linear-gradient(180deg, var(--dex-surface) 0%, var(--dex-surface-soft) 100%);
        padding: 16px 0;
        margin-top: 16px;
    }

    .dex-footer__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .dex-footer__copy {
        font-size: 12px;
        color: var(--dex-muted);
    }

    .dex-footer__copy a {
        color: var(--dex-accent);
        font-weight: 600;
        text-decoration: none;
    }

    .dex-footer__copy a:hover {
        text-decoration: underline;
    }

    .dex-footer__links {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dex-footer__links a {
        font-size: 12px;
        color: var(--dex-muted);
        text-decoration: none;
        transition: color .15s;
    }

    .dex-footer__links a:hover {
        color: var(--dex-text);
    }

    @media (max-width: 580px) {
        .dex-footer__inner { justify-content: center; }
        .dex-footer__links { display: none; }
    }

    /* ─── Old powered-by (remove legacy div) ──────────────────────── */
    .powered-by { display: none; }

    /* ─── Tabler overrides ─────────────────────────────────────────── */
    .card-body {
        color: var(--dex-text);
    }

    .ms-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    .dex-ws-nowrap {
        white-space: nowrap;
    }

    .badge.bg-open {
        background-color: color-mix(in srgb, var(--tblr-green) calc(var(--tblr-bg-opacity, 1) * 100%), transparent) !important;
    }

    .ms-small {
        font-size: .5125rem;
    }

    .ms-spark svg {
        display: block;
    }

    .ms-spark svg rect {
        fill: currentColor;
    }

    .ms-spark-wrap {
        display: inline-block;
        position: relative;
        height: var(--ms-spark-h, 18px);
        padding-right: 10px; /* room for label outside the spark */
    }

    .ms-spark-svg {
        display: block;
    }

    .ms-spark-max {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        position: absolute;
        right: 0;
        top: var(--ms-max-y, 6px);
        transform: translateY(-50%);
        font-size: 0.3rem;
        line-height: 0;
        opacity: .88;
        pointer-events: none;
        user-select: none;
    }

    .vertical-divider {
        display: inline-grid;
        grid-auto-flow: column dense;
        gap: 6px;
        justify-content: start;
        align-items: center;
        color: rgb(108, 102, 116);
        font-size: 12px;
        white-space: nowrap;
        line-height: 1.2;
        height: 10px;
        width: 1px;
        background-color: rgb(182, 182, 182);
        border-radius: 1px;
        margin-left: 5px;
        margin-right: 5px;
    }

    .ms-kv-table td {
        vertical-align: top;
    }

    /* Equal-height cards within a row */
    .ms-eq > [class*="col-"] {
        display: flex;
    }

    .ms-eq > [class*="col-"] > .card {
        width: 100%;
    }

    /* Code blocks (Tabler-ish) */
    .ms-code-wrap {
        position: relative;
    }

    .ms-code-actions {
        position: absolute;
        top: .4rem;
        right: .4rem;
        z-index: 2;
        display: flex;
        gap: .25rem;
    }

    .ms-code {
        background: var(--tblr-bg-surface-secondary);
        border: 1px solid var(--tblr-border-color);
        border-radius: .5rem;
        padding: .75rem;
        font-size: .75rem;
        line-height: 1.25;
        overflow: auto;
        white-space: pre;
    }

    .ms-code code {
        background: transparent;
    }

    .ms-code-line {
        display: block;
        padding-right: 1rem;
    }

    .ms-code-line.is-target {
        background: rgba(6, 111, 209, .12);
        border-radius: .25rem;
    }

    .ms-code-no {
        display: inline-block;
        width: 4.5em;
        color: var(--tblr-muted);
        user-select: none;
    }

    /* Tabs */
    .ms-tabs .nav-link {
        padding-top: .6rem;
        padding-bottom: .6rem;
    }

    /* Breadcrumb timeline */
    .ms-timeline {
        position: relative;
        padding-left: 18px;
    }

    .ms-timeline:before {
        content: "";
        position: absolute;
        left: 7px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--tblr-border-color);
        opacity: .8;
    }

    .ms-timeline-item {
        position: relative;
        padding: .5rem .25rem .5rem 0;
    }

    .ms-timeline-item:before {
        content: "";
        position: absolute;
        left: -14px;
        top: 1.05rem;
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--tblr-bg-surface);
        border: 2px solid var(--tblr-primary);
    }

    .ms-timeline-item .meta {
        display: flex;
        gap: .5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .ms-timeline-item .msg {
        margin-top: .2rem;
    }

    /* Span bars */
    .ms-span-track {
        position: relative;
        height: 10px;
        border-radius: 999px;
        background: var(--tblr-bg-surface-secondary);
        overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(15, 18, 25, .04);
    }

    .ms-span-track > span {
        position: absolute;
        top: 0;
        bottom: 0;
        border-radius: 999px;
        background: var(--dex-accent-grad);
        opacity: .9;
        box-shadow: 0 1px 4px rgba(232, 76, 30, .25);
    }

    /* Tag breakdown bars (Sentry-ish) */
    .ms-tagrow {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: .65rem;
    }

    .ms-tagkey {
        width: 120px;
        font-weight: 600;
        color: var(--tblr-muted);
    }

    .ms-tagbar {
        flex: 1;
        height: 8px;
        background: var(--tblr-bg-surface-secondary);
        border-radius: 999px;
        overflow: hidden;
        display: flex;
        box-shadow: inset 0 1px 0 rgba(15, 18, 25, .04);
    }

    .ms-tagseg {
        height: 100%;
        display: block;
    }

    .ms-tagval {
        width: 240px;
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        align-items: center;
    }

    .ms-tagpct {
        font-weight: 600;
        color: var(--tblr-muted);
        min-width: 3.5ch;
        text-align: right;
    }

    .ms-tagtext {
        max-width: 190px;
    }

    @media (max-width: 991px) {
        .ms-tagkey {
            width: 96px;
        }

        .ms-tagval {
            width: 200px;
        }

        .ms-tagtext {
            max-width: 150px;
        }
    }

    /* Issues list V2 styles (moved from issues_list.php) */
    .dex-issues-v2 {
        --accent: var(--dex-accent);
        --accent-lt: var(--dex-accent-lt);
        --accent-rgb: var(--dex-accent-rgb);
        --bg: var(--dex-bg);
        --surface: var(--dex-surface);
        --border: var(--dex-border);
        --border-md: var(--dex-border-md);
        --text: var(--dex-text);
        --muted: var(--dex-muted);
        --radius: var(--dex-radius);
        --row-pad-y: 14px;
        --thead-bg: #f7f7fa;
        --hover-bg: #fafafb;
        color: var(--text);
    }

    .dex-issues-v2 .page-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 32px 0 56px;
    }

    .dex-issues-v2 .section-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .1em;
        color: var(--muted);
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .dex-issues-v2 .page-title {
        font-size: 26px;
        letter-spacing: -.015em;
        font-weight: 700;
        line-height: 1.2;
        color: var(--text);
    }

    .dex-issues-v2 .page-subtitle {
        font-size: 13px;
        color: var(--muted);
        margin-top: 4px;
    }

    .dex-issues-v2 .stat-card,
    .dex-issues-v2 .chart-card,
    .dex-issues-v2 .filter-bar,
    .dex-issues-v2 .issues-table-wrap {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--dex-shadow-1);
    }

    .dex-issues-v2 .stat-card {
        position: relative;
        padding: 18px 20px 16px;
        flex: 1 1 0;
        min-width: 0;
        overflow: hidden;
        transition: box-shadow .18s ease, transform .18s ease, border-color .18s ease;
    }

    .dex-issues-v2 .stat-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        color: var(--muted);
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .dex-issues-v2 .stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }

    .dex-issues-v2 .stat-sub,
    .dex-issues-v2 .chart-subtitle,
    .dex-issues-v2 .table-footer {
        font-size: 12px;
        color: var(--muted);
    }

    .dex-issues-v2 .stat-value {
        background: linear-gradient(180deg, var(--dex-text) 0%, var(--dex-text-soft) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .dex-issues-v2 .stat-trend {
        font-size: 12px;
        color: var(--dex-success);
        margin-top: 4px;
        font-weight: 600;
    }

    .dex-issues-v2 .chart-card {
        padding: 18px 20px 20px;
    }

    .dex-issues-v2 .chart-title {
        font-weight: 600;
        font-size: 13.5px;
        color: var(--text);
    }

    .dex-issues-v2__chart-legend {
        font-size: 11px;
        color: var(--muted);
    }

    .dex-issues-v2__chart-legend-line {
        width: 22px;
        height: 3px;
        background: var(--dex-accent-grad);
        display: inline-block;
        border-radius: 2px;
        box-shadow: 0 0 6px rgba(232, 76, 30, .35);
    }

    .dex-issues-v2 #volumeChart {
        width: 100%;
        height: 110px;
        display: block;
    }

    .dex-issues-v2 .filter-bar {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom: none;
        padding: 0 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .dex-issues-v2 .filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
    }

    .dex-issues-v2 .filter-tab {
        position: relative;
        border: none;
        background: none;
        cursor: pointer;
        padding: 12px 14px 10px;
        font-size: 13px;
        font-weight: 500;
        color: var(--muted);
        border-bottom: 2px solid transparent;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color .15s ease;
    }

    .dex-issues-v2 .filter-tab:hover {
        color: var(--text);
    }

    .dex-issues-v2 .filter-tab.active {
        color: var(--text);
        font-weight: 600;
        border-bottom: 2.5px solid var(--accent);
    }

    .dex-issues-v2 .filter-tab.active::after {
        content: "";
        position: absolute;
        left: 14px;
        right: 14px;
        bottom: -2px;
        height: 6px;
        pointer-events: none;
    }

    .dex-issues-v2 .filter-count {
        font-size: 11px;
        padding: 1px 6px;
        border-radius: 10px;
        background: var(--border);
        color: var(--muted);
        font-weight: 600;
    }

    .dex-issues-v2 .filter-tab.active .filter-count {
        background: var(--accent-lt);
        color: var(--accent);
    }

    .dex-issues-v2 .tab-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .dex-issues-v2 .search-wrap {
        position: relative;
        display: flex;
        align-items: center;
        min-width: 260px;
    }

    .dex-issues-v2 .search-icon {
        position: absolute;
        left: 10px;
        color: var(--muted);
        font-size: 13px;
        pointer-events: none;
    }

    .dex-issues-v2 .search-input {
        border: 1px solid var(--border-md);
        border-radius: 8px;
        padding: 7px 12px 7px 30px;
        font-size: 13px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        width: 100%;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .dex-issues-v2 .search-input::placeholder {
        color: var(--dex-muted-soft);
    }

    .dex-issues-v2 .search-input:hover {
        border-color: var(--dex-border-strong);
    }

    .dex-issues-v2 .search-input:focus {
        border-color: var(--accent);
        background: var(--surface);
        box-shadow: var(--dex-accent-ring);
    }

    .dex-issues-v2 .issues-table-wrap {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .dex-issues-v2 table.issues-table {
        width: 100%;
        min-width: 860px;
        border-collapse: collapse;
    }

    .dex-issues-v2 table.issues-table thead tr {
        border-bottom: 1px solid var(--border);
        background: var(--thead-bg);
    }

    .dex-issues-v2 table.issues-table thead th {
        padding: 10px 14px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .07em;
        color: var(--muted);
        text-transform: uppercase;
        white-space: nowrap;
    }

    .dex-issues-v2__th-age {
        padding-right: 8px !important;
    }

    .dex-issues-v2__th-chevron {
        width: 32px;
    }

    .dex-issues-v2 table.issues-table tbody tr {
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        transition: background .14s ease, box-shadow .14s ease;
    }

    .dex-issues-v2 table.issues-table tbody tr:last-child {
        border-bottom: none;
    }

    .dex-issues-v2 table.issues-table tbody tr:hover {
        background: var(--hover-bg);
        box-shadow: inset 3px 0 0 rgba(var(--accent-rgb), .35);
    }

    .dex-issues-v2 table.issues-table td {
        padding: var(--row-pad-y) 14px;
        vertical-align: middle;
    }

    .dex-issues-v2__td-main {
        padding: var(--row-pad-y) 14px var(--row-pad-y) 12px !important;
        min-width: 0;
    }

    .dex-issues-v2__issue-class {
        font-size: 13.5px;
        color: var(--text);
        font-weight: 600;
    }

    .dex-issues-v2__route {
        font-size: 11.5px;
        color: #444549;
    }

    .dex-issues-v2__metric {
        font-size: 14px;
    }

    .dex-issues-v2__metric--total {
        font-weight: 700;
        color: var(--text);
    }

    .dex-issues-v2__metric--24h {
        font-weight: 600;
    }

    .dex-issues-v2__metric-sub {
        font-size: 11px;
        color: var(--muted);
    }

    .dex-issues-v2__metric--muted {
        color: var(--muted);
    }

    .dex-issues-v2__metric--normal {
        color: var(--text);
    }

    .dex-issues-v2__metric--hot {
        color: #be123c;
    }

    .dex-issues-v2__last-seen {
        font-size: 13px;
        color: var(--muted);
    }

    .dex-issues-v2__td-age {
        padding-right: 8px !important;
    }

    .dex-issues-v2 .dex-loading {
        color: var(--muted);
        padding: 20px 16px;
    }

    .dex-issues-v2 .dex-loading--danger {
        color: #b91c1c;
    }

    .dex-issues-v2 .td-severity {
        width: 4px;
        padding: 0 !important;
    }

    .dex-issues-v2 .severity-strip {
        width: 4px;
        min-height: 100px;
        border-radius: 2px 0 0 2px;
    }

    .dex-issues-v2 .td-chevron {
        color: var(--muted);
        font-size: 18px;
        padding: var(--row-pad-y) 16px !important;
    }

    .dex-issues-v2 .dex-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .03em;
        white-space: nowrap;
        line-height: 1.5;
    }

    .dex-issues-v2 .issue-border {
        border-left: solid 4px #ccc;
        border-radius: 0px;
        box-shadow: inset 1px 0 0 rgba(0, 0, 0, .04);
    }

    .dex-issues-v2 .issue-border-open {
        border-color: #d63939;
        box-shadow: inset 1px 0 0 rgba(214, 57, 57, .25);
    }

    .tab-dot.open{
        background: #d63939;
    }

    .dex-issues-v2 .issue-border-regressed {
        border-color: #f76707;
    }

    .tab-dot.regressed{
        background: #f76707;
    }

    .dex-issues-v2 .issue-border-resolved {
        border-color: #2fb344
    }

    .tab-dot.resolved{
        background: #2fb344;
    }

    .dex-issues-v2 .issue-border-ignored {
        border-color: #9ca3af
    }

    .tab-dot.ignored{
        background: #9ca3af;
    }

    .dex-issues-v2 .badge-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .dex-issues-v2 .badge-open {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .dex-issues-v2 .badge-open .badge-dot {
        background: #16a34a;
    }

    .dex-issues-v2 .badge-regressed {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .dex-issues-v2 .badge-regressed .badge-dot {
        background: #d97706;
    }

    .dex-issues-v2 .badge-resolved {
        background: #f9fafb;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .dex-issues-v2 .badge-resolved .badge-dot {
        background: #6b7280;
    }

    .dex-issues-v2 .badge-ignored {
        background: #f9fafb;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    .dex-issues-v2 .badge-ignored .badge-dot {
        background: #9ca3af;
    }

    .dex-issues-v2 .badge-fatal {
        background: #fff1f2;
        color: #be123c;
        border: 1px solid #fecdd3;
    }

    .dex-issues-v2 .badge-error {
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .dex-issues-v2 .badge-warning {
        background: #fefce8;
        color: #a16207;
        border: 1px solid #fef08a;
    }

    .dex-issues-v2 .method-pill {
        font-size: 9px;
        font-weight: 600;
        padding: 1px 5px;
        border-radius: 4px;
        white-space: nowrap;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        text-transform: uppercase;
    }

    .dex-issues-v2 .method-GET {
        color: var(--dex-m-get-fg);
        background-color: var(--dex-m-get-bg);
    }

    .dex-issues-v2 .method-POST {
        color: var(--dex-m-post-fg);
        background-color: var(--dex-m-post-bg);
    }

    .dex-issues-v2 .method-PUT,
    .dex-issues-v2 .method-PATCH {
        color: var(--dex-m-put-fg);
        background-color: var(--dex-m-put-bg);
    }

    .dex-issues-v2 .method-HEAD,
    .dex-issues-v2 .method-OPTIONS,
    .dex-issues-v2 .method-CONNECT,
    .dex-issues-v2 .method-TRACE {
        color: var(--dex-m-other-fg);
        background-color: var(--dex-m-other-bg);
    }

    .dex-issues-v2 .method-DELETE {
        color: var(--dex-m-delete-fg);
        background-color: var(--dex-m-delete-bg);
    }

    .dex-issues-v2 .sparkline {
        display: block;
        overflow: visible;
    }

    .dex-issues-v2 .age-chip {
        font-size: 11.5px;
        font-weight: 600;
        padding: 2px 9px;
        border-radius: var(--dex-radius-pill);
        white-space: nowrap;
        display: inline-block;
        line-height: 1.5;
        box-shadow: inset 0 0 0 1px rgba(15, 18, 25, .04);
    }

    .dex-issues-v2 .age-old {
        background: #fff7ed;
        color: #c2410c;
    }

    .dex-issues-v2 .age-recent {
        background: #fff1f2;
        color: #be123c;
    }

    .dex-issues-v2 .age-done {
        background: #f3f4f6;
        color: #6b7280;
    }

    .dex-issues-v2 .env-tag {
        font-size: 11px;
        color: var(--muted);
        background: var(--border);
        padding: 1px 6px;
        border-radius: 3px;
        border: 1px solid var(--border);
    }

    .dex-issues-v2 .table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 14px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .dex-issues-v2 .footer-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .dex-issues-v2 .empty-row td {
        padding: 40px;
        text-align: center;
        color: var(--muted);
        font-size: 13px;
    }

    .dex-issues-v2 .chart-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 4px;
        font-size: 10px;
        color: #9ca3af;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
    }

    .dex-issue-overlay[hidden] {
        display: none !important;
    }

    .dex-issue-overlay {
        position: fixed;
        inset: 0;
        z-index: 1055;
        display: flex;
        align-items: stretch;
        justify-content: flex-end;
    }

    .dex-issue-overlay__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(13, 16, 23, 0.50);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    .dex-issue-overlay__panel {
        position: relative;
        margin-left: auto;
        width: min(1100px, 100vw);
        height: 100vh;
        background: var(--dex-bg);
        box-shadow: -24px 0 64px rgba(13, 16, 23, .35), -1px 0 0 rgba(0, 0, 0, .08);
        border: 0;
        outline: none;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .dex-issue-overlay__panel:hover,
    .dex-issue-overlay__panel:active,
    .dex-issue-overlay__panel:focus,
    .dex-issue-overlay__panel:focus-visible,
    .dex-issue-overlay__panel:focus-within {
        border: 0;
        outline: none;
        box-shadow: -24px 0 64px rgba(13, 16, 23, .35), -1px 0 0 rgba(0, 0, 0, .08);
    }

    .dex-issue-overlay__content {
        overflow: auto;
        padding: 20px;
        height: 100%;
    }

    .dex-issue-overlay__loading,
    .dex-issue-overlay__error {
        min-height: 240px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 14px;
    }

    .dex-issue-dialog-shell .nav-link {
        cursor: pointer;
    }

    .dex-issue-dialog-shell .tab-pane {
        display: none;
    }

    .dex-issue-dialog-shell .tab-pane.active.show {
        display: block;
    }

    @media (max-width: 860px) {
        .dex-issue-overlay__panel {
            width: 100vw;
        }

        .dex-issue-overlay__content {
            padding: 14px;
        }
    }

    @media (max-width: 860px) {
        .dex-issues-v2 #statCards {
            flex-wrap: wrap !important;
        }

        .dex-issues-v2 #statCards .stat-card {
            flex: 1 1 calc(50% - 8px);
            min-width: 160px;
        }
    }

    @media (max-width: 580px) {
        .dex-issues-v2 .filter-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
            padding: 8px 12px;
        }

        .dex-issues-v2 .filter-tabs {
            overflow-x: auto;
            flex-wrap: nowrap;
        }

        .dex-issues-v2 .search-wrap {
            min-width: 0;
        }

        .dex-issues-v2 #statCards .stat-card {
            flex: 1 1 100%;
        }

        .dex-issues-v2 .chart-card {
            display: none;
        }

        .dex-issues-v2 table.issues-table thead th,
        .dex-issues-v2 table.issues-table td {
            padding-left: 12px;
            padding-right: 12px;
        }
    }

    /* Issue dialog V2 styles (moved from _dialog_v2_styles.php) */
    .dex-v2 {
        --accent: var(--dex-accent);
        --accent-lt: var(--dex-accent-lt);
        --accent-dark: var(--dex-accent-dark);
        --bg: var(--dex-bg);
        --surface: var(--dex-surface);
        --border: var(--dex-border);
        --border-md: var(--dex-border-md);
        --text: var(--dex-text);
        --muted: var(--dex-muted);
        --radius: var(--dex-radius);
        --success: var(--dex-success);
        --danger: var(--dex-danger);
        --warning: var(--dex-warning);
        color: var(--text);
        font-size: 14px;
    }

    .dex-v2 *, .dex-v2 *::before, .dex-v2 *::after {
        box-sizing: border-box;
    }

    .dex-v2 .page-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 8px 4px 28px;
    }

    .dex-v2 .dex-crumbs {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .1em;
        color: var(--muted);
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0;
        padding: 5px 10px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--dex-radius-pill);
        box-shadow: var(--dex-shadow-1);
    }

    .dex-v2 .dex-crumbs .sep {
        margin: 0 6px;
        color: var(--border-md);
    }

    .dex-v2 .dex-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .dex-v2 .btn-action {
        border: 1px solid var(--border-md);
        border-radius: 7px;
        background: var(--surface);
        padding: 6px 13px;
        font-size: 13px;
        color: var(--muted);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
        text-decoration: none;
        box-shadow: 0 1px 0 rgba(15, 18, 25, .03);
        transition: background .15s, color .15s, border-color .15s, box-shadow .15s, transform .15s;
    }

    .dex-v2 .btn-action:hover {
        background: var(--surface);
        color: var(--text);
        border-color: var(--dex-border-strong);
        box-shadow: 0 2px 8px rgba(15, 18, 25, .07);
    }

    .dex-v2 .btn-action:active {
        background: var(--bg);
        transform: translateY(0);
        box-shadow: inset 0 1px 2px rgba(15, 18, 25, .06);
    }

    .dex-v2 .btn-action:focus-visible {
        outline: none;
        border-color: var(--accent);
        box-shadow: var(--dex-accent-ring);
    }

    .dex-v2 .btn-resolve {
        background: var(--dex-success);
        color: #fff;
        border-color: var(--dex-success);
        font-weight: 600;
        box-shadow: 0 1px 0 rgba(0, 0, 0, .04), 0 2px 6px rgba(22, 163, 74, .25);
    }

    .dex-v2 .btn-resolve:hover {
        background: #15803d;
        border-color: #15803d;
        color: #fff;
        box-shadow: 0 4px 14px rgba(22, 163, 74, .35);
    }

    .dex-v2 .btn-resolve[disabled] {
        opacity: .5;
        cursor: not-allowed;
        box-shadow: none;
    }

    .dex-v2 .btn-action .ti {
        font-size: 14px;
        line-height: 1;
        flex: none;
    }

    .dex-v2 .btn-action--icon {
        padding: 6px 9px;
    }

    .dex-v2 .btn-action--icon .ti {
        font-size: 20px;
    }

    /* ─── Dropdown action menu ─────────────────────────────────────── */
    .dex-v2 .dex-action-menu {
        position: relative;
    }

    .dex-v2 .dex-action-menu__trigger[aria-expanded="true"] {
        background: var(--bg);
        border-color: var(--dex-border-strong);
        color: var(--text);
        box-shadow: 0 1px 0 rgba(15, 18, 25, .04);
    }

    .dex-v2 .dex-action-menu__panel {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        min-width: 200px;
        padding: 6px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--dex-radius);
        box-shadow: var(--dex-shadow-3);
        z-index: 1100;
        display: flex;
        flex-direction: column;
        gap: 1px;
        animation: dex-menu-in .12s ease-out;
        transform-origin: top right;
    }

    .dex-v2 .dex-action-menu__panel[hidden] {
        display: none;
    }

    @keyframes dex-menu-in {
        from { opacity: 0; transform: translateY(-4px) scale(.98); }
        to   { opacity: 1; transform: translateY(0)    scale(1); }
    }

    .dex-v2 .dex-action-menu__item {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 7px 10px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text);
        background: transparent;
        border: 0;
        border-radius: 7px;
        cursor: pointer;
        text-align: left;
        text-decoration: none;
        white-space: nowrap;
        transition: background .12s ease, color .12s ease;
    }

    .dex-v2 .dex-action-menu__item:hover,
    .dex-v2 .dex-action-menu__item:focus-visible {
        background: var(--dex-accent-lt);
        color: var(--accent-dark);
        outline: none;
    }

    .dex-v2 .dex-action-menu__item:hover .ti,
    .dex-v2 .dex-action-menu__item:focus-visible .ti {
        color: var(--accent-dark);
    }

    .dex-v2 .dex-action-menu__item .ti {
        font-size: 15px;
        color: var(--muted);
        flex: none;
        line-height: 1;
    }

    .dex-v2 .dex-action-menu__item[disabled] {
        opacity: .45;
        cursor: not-allowed;
        pointer-events: none;
    }

    .dex-v2 .dex-action-menu__item.is-success {
        color: var(--dex-success);
    }

    .dex-v2 .dex-action-menu__item.is-success .ti {
        color: var(--dex-success);
    }

    .dex-v2 .dex-action-menu__divider {
        height: 1px;
        margin: 4px 6px;
        background: var(--border);
    }

    .dex-v2 .dex-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--dex-shadow-1);
        overflow: hidden;
    }

    .dex-v2 .dex-card + .dex-card {
        margin-top: 12px;
    }

    .dex-v2 .dex-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(180deg, var(--dex-surface-soft) 0%, var(--surface) 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
    }

    .dex-v2 .dex-card-title {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }

    .dex-v2 .dex-card-title small {
        color: var(--muted);
        font-weight: 400;
        font-size: 12px;
    }

    .dex-v2 .dex-card-body {
        padding: 16px;
    }

    .dex-v2 .dex-card-body--flush {
        padding: 0;
    }

    .dex-v2 .dex-collapse > summary {
        list-style: none;
        cursor: pointer;
    }

    .dex-v2 .dex-collapse > summary::-webkit-details-marker {
        display: none;
    }

    .dex-v2 .dex-collapse > summary::marker {
        content: '';
    }

    .dex-v2 .dex-collapse__right {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .dex-v2 .dex-collapse__chev {
        color: var(--muted);
        transition: transform .15s ease;
    }

    .dex-v2 .dex-collapse[open] .dex-collapse__chev {
        transform: rotate(180deg);
    }

    .dex-v2 .dex-issue-head {
        position: relative;
        padding: 18px 18px 16px;
    }

    

    .dex-v2 .dex-issue-title {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        font-size: 18px;
        font-weight: 600;
        color: var(--text);
        word-break: break-word;
    }

    .dex-v2 .dex-issue-code {
        font-size: 11px;
        font-weight: 600;
        padding: 1px 6px;
        background: var(--border);
        color: var(--muted);
        border-radius: 3px;
        letter-spacing: .03em;
    }

    .dex-v2 .dex-issue-msg {
        margin-top: 8px;
        font-size: 12px;
        color: var(--muted);
        line-height: 1.5;
        word-break: break-word;
    }

    .dex-v2 .dex-chips {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        align-items: center;
        margin-top: 10px;
    }

    .dex-v2 .dex-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 600;
        padding: 1px 7px;
        border-radius: 4px;
        background: var(--border);
        color: var(--muted);
        border: 1px solid var(--border);
    }

    .dex-v2 .dex-chip--open { 
        background: #fff1f2;
        color: #be123c;
        border-color: #fecdd3;
    } 

    .dex-v2 .dex-chip--regressed {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }

    .dex-v2 .dex-chip--resolved { 
        background: #f0fdf4; 
        color: #166534; 
        border-color: #bbf7d0; 
    } 

    .dex-v2 .dex-chip--ignored {
        background: #f1f5f9;
        color: #475569;
        border-color: #e2e8f0;
    }
 
    .dex-v2 .dex-chip--error { 
        background: #fff7ed; 
        color: #c2410c; 
        border-color: #fed7aa; 
    } 

    .dex-v2 .dex-chip--fatal {
        background: #fff1f2;
        color: #be123c;
        border-color: #fecdd3;
    }

    .dex-v2 .dex-chip--warning {
        background: #fefce8;
        color: #a16207;
        border-color: #fef08a;
    }

    .dex-v2 .dex-chip--fingerprint {
        background: transparent;
        border-color: transparent;
        display: inline-block;
        max-width: 100%;
        white-space: normal;
    }

    .dex-v2 .dex-chip code {
        background: transparent;
        color: var(--muted);
        font-size: 11px;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .dex-v2 .dex-stats {
        display: grid;
        grid-template-columns:repeat(4, 1fr);
        border-top: 1px solid var(--border);
        background: linear-gradient(180deg, var(--surface) 0%, var(--dex-surface-soft) 100%);
    }

    .dex-v2 .dex-stat {
        padding: 14px 16px;
        border-right: 1px solid var(--border);
    }

    .dex-v2 .dex-stat:last-child {
        border-right: none;
    }

    .dex-v2 .dex-stat__label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .dex-v2 .dex-stat__value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text);
        line-height: 1;
        letter-spacing: -.01em;
        background: linear-gradient(180deg, var(--dex-text) 0%, var(--dex-text-soft) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .dex-v2 .dex-stat__sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 2px;
    }

    .dex-v2 .dex-chart-wrap {
        padding: 4px 16px 16px;
    }

    .dex-v2 .dex-chart-wrap canvas {
        width: 100%;
        height: 140px;
        display: block;
    }

    .dex-v2 .dex-x-axis {
        display: flex;
        justify-content: space-between;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        font-size: 10px;
        color: var(--muted);
        padding: 0 4px;
        margin-top: 4px;
    }

    @media (max-width: 575.98px) {
        .dex-v2 .dex-x-axis {
            font-size: 9px;
            padding: 0 2px;
        }

        .dex-v2 .dex-x-axis span {
            display: none;
        }

        .dex-v2 .dex-x-axis span:first-child,
        .dex-v2 .dex-x-axis span:nth-child(4),
        .dex-v2 .dex-x-axis span:nth-child(8),
        .dex-v2 .dex-x-axis span:last-child {
            display: inline;
        }
    }

    .dex-v2 .dex-frame-count {
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
        letter-spacing: .03em;
        padding: 1px 7px;
        background: var(--border);
        border-radius: 10px;
    }

    .dex-v2 .dex-frame-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 3px;
        box-shadow: inset 0 1px 2px rgba(15, 18, 25, .04);
    }

    .dex-v2 .dex-frame-toggle button {
        border: 0;
        background: transparent;
        font-size: 12px;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 5px;
        color: var(--muted);
        cursor: pointer;
        transition: color .15s ease, background .15s ease, box-shadow .15s ease;
    }

    .dex-v2 .dex-frame-toggle button:hover {
        color: var(--text);
    }

    .dex-v2 .dex-frame-toggle button.active {
        background: var(--surface);
        color: var(--text);
        font-weight: 600;
        box-shadow: 0 1px 2px rgba(15, 18, 25, .07);
    }

    .dex-v2 .dex-frame {
        border-top: 1px solid var(--border);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        font-size: 12.5px;
    }

    .dex-v2 .dex-frame:first-child {
        border-top: none;
    }

    .dex-v2 .dex-frame__head {
        display: grid;
        grid-template-columns:auto 1fr auto auto;
        gap: 8px;
        align-items: center;
        padding: 11px 16px;
        cursor: pointer;
        transition: background .14s ease;
    }

    .dex-v2 .dex-frame__head:hover {
        background: var(--dex-surface-soft);
    }

    .dex-v2 .dex-frame.is-open .dex-frame__head {
        background: none;

    }

    .dex-v2 .dex-frame__tag {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 3px;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .dex-v2 .dex-frame__tag--vendor {
        background: var(--border);
        color: var(--muted);
    }

    .dex-v2 .dex-frame__tag--inapp {
        background: var(--accent-lt);
        color: var(--accent-dark);
    }

    .dex-v2 .dex-frame__path {
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
    }

    .dex-v2 .dex-frame__fn {
        color: var(--muted);
        font-style: italic;
    }

    .dex-v2 .dex-frame__chev {
        color: var(--muted);
        transition: transform .2s;
        font-size: 12px;
    }

    .dex-v2 .dex-frame.is-open .dex-frame__chev {
        transform: rotate(180deg);
    }

    .dex-v2 .dex-code-block {
        /* Dex Dark — IDE-style theme tuned to the brand */
        --dex-tok-text:    #24292f;
        --dex-tok-bg:      #ffffff;
        --dex-tok-bg-top:  #f6f8fa;
        --dex-tok-gutter:  #d0d7de;
        --dex-tok-no:      #57606a;
        --dex-tok-no-hi:   #b33611;
        --dex-tok-kw:      #b33611;   /* keyword — warm coral on brand */
        --dex-tok-str:     #0b6e30;   /* string — green */
        --dex-tok-num:     #7a4b00;   /* number — amber */
        --dex-tok-com:     #4b5563;   /* comment — slate (italic) */
        --dex-tok-var:     #b4233a;   /* variable — pink */
        --dex-tok-fn:      #0b5cad;   /* function — blue */
        --dex-tok-cls:     #7a5c00;   /* class / type — yellow */
        --dex-tok-attr:    #0f766e;   /* member / property — cyan */
        --dex-tok-op:      #374151;   /* operator — neutral */

        color: var(--dex-tok-text);
        border-top: 1px solid var(--dex-tok-gutter);
        display: none;
        font-feature-settings: "calt" 0;
        font-variant-ligatures: none;
    }

    /* Token colors — applied by the JS highlighter */
    .dex-v2 .dex-code-block .dex-tok-kw   { color: var(--dex-tok-kw);   font-weight: 600; }
    .dex-v2 .dex-code-block .dex-tok-str  { color: var(--dex-tok-str); }
    .dex-v2 .dex-code-block .dex-tok-num  { color: var(--dex-tok-num); }
    .dex-v2 .dex-code-block .dex-tok-com  { color: var(--dex-tok-com); font-style: italic; }
    .dex-v2 .dex-code-block .dex-tok-var  { color: var(--dex-tok-var); }
    .dex-v2 .dex-code-block .dex-tok-fn   { color: var(--dex-tok-fn); }
    .dex-v2 .dex-code-block .dex-tok-cls  { color: var(--dex-tok-cls); }
    .dex-v2 .dex-code-block .dex-tok-attr { color: var(--dex-tok-attr); }
    .dex-v2 .dex-code-block .dex-tok-op   { color: var(--dex-tok-op); }

    /* Gutter line numbers — restate against new tokens */
    .dex-v2 .dex-code-block .dex-code-line__no {
        color: var(--dex-tok-no);
        border-right-color: var(--dex-tok-gutter);
        background: rgba(0, 0, 0, .12);
    }

    /* Highlighted (culprit) line — soft accent wash + brighter line number */
    .dex-v2 .dex-code-line.is-highlight .dex-code-line__src,
    .dex-v2 .dex-code-line.is-highlight .dex-code-line__no {
        background: #e0e0e0;
    }

    .dex-v2 .dex-code-line.is-highlight .dex-code-line__no {
        color: var(--dex-tok-no-hi);
        font-weight: 500;
    }

    .dex-v2 .dex-frame.is-open .dex-code-block {
        display: block;
    }

    .dex-v2 .dex-code-line {
        display: grid;
        grid-template-columns:56px 1fr;
        gap: 0;
        line-height: 1.45;
    }

    .dex-v2 .dex-code-line__no {
        padding: 4px 10px;
        color: #8b8fa8;
        text-align: right;
        user-select: none;
        border-right: 1px solid #2a2d3a;
    }

    .dex-v2 .dex-code-line__src {
        padding: 4px 12px;
        white-space: pre-wrap;
        word-break: break-word;
    }
    /* is-highlight handled inside the new .dex-code-block token theme above */

    .dex-v2 .dex-culprit {
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 12.5px;
    }

    .dex-v2 .dex-culprit__label {
        color: var(--muted);
        font-weight: 600;
    }

    .dex-v2 .dex-events-scroller {
        overflow: auto;
    }

    .dex-v2 .dex-events {
        width: 100%;
        border-collapse: collapse;
    }

    .dex-v2 .dex-events td {
        padding: 10px 16px;
        border-top: 1px solid var(--border);
        font-size: 12.5px;
    }

    .dex-v2 .dex-events tr:first-child td {
        border-top: none;
    }

    .dex-v2 .dex-events tr.is-current {
        background: #fff7ed;
    }

    .dex-v2 .dex-event-pager {
        position: relative;
        background: linear-gradient(180deg, var(--surface) 0%, var(--dex-surface-soft) 100%);
        overflow: hidden;
    }

    .dex-v2 .dex-event-pager::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--dex-accent-grad);
    }

    .dex-v2 .dex-event-pager__main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 14px 18px 14px 22px;
        flex-wrap: wrap;
    }

    .dex-v2 .dex-event-pager__info {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .dex-v2 .dex-event-pager__heading {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dex-v2 .dex-event-pager__eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--muted);
    }



    .dex-v2 .dex-event-pager__position {
        display: inline-flex;
        align-items: baseline;
        gap: 4px;
        font-size: 11px;
        letter-spacing: .02em;
        color: var(--accent);
        background: var(--accent-lt);
        border: 1px solid rgba(var(--accent-rgb), .25);
        padding: 2px 9px;
        border-radius: var(--dex-radius-pill);
    }

    .dex-v2 .dex-event-pager__position-num {
        font-variant-numeric: tabular-nums;
    }

    .dex-v2 .dex-event-pager__position-of {
        color: var(--muted);
        font-weight: 500;
        text-transform: lowercase;
    }

    .dex-v2 .dex-event-pager__meta {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 12px;
        color: var(--muted);
        flex-wrap: wrap;
    }

    .dex-v2 .dex-event-pager__meta-icon {
        font-size: 13px;
        color: var(--dex-muted-soft);
        line-height: 1;
    }

    .dex-v2 .dex-event-pager__time-rel {
        font-weight: 500;
        color: var(--text);
    }

    .dex-v2 .dex-event-pager__sep {
        opacity: .45;
    }

    .dex-v2 .dex-event-pager__time-abs {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        font-size: 11px;
        color: var(--muted);
    }

    .dex-v2 .dex-event-pager__nav {
        display: inline-flex;
        align-items: stretch;
        gap: 6px;
        flex-shrink: 0;
    }

    .dex-v2 .dex-event-pager__nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid var(--border-md);
        border-radius: var(--dex-radius);
        background: var(--surface);
        padding: 7px 13px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--muted);
        cursor: pointer;
        box-shadow: var(--dex-shadow-1);
        transition: background .15s ease, color .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .dex-v2 .dex-event-pager__nav-btn:not([disabled]):hover {
        background: var(--accent-lt);
        color: var(--accent);
        border-color: rgba(var(--accent-rgb), .35);
        box-shadow: 0 2px 8px rgba(var(--accent-rgb), .14);
        transform: translateY(-1px);
    }

    .dex-v2 .dex-event-pager__nav-btn:not([disabled]):active {
        transform: translateY(0);
        box-shadow: inset 0 1px 2px rgba(15, 18, 25, .06);
    }

    .dex-v2 .dex-event-pager__nav-btn:focus-visible {
        outline: none;
        border-color: var(--accent);
        box-shadow: var(--dex-accent-ring);
    }

    .dex-v2 .dex-event-pager__nav-btn .ti {
        font-size: 14px;
        line-height: 1;
        flex: none;
    }

    .dex-v2 .dex-event-pager__nav-btn[disabled] {
        opacity: .42;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .dex-v2 .dex-method {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        color: var(--dex-m-post-fg);
        background: var(--dex-m-post-bg);
        padding: 2px 6px;
        border-radius: 4px;
        line-height: 1.2;
    }

    .dex-v2 .dex-path {
        color: var(--muted);
        margin-left: 6px;
    }

    .dex-v2 .dex-spans-table {
        width: 100%;
        overflow: auto;
    }

    .dex-v2 .dex-spans-head, .dex-v2 .dex-spans-row {
        display: grid;
        grid-template-columns:28px 120px minmax(180px, 1fr) minmax(180px, 1fr) 64px;
        gap: 8px;
        align-items: center;
        padding: 10px 16px;
    }

    .dex-v2 .dex-spans-head {
        font-size: 11px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
        border-bottom: 1px solid var(--border);
    }

    .dex-v2 .dex-spans-row {
        border-top: 1px solid var(--border);
        font-size: 12.5px;
    }

    .dex-v2 .dex-span-toggle {
        border: 0;
        background: transparent;
        color: var(--muted);
        padding: 0;
        cursor: pointer;
    }

    .dex-v2 .dex-span-op {
        font-weight: 600;
        color: var(--text);
    }

    .dex-v2 .dex-span-desc {
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dex-v2 .dex-span-bar {
        display: block;
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #eef0f5;
        position: relative;
        overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(15, 18, 25, .04);
    }

    .dex-v2 .dex-span-bar__fill {
        position: absolute;
        top: 0;
        bottom: 0;
        border-radius: 999px;
        background: var(--dex-accent-grad);
        box-shadow: 0 1px 4px rgba(232, 76, 30, .28);
    }

    .dex-v2 .dex-span-dur {
        text-align: right;
    }

    .dex-v2 .dex-tl {
        padding: 8px 16px 16px;
    }

    .dex-v2 .dex-tl-item {
        position: relative;
        padding: 12px 0 12px 24px;
        border-left: 1px solid var(--border);
        margin-left: 8px;
    }

    .dex-v2 .dex-tl-item:last-child {
        padding-bottom: 0;
    }

    .dex-v2 .dex-tl-dot {
        position: absolute;
        left: -6px;
        top: 16px;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #cbd5e1;
        border: 2px solid var(--surface);
        box-shadow: 0 0 0 2px rgba(15, 18, 25, .04);
    }

    .dex-v2 .dex-tl-dot.is-info  { box-shadow: 0 0 0 3px rgba(37, 99, 235, .18); }
    .dex-v2 .dex-tl-dot.is-warn  { box-shadow: 0 0 0 3px rgba(217, 119, 6, .18); }
    .dex-v2 .dex-tl-dot.is-error { box-shadow: 0 0 0 3px rgba(220, 38, 38, .22); }

    .dex-v2 .dex-tl-dot.is-info {
        background: #2563eb;
    }

    .dex-v2 .dex-tl-dot.is-warn {
        background: #d97706;
    }

    .dex-v2 .dex-tl-dot.is-error {
        background: #dc2626;
    }

    .dex-v2 .dex-tl-head {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .dex-v2 .dex-tl-time, .dex-v2 .dex-tl-cat, .dex-v2 .dex-tl-error-tag {
        font-size: 11px;
        font-weight: 600;
    }

    .dex-v2 .dex-tl-time {
        color: var(--muted);
    }

    .dex-v2 .dex-tl-cat {
        color: var(--accent-dark);
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .dex-v2 .dex-tl-error-tag {
        color: #b91c1c;
        background: #fee2e2;
        border-radius: 999px;
        padding: 1px 6px;
    }

    .dex-v2 .dex-tl-msg {
        color: var(--text);
        font-size: 12.5px;
    }

    .dex-v2 .dex-kv {
        margin-top: 8px;
        display: grid;
        gap: 4px;
    }

    .dex-v2 .dex-kv-row {
        display: grid;
        grid-template-columns:120px 1fr;
        gap: 8px;
        font-size: 12px;
    }

    .dex-v2 .dex-kv-key {
        color: var(--muted);
    }

    .dex-v2 .dex-kv-val {
        color: var(--text);
        word-break: break-word;
    }

    /* ─── Lifecycle: title icon ─────────────────────────────────────── */
    .dex-v2 .dex-lifecycle-title-icon {
        color: var(--dex-accent);
        font-size: 16px;
        line-height: 1;
    }

    /* ─── Lifecycle: KPI summary strip ──────────────────────────────── */
    .dex-v2 .dex-lifecycle-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(132px, 1fr));
        gap: 10px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        background:
            radial-gradient(420px 140px at 0% 0%, rgba(var(--dex-accent-rgb), .045), transparent 70%),
            var(--dex-surface-soft);
    }

    .dex-v2 .dex-lifecycle-summary__item {
        position: relative;
        display: grid;
        gap: 4px;
        padding: 10px 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--dex-radius-sm);
        box-shadow: var(--dex-shadow-1);
        overflow: hidden;
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .dex-v2 .dex-lifecycle-summary__item::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--dex-accent-grad);
        opacity: .85;
    }

    .dex-v2 .dex-lifecycle-summary__item:hover {
        border-color: var(--dex-border-md);
        box-shadow: var(--dex-shadow-2);
        transform: translateY(-1px);
    }

    .dex-v2 .dex-lifecycle-summary__label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .dex-v2 .dex-lifecycle-summary__value {
        color: var(--text);
        font-size: 16px;
        font-weight: 700;
        line-height: 1.2;
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum" 1;
    }

    /* ─── Lifecycle: hints ──────────────────────────────────────────── */
    .dex-v2 .dex-lifecycle-hints {
        padding: 14px 16px 2px;
    }

    .dex-v2 .dex-info-note--error {
        background: linear-gradient(180deg, var(--dex-danger-lt) 0%, #fff7f7 100%);
        border-color: var(--dex-danger-bd);
        box-shadow: inset 3px 0 0 var(--dex-danger);
        color: #7f1d1d;
    }

    .dex-v2 .dex-info-note--info {
        background: linear-gradient(180deg, var(--dex-info-lt) 0%, #f5f8ff 100%);
        border-color: var(--dex-info-bd);
        box-shadow: inset 3px 0 0 var(--dex-info);
        color: #1e3a8a;
    }

    /* ─── Lifecycle: timeline list ──────────────────────────────────── */
    .dex-v2 .dex-lifecycle-list {
        position: relative;
        padding: 14px 16px 18px 36px;
    }

    .dex-v2 .dex-lifecycle-list::before {
        content: "";
        position: absolute;
        left: 24px;
        top: 22px;
        bottom: 22px;
        width: 2px;
        background: linear-gradient(180deg,
            var(--border) 0%,
            var(--dex-border-md) 50%,
            var(--border) 100%);
        border-radius: 2px;
    }

    .dex-v2 .dex-lifecycle-item {
        position: relative;
        padding: 10px 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--dex-radius-sm);
        box-shadow: var(--dex-shadow-1);
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .dex-v2 .dex-lifecycle-item + .dex-lifecycle-item {
        margin-top: 10px;
    }

    .dex-v2 .dex-lifecycle-item__marker {
        position: absolute;
        left: -18px;
        top: 16px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--muted-soft, #9398a2);
        border: 2px solid var(--surface);
        box-shadow: 0 0 0 2px var(--border);
    }

    /* Type-aware marker colors via :has() */
    .dex-v2 .dex-lifecycle-item:has(.dex-lifecycle-badge--db) .dex-lifecycle-item__marker {
        background: var(--dex-warning);
        box-shadow: 0 0 0 2px rgba(217, 119, 6, .22);
    }

    .dex-v2 .dex-lifecycle-item:has(.dex-lifecycle-badge--error) .dex-lifecycle-item__marker {
        background: var(--dex-danger);
        box-shadow: 0 0 0 2px rgba(220, 38, 38, .22);
    }

    .dex-v2 .dex-lifecycle-item:has(.dex-lifecycle-badge--response) .dex-lifecycle-item__marker,
    .dex-v2 .dex-lifecycle-item:has(.dex-lifecycle-badge--checkpoint) .dex-lifecycle-item__marker {
        background: var(--dex-info);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .18);
    }

    .dex-v2 .dex-lifecycle-item:has(.dex-lifecycle-badge--span) .dex-lifecycle-item__marker {
        background: var(--dex-accent);
        box-shadow: 0 0 0 2px rgba(var(--dex-accent-rgb), .22);
    }

    .dex-v2 .dex-lifecycle-item:has(.dex-lifecycle-badge--breadcrumb) .dex-lifecycle-item__marker {
        background: var(--muted);
        box-shadow: 0 0 0 2px rgba(15, 18, 25, .08);
    }

    /* Nested items: subtle left-border to show span nesting */
    .dex-v2 .dex-lifecycle-item--nested {
        border-left: 2px solid var(--dex-accent, #6366f1);
        border-left-color: rgba(var(--dex-accent-rgb, 99, 102, 241), .35);
    }

    .dex-v2 .dex-lifecycle-item--nested .dex-lifecycle-item__marker {
        left: -19px;
    }

    /* Accent left-edge on error rows to draw the eye */
    .dex-v2 .dex-lifecycle-item:has(.dex-lifecycle-badge--error) {
        border-color: var(--dex-danger-bd);
        box-shadow: inset 3px 0 0 var(--dex-danger), var(--dex-shadow-1);
    }

    .dex-v2 .dex-lifecycle-item:has(.dex-lifecycle-badge--error):hover {
        box-shadow: inset 3px 0 0 var(--dex-danger), var(--dex-shadow-2);
    }

    .dex-v2 .dex-lifecycle-item__line {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        min-width: 0;
    }

    .dex-v2 .dex-lifecycle-item__line-end {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .dex-v2 .dex-lifecycle-item__toggle {
        border: 0;
        background: transparent;
        color: var(--muted);
        padding: 2px 4px;
        border-radius: 6px;
        cursor: pointer;
        line-height: 1;
        transition: color .15s ease, background .15s ease, transform .2s ease;
    }

    .dex-v2 .dex-lifecycle-item__toggle:hover {
        color: var(--text);
        background: rgba(15, 18, 25, .04);
    }

    .dex-v2 .dex-lifecycle-item.is-collapsed .dex-lifecycle-item__toggle {
        transform: rotate(-90deg);
    }

    .dex-v2 .dex-lifecycle-item--depth-1 { margin-left: 22px; }
    .dex-v2 .dex-lifecycle-item--depth-2 { margin-left: 44px; }
    .dex-v2 .dex-lifecycle-item--depth-3 { margin-left: 66px; }
    .dex-v2 .dex-lifecycle-item--depth-4 { margin-left: 88px; }
    .dex-v2 .dex-lifecycle-item--depth-5 { margin-left: 110px; }
    .dex-v2 .dex-lifecycle-item--depth-6 { margin-left: 132px; }
    .dex-v2 .dex-lifecycle-item--depth-7 { margin-left: 154px; }
    .dex-v2 .dex-lifecycle-item--depth-8 { margin-left: 176px; }
    .dex-v2 .dex-lifecycle-item--depth-9 { margin-left: 198px; }
    .dex-v2 .dex-lifecycle-item--depth-10 { margin-left: 220px; }
    .dex-v2 .dex-lifecycle-item--depth-11 { margin-left: 242px; }
    .dex-v2 .dex-lifecycle-item--depth-12 { margin-left: 264px; }

    .dex-v2 .dex-lifecycle-time,
    .dex-v2 .dex-lifecycle-duration,
    .dex-v2 .dex-lifecycle-status {
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .dex-v2 .dex-lifecycle-time {
        color: var(--muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        padding: 2px 7px;
        border-radius: var(--dex-radius-sm);
        background: var(--dex-bg-tint);
        border: 1px solid var(--border);
        font-variant-numeric: tabular-nums;
    }

    .dex-v2 .dex-lifecycle-duration {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: var(--muted);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-variant-numeric: tabular-nums;
    }

    .dex-v2 .dex-lifecycle-duration i {
        font-size: 12px;
        line-height: 1;
        opacity: .7;
    }

    .dex-v2 .dex-lifecycle-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 2px 8px 2px 7px;
        border-radius: var(--dex-radius-pill);
        background: rgba(15, 18, 25, .04);
        border: 1px solid var(--border);
        text-transform: capitalize;
        letter-spacing: .01em;
    }

    .dex-v2 .dex-lifecycle-status::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 2px rgba(0, 0, 0, 0);
    }

    .dex-v2 .dex-lifecycle-label {
        color: var(--text);
        font-size: 11px;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1 1 0%;
    }

    .dex-v2 .badge2{
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        font:size: 11px;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 3px;
    }

    .dex-v2 .dex-lifecycle-badge {
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
    }

    .dex-v2 .dex-lifecycle-badge--db {
        background: var(--dex-warning-lt);
        color: var(--dex-warning);
    }

    .dex-v2 .dex-lifecycle-badge--error {
        background: var(--dex-danger-lt);
        color: var(--dex-danger);
    }

    .dex-v2 .dex-lifecycle-badge--response,
    .dex-v2 .dex-lifecycle-badge--checkpoint {
        background: var(--dex-info-lt);
        color: var(--dex-info);
    }

    .dex-v2 .dex-lifecycle-badge--span {
        background: var(--dex-accent-lt);
        color: var(--accent-dark);
    }

    .dex-v2 .dex-lifecycle-badge--breadcrumb {
        background: var(--dex-bg-tint);
        color: var(--muted);
    }

    /* ─── Lifecycle: data rows ──────────────────────────────────────── */
    .dex-v2 .dex-lifecycle-data {
        margin-top: 10px;
        padding: 8px 10px;
        background: var(--dex-surface-soft);
        border: 1px solid var(--border);
        border-radius: var(--dex-radius-sm);
    }

    .dex-v2 .dex-lifecycle-data .dex-kv-row {
        padding: 3px 0;
        border-bottom: 1px dashed var(--border);
    }

    .dex-v2 .dex-lifecycle-data .dex-kv-row:last-child {
        border-bottom: 0;
    }

    .dex-v2 .dex-lifecycle-data .dex-kv-key {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        font-size: 11px;
        text-transform: lowercase;
        letter-spacing: .02em;
    }

    .dex-v2 .dex-lifecycle-data .dex-kv-val {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        font-size: 11px;
    }

    .dex-v2 .dex-signal {
        display: flex;
        gap: 12px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: var(--dex-radius-sm);
        background: var(--surface);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .dex-v2 .dex-signal:hover {
        border-color: var(--dex-border-md);
        box-shadow: var(--dex-shadow-1);
    }

    .dex-v2 .dex-signal + .dex-signal {
        margin-top: 8px;
    }

    .dex-v2 .dex-signal__icon {
        width: 32px;
        height: 32px;
        flex: none;
        border-radius: 8px;
        display: grid;
        place-items: center;
        background: var(--bg);
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
        box-shadow: inset 0 0 0 1px rgba(15, 18, 25, .04);
    }

    .dex-v2 .dex-signal--alert .dex-signal__icon {
        background: linear-gradient(135deg, #fee2e2 0%, #fdcaca 100%);
        color: #b91c1c;
        box-shadow: inset 0 0 0 1px rgba(220, 38, 38, .15);
    }

    .dex-v2 .dex-signal--up .dex-signal__icon {
        background: linear-gradient(135deg, var(--dex-accent-lt) 0%, var(--dex-accent-lt-2) 100%);
        color: var(--accent-dark);
        box-shadow: inset 0 0 0 1px rgba(232, 76, 30, .18);
    }

    .dex-v2 .dex-signal--fast .dex-signal__icon {
        background: linear-gradient(135deg, #fff7ed 0%, #ffe6c4 100%);
        color: #b45309;
        box-shadow: inset 0 0 0 1px rgba(217, 119, 6, .18);
    }

    .dex-v2 .dex-signal__title {
        font-weight: 600;
        font-size: 13px;
        color: var(--text);
        margin: 0;
        line-height: 1.3;
    }

    .dex-v2 .dex-signal__body {
        font-size: 12px;
        color: var(--muted);
        margin-top: 2px;
        line-height: 1.5;
    }

    .dex-v2 .dex-signal__body code {
        color: var(--text);
        background: var(--border);
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 11px;
    }

    .dex-v2 .dex-status-pill {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        padding: 2px 9px;
        border-radius: var(--dex-radius-pill);
        background: var(--dex-danger-lt);
        color: var(--dex-danger);
        box-shadow: inset 0 0 0 1px var(--dex-danger-bd);
    }

    .dex-v2 .dex-meta-list {
        padding: 10px 16px 16px;
        display: grid;
        gap: 8px;
    }

    .dex-v2 .dex-meta-section {
        border-top: 1px solid var(--border);
    }

    .dex-v2 .dex-meta-section:first-of-type {
        border-top: none;
    }

    .dex-v2 .dex-meta-section__title {
        padding: 12px 16px 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .dex-v2 .dex-meta-row {
        display: grid;
        grid-template-columns:120px 1fr;
        gap: 10px;
        font-size: 12.5px;
    }

    .dex-v2 .dex-meta-key {
        color: var(--muted);
    }

    .dex-v2 .dex-meta-val {
        color: var(--text);
        word-break: break-word;
    }

    .dex-v2 .dex-info-note {
        margin: 0 16px 16px;
        padding: 12px 14px;
        border-radius: 8px;
        background: linear-gradient(180deg, var(--dex-accent-lt) 0%, #fff8f4 100%);
        border: 1px solid var(--dex-accent-lt-2);
        color: #8a2f15;
        font-size: 12px;
        line-height: 1.5;
        box-shadow: inset 3px 0 0 var(--dex-accent);
    }

    .dex-v2 .dex-info-note__title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .dex-v2 .dex-loading {
        color: var(--muted);
        padding: 20px 16px;
    }

    .dex-v2 .dex-empty {
        color: var(--muted);
        padding: 16px;
    }

    .dex-v2 .dex-section-anchor {
        scroll-margin-top: 16px;
    }

    /* legacy powered-by — hidden, replaced by dex-footer in layout */
    .powered-by { display: none !important; }

    @media (max-width: 992px) {
        .dex-v2 .dex-stats {
            grid-template-columns:repeat(2, 1fr);
        }

        .dex-v2 .dex-stat:nth-child(2) {
            border-right: none;
        }

        .dex-v2 .dex-stat:nth-child(1), .dex-v2 .dex-stat:nth-child(2) {
            border-bottom: 1px solid var(--border);
        }
    }

    @media (max-width: 768px) {
        .dex-v2 .dex-spans-head, .dex-v2 .dex-spans-row {
            grid-template-columns:28px 92px minmax(120px, 1fr) minmax(120px, 1fr) 52px;
        }
    }

    @media (max-width: 520px) { 
        .dex-v2 .page-wrap { 
            padding: 8px 0 18px; 
        } 

        .dex-v2 .dex-stats {
            grid-template-columns:1fr;
        }

        .dex-v2 .dex-stat {
            border-right: none;
            border-bottom: 1px solid var(--border);
        }

        .dex-v2 .dex-stat:last-child {
            border-bottom: none;
        }

        .dex-v2 .dex-meta-row, .dex-v2 .dex-kv-row {
            grid-template-columns:1fr;
            gap: 4px;
        }

        .dex-v2 .dex-lifecycle-item__line-end { 
            margin-left: 0; 
            width: 100%; 
            justify-content: flex-start; 
        } 
 
        .dex-v2 .dex-lifecycle-item__line {
            flex-wrap: wrap;
            align-items: flex-start;
            row-gap: 6px;
        }

        .dex-v2 .dex-lifecycle-item__line-end {
            display: contents;
        }

        .dex-v2 .dex-lifecycle-label {
            order: 10;
            flex: 1 1 100%;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
        }

        .dex-v2 .dex-lifecycle-duration {
            order: 20;
            flex: 1 1 100%;
        }

        .dex-v2 .dex-lifecycle-item__toggle {
            order: 5;
            margin-left: auto;
            flex: 0 0 auto;
        }

        .dex-v2 .dex-lifecycle-time,
        .dex-v2 .dex-lifecycle-badge {
            flex: 0 0 auto;
        }

        .dex-v2 .dex-lifecycle-item--depth-1,
        .dex-v2 .dex-lifecycle-item--depth-2,
        .dex-v2 .dex-lifecycle-item--depth-3,
        .dex-v2 .dex-lifecycle-item--depth-4,
        .dex-v2 .dex-lifecycle-item--depth-5,
        .dex-v2 .dex-lifecycle-item--depth-6,
        .dex-v2 .dex-lifecycle-item--depth-7,
        .dex-v2 .dex-lifecycle-item--depth-8,
        .dex-v2 .dex-lifecycle-item--depth-9,
        .dex-v2 .dex-lifecycle-item--depth-10,
        .dex-v2 .dex-lifecycle-item--depth-11,
        .dex-v2 .dex-lifecycle-item--depth-12 { margin-left: 0; }
 
        .dex-v2 .dex-lifecycle-list { 
            padding-left: 32px; 
        }

        .dex-v2 .dex-lifecycle-list::before {
            left: 20px;
        }

        .dex-v2 .dex-lifecycle-item__marker {
            left: -16px;
        }
    }

</style>
