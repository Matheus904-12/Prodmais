const DS_SEARCH_INDEX = [
    { terms: ['visão geral', 'overview', 'início', 'home', 'bem-vindo', 'welcome'], url: 'index.html' },
    { terms: ['fundamentos', 'cores', 'cor', 'tipografia', 'tokens', 'espaçamento', 'raio'], url: 'fundamentos.html' },
    { terms: ['ícones', 'icones', 'icon', 'bootstrap icons'], url: 'icons.html' },
    { terms: ['componentes', 'todos os componentes'], url: 'components.html' },
    { terms: ['alert', 'alerta', 'aviso'], url: 'alert.html' },
    { terms: ['card', 'cartão', 'stat card'], url: 'card.html' },
    { terms: ['botão', 'botao', 'button', 'btn'], url: 'button.html' },
    { terms: ['badge', 'tag', 'selo'], url: 'badge.html' },
    { terms: ['navbar', 'menu', 'navegação'], url: 'navbar.html' },
    { terms: ['footer', 'rodapé', 'rodape'], url: 'footer.html' },
    { terms: ['hero', 'banner'], url: 'hero.html' },
    { terms: ['backend', 'php', 'domain', 'infrastructure'], url: 'backend.html' },
    { terms: ['frontend', 'css', 'js', 'javascript'], url: 'frontend.html' },
    { terms: ['api', 'endpoint', 'rota', 'search.php'], url: 'api.html' },
    { terms: ['extração de dados', 'extracao de dados', 'lattes', 'orcid', 'openalex', 'brcris'], url: 'extracao-dados.html' }
];

function dsRunSearch(input) {
    const query = input.value.trim().toLowerCase();
    if (!query) return;
    const match = DS_SEARCH_INDEX.find(function (entry) {
        return entry.terms.some(function (term) { return term.includes(query) || query.includes(term); });
    });
    if (match) window.location.href = match.url;
}

document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const input = e.target.closest('[data-ds-search]');
    if (!input) return;
    e.preventDefault();
    dsRunSearch(input);
});

if (window.mermaid) {
    window.mermaid.initialize({
        startOnLoad: true,
        theme: 'base',
        fontFamily: 'Inter, sans-serif',
        themeVariables: {
            background: 'transparent',
            primaryColor: '#0d1b4a',
            primaryTextColor: '#f1f5f9',
            primaryBorderColor: '#6366f1',
            lineColor: '#6366f1',
            secondaryColor: '#0a1535',
            tertiaryColor: '#0a1535',
            edgeLabelBackground: '#0a1535'
        }
    });
}

const tocLinks = document.querySelectorAll('.ds-toc a');
if (tocLinks.length && 'IntersectionObserver' in window) {
    const sections = Array.from(tocLinks)
        .map(function (a) { return document.querySelector(a.getAttribute('href')); })
        .filter(Boolean);
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            tocLinks.forEach(function (a) { a.classList.remove('is-active'); });
            const link = document.querySelector('.ds-toc a[href="#' + entry.target.id + '"]');
            if (link) link.classList.add('is-active');
        });
    }, { rootMargin: '-96px 0px -70% 0px' });
    sections.forEach(function (s) { observer.observe(s); });
}

document.addEventListener('click', function (e) {
    const toggle = e.target.closest('[data-toggle-code]');
    if (toggle) {
        const codeBlock = toggle.closest('.ds-example').querySelector('.ds-example-code');
        const isOpen = codeBlock.classList.toggle('is-open');
        toggle.querySelector('span').textContent = isOpen ? 'Ocultar código' : 'Ver código';
        return;
    }

    const fbBtn = e.target.closest('[data-feedback]');
    if (fbBtn) {
        const group = fbBtn.closest('.ds-feedback-btns');
        group.querySelectorAll('.ds-feedback-btn').forEach(function (b) {
            b.classList.remove('is-selected');
            b.disabled = true;
        });
        fbBtn.classList.add('is-selected');
        const thanks = fbBtn.closest('.ds-feedback').querySelector('.ds-feedback-thanks');
        if (thanks) thanks.style.display = 'inline';
        return;
    }

    const btn = e.target.closest('[data-copy]');
    if (!btn) return;

    const code = btn.parentElement.querySelector('code');
    if (!code) return;

    navigator.clipboard.writeText(code.textContent.trim()).then(function () {
        const original = btn.textContent;
        btn.textContent = 'Copiado!';
        btn.classList.add('is-copied');
        setTimeout(function () {
            btn.textContent = original;
            btn.classList.remove('is-copied');
        }, 1800);
    });
});
