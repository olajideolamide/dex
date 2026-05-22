<?php
$pageTitle = $title ?? 'DEX — Issue Tracking';
?>

<!doctype html>
<html lang="en" class="dex-is-loading">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="robots" content="noindex, nofollow"/>
    <title><?= esc($pageTitle) ?></title>

    <style>
        html.dex-is-loading body {
            overflow: hidden;
        }

        html.dex-is-loading .page {
            visibility: hidden;
        }

        #dex-page-loader {
            position: fixed;
            inset: 0;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 18px;
            background:
                radial-gradient(900px 320px at 50% 20%, rgba(232, 76, 30, .08), rgba(232, 76, 30, 0) 70%),
                #ffffff;
            z-index: 2147483647;
        }

        html.dex-is-loading #dex-page-loader {
            display: flex;
        }

        #dex-page-loader .dex-spinner {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            border: 3px solid rgba(232, 76, 30, .15);
            border-top-color: #e84c1e;
            box-shadow: 0 0 0 1px rgba(232, 76, 30, .04), 0 4px 16px rgba(232, 76, 30, .18);
            animation: dex-spin .8s linear infinite;
        }

        #dex-page-loader .dex-loader-brand {
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #6b6c71;
            letter-spacing: .04em;
        }

        #dex-page-loader .dex-loader-brand strong {
            color: #e84c1e;
        }

        @keyframes dex-spin {
            to { transform: rotate(360deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            #dex-page-loader .dex-spinner {
                animation: none;
                border-top-color: rgba(232, 76, 30, .45);
            }
        }
    </style>

    <script>
        (function () {
            const html = document.documentElement;
            const reveal = function () {
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        html.classList.remove('dex-is-loading');
                    });
                });
            };

            if (document.readyState === 'complete') {
                reveal();
                return;
            }

            window.addEventListener('load', reveal, { once: true });
            window.setTimeout(reveal, 10000);
        })();
    </script>

    <noscript>
        <style>
            html.dex-is-loading .page { visibility: visible; }
            html.dex-is-loading #dex-page-loader { display: none; }
        </style>
    </noscript>

    <?php include __DIR__ . '/_styles.php'; ?>
</head>

<body>

<div id="dex-page-loader" role="status" aria-live="polite" aria-label="Loading DEX">
    <div class="dex-spinner" aria-hidden="true"></div>
    <div class="dex-loader-brand"><strong>DEX</strong> &nbsp;·&nbsp; Loading issues…</div>
</div>

<div class="page">
    <div class="page-wrapper">
        <div class="container-xl">
            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <footer class="dex-footer">
        <div class="dex-footer__inner container-xl">
            <span class="dex-footer__copy">
                Powered by <a href="https://www.dexphp.com" target="_blank" rel="noopener noreferrer">DEX</a>
                &nbsp;·&nbsp; Open-source issue tracking for CodeIgniter&nbsp;4
            </span>
            <span class="dex-footer__links">
                <a href="https://github.com/olajideolamide/dex" target="_blank" rel="noopener noreferrer">GitHub</a>
            </span>
        </div>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>

<?php include __DIR__ . '/_js.php'; ?>
</body>
</html>
