// ============================================
// DEX Docs — JavaScript
// ============================================

(function () {
  'use strict';

  // Theme Toggle
  const themeToggle = document.getElementById('themeToggle');
  const iconSun = themeToggle?.querySelector('.icon-sun');
  const iconMoon = themeToggle?.querySelector('.icon-moon');

  function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('dex-theme', theme);
    if (iconSun && iconMoon) {
      iconSun.style.display = theme === 'dark' ? 'none' : 'block';
      iconMoon.style.display = theme === 'dark' ? 'block' : 'none';
    }
  }

  const stored = localStorage.getItem('dex-theme');
  const preferred = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  setTheme(stored || preferred);

  themeToggle?.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme');
    setTheme(current === 'dark' ? 'light' : 'dark');
  });

  // Mobile Sidebar Toggle
  const mobileToggle = document.getElementById('mobileToggle');
  const sidebar = document.getElementById('docsSidebar');

  mobileToggle?.addEventListener('click', () => {
    sidebar?.classList.toggle('open');
  });

  document.addEventListener('click', (e) => {
    if (sidebar?.classList.contains('open') &&
        !sidebar.contains(e.target) &&
        !mobileToggle?.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });

  // Search Modal
  const searchTrigger = document.getElementById('searchTrigger');
  const searchModal = document.getElementById('searchModal');
  const searchBackdrop = document.getElementById('searchBackdrop');
  const searchInput = document.getElementById('searchInput');
  const searchResults = document.getElementById('searchResults');

  function openSearch() {
    searchModal?.classList.add('open');
    setTimeout(() => searchInput?.focus(), 100);
  }

  function closeSearch() {
    searchModal?.classList.remove('open');
    if (searchInput) searchInput.value = '';
    if (searchResults) {
      searchResults.innerHTML = '<div class="search-empty">Start typing to search the docs...</div>';
    }
  }

  searchTrigger?.addEventListener('click', openSearch);
  searchBackdrop?.addEventListener('click', closeSearch);

  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      if (searchModal?.classList.contains('open')) {
        closeSearch();
      } else {
        openSearch();
      }
    }
    if (e.key === 'Escape') closeSearch();
  });

  // Simple client-side search
  const searchIndex = [];

  document.querySelectorAll('.sidebar-link').forEach(link => {
    searchIndex.push({
      title: link.textContent.trim(),
      url: link.getAttribute('href'),
      section: link.closest('.sidebar-section')?.querySelector('.sidebar-heading')?.textContent.trim() || ''
    });
  });

  searchInput?.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase().trim();
    if (!query) {
      searchResults.innerHTML = '<div class="search-empty">Start typing to search the docs...</div>';
      return;
    }

    const matches = searchIndex.filter(item =>
      item.title.toLowerCase().includes(query) ||
      item.section.toLowerCase().includes(query)
    );

    if (matches.length === 0) {
      searchResults.innerHTML = '<div class="search-empty">No results found.</div>';
      return;
    }

    searchResults.innerHTML = matches.map(item => `
      <a href="${item.url}" class="search-result-item" onclick="closeSearch?.()">
        <div class="result-title">${item.title}</div>
        <div class="result-section">${item.section}</div>
      </a>
    `).join('');
  });

  window.closeSearch = closeSearch;

  // Active TOC highlighting
  const tocLinks = document.querySelectorAll('.toc a');
  if (tocLinks.length > 0) {
    const headings = [];
    tocLinks.forEach(link => {
      const id = link.getAttribute('href')?.replace('#', '');
      const heading = id && document.getElementById(id);
      if (heading) headings.push({ el: heading, link: link });
    });

    function updateTOC() {
      let active = headings[0];
      for (const h of headings) {
        if (h.el.getBoundingClientRect().top <= 100) {
          active = h;
        }
      }
      tocLinks.forEach(l => l.classList.remove('active'));
      active?.link.classList.add('active');
    }

    window.addEventListener('scroll', updateTOC, { passive: true });
    updateTOC();
  }

  // Copy code button
  document.querySelectorAll('.docs-body pre').forEach(pre => {
    const btn = document.createElement('button');
    btn.className = 'copy-btn';
    btn.textContent = 'Copy';
    btn.style.cssText = `
      position: absolute; top: 8px; right: 8px;
      padding: 4px 10px; font-size: 0.7rem; font-family: var(--font-mono);
      background: rgba(255,255,255,0.08); color: #8a7d70;
      border: 1px solid rgba(255,255,255,0.1); border-radius: 4px;
      cursor: pointer; transition: all 0.2s;
    `;
    btn.addEventListener('mouseenter', () => { btn.style.color = '#e0d8d0'; });
    btn.addEventListener('mouseleave', () => { btn.style.color = '#8a7d70'; });
    btn.addEventListener('click', () => {
      const code = pre.querySelector('code')?.textContent;
      if (code) {
        navigator.clipboard.writeText(code).then(() => {
          btn.textContent = 'Copied!';
          btn.style.color = '#DD4814';
          setTimeout(() => { btn.textContent = 'Copy'; btn.style.color = '#8a7d70'; }, 2000);
        });
      }
    });
    pre.style.position = 'relative';
    pre.appendChild(btn);
  });
})();
