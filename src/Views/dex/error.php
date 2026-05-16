<?php

$message = $message ?? 'Something went wrong.'; ?>
<div class="dex-error-page">
    <div class="dex-error-page__card">
        <div class="dex-error-page__icon" aria-hidden="true">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="9" fill="#fff1ed"/>
                <path d="M16 10v7" stroke="#e84c1e" stroke-width="2" stroke-linecap="round"/>
                <circle cx="16" cy="21.5" r="1.25" fill="#e84c1e"/>
            </svg>
        </div>
        <div class="dex-error-page__brand">
            <svg width="18" height="18" viewBox="0 0 22 22" fill="none" aria-hidden="true">
                <rect width="22" height="22" rx="6" fill="#e84c1e"/>
                <path d="M5.5 6.5h4.8c2.87 0 5.2 2.01 5.2 4.5S13.17 15.5 10.3 15.5H5.5V6.5z" fill="#fff"/>
                <circle cx="16" cy="8.5" r="1.5" fill="rgba(255,255,255,.55)"/>
            </svg>
            <span>DEX</span>
        </div>
        <h1 class="dex-error-page__title">Dashboard Error</h1>
        <p class="dex-error-page__message"><?= esc($message) ?></p>
        <a href="javascript:history.back()" class="dex-error-page__btn">← Go back</a>
    </div>
</div>

<style>
    .dex-error-page {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        padding: 40px 16px;
        font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
    }

    .dex-error-page__card {
        position: relative;
        background: #fff;
        border: 1px solid #e7e7ec;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 18, 25, .08), 0 16px 48px rgba(15, 18, 25, .07);
        padding: 44px 36px 36px;
        max-width: 440px;
        width: 100%;
        text-align: center;
        overflow: hidden;
    }

    .dex-error-page__card::before {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 3px;
        background: linear-gradient(135deg, #f56432 0%, #e84c1e 55%, #c8341a 100%);
    }

    .dex-error-page__icon {
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
    }

    .dex-error-page__brand {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        color: #1c1d20;
        margin-bottom: 16px;
        letter-spacing: -.01em;
    }

    .dex-error-page__title {
        font-size: 20px;
        font-weight: 700;
        color: #1c1d20;
        margin: 0 0 10px;
        line-height: 1.2;
    }

    .dex-error-page__message {
        font-size: 14px;
        color: #6b6c71;
        line-height: 1.6;
        margin: 0 0 28px;
    }

    .dex-error-page__btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 22px;
        background: linear-gradient(135deg, #f56432 0%, #e84c1e 55%, #c8341a 100%);
        color: #fff;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 1px 0 rgba(0, 0, 0, .04), 0 4px 14px rgba(232, 76, 30, .28);
        transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    }

    .dex-error-page__btn:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 22px rgba(232, 76, 30, .38);
        filter: brightness(1.04);
    }

    .dex-error-page__btn:active {
        transform: translateY(0);
        filter: brightness(.96);
    }
</style>
