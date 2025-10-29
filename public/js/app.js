document.addEventListener('DOMContentLoaded', () => {
    // Estado da aplicação
    const appState = {
        currentFilters: {},
        currentResults: [],
        charts: {}
    };

    // Elementos DOM
    const elements = {
        searchQuery: document.getElementById('search-query'),
        btnSearch: document.getElementById('btn-search'),
        btnFilter: document.getElementById('btn-filter'),
        btnClear: document.getElementById('btn-clear'),
        loadingIndicator: document.getElementById('loading'),
        tableBody: document.getElementById('results-table-body'),
        resultsSummary: document.getElementById('results-summary'),
        researcherSearch: document.getElementById('researcher-search'),
        btnSearchResearchers: document.getElementById('btn-search-researchers'),
        researchersResults: document.getElementById('researchers-results')
    };

    // Filtros
    const filters = {
        type: document.getElementById('filter-type'),
        language: document.getElementById('filter-language'),
        institution: document.getElementById('filter-institution'),
        year: document.getElementById('filter-year'),
        yearFrom: document.getElementById('filter-year-from'),
        yearTo: document.getElementById('filter-year-to'),
        author: document.getElementById('filter-author')
    };

    // Inicialização
    init();

    function init() {
        loadFilterOptions();
        setupEventListeners();
        fetchResults(); // Carrega resultados iniciais
        loadStatistics(); // Carrega estatísticas
    }

    function setupEventListeners() {
        // Busca principal
        elements.searchQuery.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') fetchResults();
        });
        elements.btnSearch.addEventListener('click', fetchResults);
        elements.btnFilter.addEventListener('click', fetchResults);
        elements.btnClear.addEventListener('click', clearFilters);

        // Busca de pesquisadores
        elements.researcherSearch.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') searchResearchers();
        });
        elements.btnSearchResearchers.addEventListener('click', searchResearchers);

        // Exportação
        document.querySelectorAll('[data-format]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                exportData(e.target.dataset.format);
            });
        });

        // Links de navegação
        document.getElementById('researchers-link')?.addEventListener('click', () => {
            document.getElementById('nav-researchers-tab').click();
        });
        document.getElementById('stats-link')?.addEventListener('click', () => {
            document.getElementById('nav-stats-tab').click();
        });

        // Carregar estatísticas quando a aba for ativada
        document.getElementById('nav-stats-tab').addEventListener('shown.bs.tab', loadStatistics);
    }

    async function loadFilterOptions() {
        const filterFields = ['type', 'language', 'institution', 'year'];
        
        for (const field of filterFields) {
            try {
                const response = await fetch(`api/filter_values.php?field=${field}&size=50`);
                const data = await response.json();
                
                if (data.values && filters[field]) {
                    populateSelect(filters[field], data.values, field === 'year');
                }
            } catch (error) {
                console.error(`Erro ao carregar opções para ${field}:`, error);
            }
        }
    }

    function populateSelect(selectElement, values, isNumeric = false) {
        // Limpar opções existentes (exceto a primeira)
        while (selectElement.children.length > 1) {
            selectElement.removeChild(selectElement.lastChild);
        }

        // Ordenar valores
        if (isNumeric) {
            values.sort((a, b) => b - a); // Ordem decrescente para anos
        } else {
            values.sort();
        }

        // Adicionar opções
        values.forEach(value => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            selectElement.appendChild(option);
        });
    }

    function getFiltersFromForm() {
        const formFilters = {
            q: elements.searchQuery.value.trim(),
            type: filters.type.value,
            language: filters.language.value,
            institution: filters.institution.value,
            year: filters.year.value,
            year_from: filters.yearFrom.value,
            year_to: filters.yearTo.value,
            author: filters.author.value.trim()
        };

        // Remove valores vazios
        return Object.fromEntries(
            Object.entries(formFilters).filter(([key, value]) => value !== '')
        );
    }

    async function fetchResults() {
        const currentFilters = getFiltersFromForm();
        appState.currentFilters = currentFilters;

        // Monta a URL da API com os parâmetros de filtro
        const queryParams = new URLSearchParams(currentFilters).toString();
        const apiUrl = `api/search.php?${queryParams}&include_stats=true`;

        // Limpa resultados anteriores e mostra o loading
        elements.tableBody.innerHTML = '';
        elements.resultsSummary.innerHTML = '';
        elements.loadingIndicator.style.display = 'block';

        try {
            const response = await fetch(apiUrl);
            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                const errorMessage = errorData?.details || `HTTP error! status: ${response.status}`;
                throw new Error(errorMessage);
            }
            const data = await response.json();

            // Esconde o loading
            elements.loadingIndicator.style.display = 'none';

            if (data.hits && data.hits.hits.length > 0) {
                appState.currentResults = data.hits.hits.map(hit => ({
                    ...hit._source,
                    _id: hit._id
                }));
                displayResults(data.hits.hits);
                elements.resultsSummary.textContent = `Exibindo ${data.hits.hits.length} de ${data.hits.total.value} resultados.`;
            } else {
                elements.resultsSummary.innerHTML = '<div class="alert alert-info">Nenhum resultado encontrado.</div>';
            }

        } catch (error) {
            elements.loadingIndicator.style.display = 'none';
            elements.resultsSummary.innerHTML = `<div class="alert alert-danger">Erro ao buscar dados: ${error.message}</div>`;
            console.error('Fetch error:', error);
        }
    }

    function displayResults(hits) {
        const tbody = elements.tableBody;
        tbody.innerHTML = '';

        hits.forEach(hit => {
            const doc = hit._source;
            const row = document.createElement('tr');
            
            // Mapear campos em português para inglês (compatibilidade)
            const title = doc.titulo || doc.title || 'undefined';
            const year = doc.ano || doc.year || 'undefined';
            const type = doc.tipo || doc.type || 'undefined';
            const researcher = doc.autores || doc.researcher_name || doc.authors || 'undefined';
            
            // Determinar veículo de publicação
            const venue = doc.periodico || doc.journal || doc.nome_evento || doc.event_name || doc.titulo_livro || doc.book_title || doc.editora || doc.publisher || 'N/A';
            
            row.innerHTML = `
                <td>
                    <strong>${title}</strong>
                    ${doc.natureza || doc.subtype ? `<br><small class="text-muted">${doc.natureza || doc.subtype}</small>` : ''}
                </td>
                <td>${researcher}</td>
                <td>${year}</td>
                <td>
                    <span class="badge bg-primary">${type}</span>
                </td>
                <td>
                    ${venue}
                    ${doc.idioma || doc.language ? `<br><small class="text-muted">Idioma: ${doc.idioma || doc.language}</small>` : ''}
                </td>
                <td>
                    <div class="btn-group-vertical btn-group-sm">
                        ${doc.doi ? `<a href="https://doi.org/${doc.doi}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-link-45deg"></i> DOI
                        </a>` : ''}
                        ${doc.open_access_url ? `<a href="${doc.open_access_url}" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-unlock"></i> Acesso Aberto
                        </a>` : ''}
                        <button class="btn btn-outline-info btn-sm" onclick="showDetails('${doc._id || hit._id}')">
                            <i class="bi bi-info-circle"></i> Detalhes
                        </button>
                    </div>
                </td>
            `;
            
            tbody.appendChild(row);
        });
    }

    async function searchResearchers() {
        const query = elements.researcherSearch.value.trim();
        if (!query) return;

        const resultsDiv = elements.researchersResults;
        resultsDiv.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';

        try {
            const response = await fetch(`api/researchers.php?q=${encodeURIComponent(query)}&size=20`);
            const data = await response.json();

            if (data.researchers && data.researchers.length > 0) {
                displayResearchers(data.researchers);
            } else {
                resultsDiv.innerHTML = '<div class="alert alert-info">Nenhum pesquisador encontrado.</div>';
            }
        } catch (error) {
            resultsDiv.innerHTML = `<div class="alert alert-danger">Erro ao buscar pesquisadores: ${error.message}</div>`;
        }
    }

    function displayResearchers(researchers) {
        const resultsDiv = elements.researchersResults;
        
        const html = researchers.map(researcher => `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title">${researcher.name}</h5>
                            <p class="card-text">
                                <strong>Produções:</strong> ${researcher.production_count} 
                                <span class="text-muted">(última em ${researcher.latest_year})</span>
                            </p>
                            <p class="card-text">
                                <strong>Instituições:</strong> ${researcher.institutions.join(', ')}
                            </p>
                            <p class="card-text">
                                <strong>Áreas:</strong> ${researcher.areas.slice(0, 3).join(', ')}
                                ${researcher.areas.length > 3 ? '...' : ''}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <button class="btn btn-primary btn-sm" onclick="filterByResearcher('${researcher.name}')">
                                    <i class="bi bi-search"></i> Ver Produções
                                </button>
                                ${researcher.lattes_id ? `
                                <button class="btn btn-outline-info btn-sm mt-1" onclick="viewLattesProfile('${researcher.lattes_id}')">
                                    <i class="bi bi-person-badge"></i> Currículo Lattes
                                </button>` : ''}
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Tipos de produção:</small>
                                <div class="mt-1">
                                    ${researcher.production_types.map(type => 
                                        `<span class="badge bg-secondary me-1">${type.type}: ${type.count}</span>`
                                    ).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        resultsDiv.innerHTML = html;
    }

    async function loadStatistics() {
        try {
            const response = await fetch('api/search.php?include_stats=true&size=0');
            const data = await response.json();
            
            if (data.aggregations) {
                createCharts(data.aggregations);
            }
        } catch (error) {
            console.error('Erro ao carregar estatísticas:', error);
        }
    }

    function createCharts(aggregations) {
        // Gráfico por ano
        if (aggregations.by_year) {
            createLineChart('chart-by-year', aggregations.by_year.buckets, 'Produção por Ano');
        }

        // Gráfico por tipo
        if (aggregations.by_type) {
            createPieChart('chart-by-type', aggregations.by_type.buckets, 'Produção por Tipo');
        }

        // Gráfico por instituição
        if (aggregations.by_institution) {
            createBarChart('chart-by-institution', aggregations.by_institution.buckets, 'Produção por Instituição');
        }

        // Gráfico por área
        if (aggregations.by_areas && aggregations.by_areas.area_breakdown) {
            createDoughnutChart('chart-by-area', aggregations.by_areas.area_breakdown.buckets, 'Produção por Área');
        }
    }

    function createLineChart(canvasId, data, title) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        if (appState.charts[canvasId]) {
            appState.charts[canvasId].destroy();
        }

        appState.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.key),
                datasets: [{
                    label: 'Quantidade',
                    data: data.map(item => item.doc_count),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: title
                    }
                }
            }
        });
    }

    function createPieChart(canvasId, data, title) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        if (appState.charts[canvasId]) {
            appState.charts[canvasId].destroy();
        }

        appState.charts[canvasId] = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(item => item.key),
                datasets: [{
                    data: data.map(item => item.doc_count),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#36A2EB'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: title
                    }
                }
            }
        });
    }

    function createBarChart(canvasId, data, title) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        if (appState.charts[canvasId]) {
            appState.charts[canvasId].destroy();
        }

        appState.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.slice(0, 10).map(item => item.key.length > 20 ? item.key.substring(0, 20) + '...' : item.key),
                datasets: [{
                    label: 'Quantidade',
                    data: data.slice(0, 10).map(item => item.doc_count),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: title
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function createDoughnutChart(canvasId, data, title) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        if (appState.charts[canvasId]) {
            appState.charts[canvasId].destroy();
        }

        appState.charts[canvasId] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.slice(0, 8).map(item => item.key),
                datasets: [{
                    data: data.slice(0, 8).map(item => item.doc_count),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#36A2EB'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: title
                    }
                }
            }
        });
    }

    function clearFilters() {
        elements.searchQuery.value = '';
        Object.values(filters).forEach(filter => {
            if (filter.tagName === 'SELECT') {
                filter.selectedIndex = 0;
            } else {
                filter.value = '';
            }
        });
        fetchResults();
    }

    function exportData(format) {
        const queryParams = new URLSearchParams(appState.currentFilters);
        queryParams.set('format', format);
        queryParams.set('size', '1000'); // Máximo para exportação
        
        const exportUrl = `api/export.php?${queryParams.toString()}`;
        window.open(exportUrl, '_blank');
    }

    // Funções globais para callbacks
    window.showDetails = function(id) {
        const production = appState.currentResults.find(p => (p._id === id || p.id === id));
        if (production) {
            // Criar modal com detalhes
            showProductionModal(production);
        } else {
            console.error('Produção não encontrada:', id);
        }
    };

    window.filterByResearcher = function(researcherName) {
        elements.searchQuery.value = researcherName;
        document.getElementById('nav-search-tab').click();
        setTimeout(fetchResults, 100);
    };

    window.viewLattesProfile = function(lattesId) {
        window.open(`http://lattes.cnpq.br/${lattesId}`, '_blank');
    };

    function showProductionModal(production) {
        const modalHtml = `
            <div class="modal fade" id="productionModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detalhes da Produção</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h6><strong>${production.titulo || production.title || 'Sem título'}</strong></h6>
                            <p><strong>Autor(es):</strong> ${production.autores || production.researcher_name || production.authors || 'N/A'}</p>
                            <p><strong>Ano:</strong> ${production.ano || production.year || 'N/A'}</p>
                            <p><strong>Tipo:</strong> ${production.tipo || production.type || 'N/A'}${production.natureza || production.subtype ? ` (${production.natureza || production.subtype})` : ''}</p>
                            ${production.periodico || production.journal ? `<p><strong>Revista:</strong> ${production.periodico || production.journal}</p>` : ''}
                            ${production.nome_evento || production.event_name ? `<p><strong>Evento:</strong> ${production.nome_evento || production.event_name}</p>` : ''}
                            ${production.editora || production.publisher ? `<p><strong>Editora:</strong> ${production.editora || production.publisher}</p>` : ''}
                            ${production.issn ? `<p><strong>ISSN:</strong> ${production.issn}</p>` : ''}
                            ${production.isbn ? `<p><strong>ISBN:</strong> ${production.isbn}</p>` : ''}
                            ${production.volume ? `<p><strong>Volume:</strong> ${production.volume}</p>` : ''}
                            ${production.pagina_inicial ? `<p><strong>Páginas:</strong> ${production.pagina_inicial}${production.pagina_final ? `-${production.pagina_final}` : ''}</p>` : ''}
                            ${production.doi ? `<p><strong>DOI:</strong> <a href="https://doi.org/${production.doi}" target="_blank">${production.doi}</a></p>` : ''}
                            ${production.ppg ? `<p><strong>PPG:</strong> ${production.ppg}</p>` : ''}
                            ${production.area_concentracao ? `<p><strong>Área:</strong> ${production.area_concentracao}</p>` : ''}
                            ${production.instituicao || production.institution ? `<p><strong>Instituição:</strong> ${production.instituicao || production.institution}</p>` : ''}
                            ${production.idioma || production.language ? `<p><strong>Idioma:</strong> ${production.idioma || production.language}</p>` : ''}
                            ${production.citacoes || production.cited_by_count ? `<p><strong>Citações:</strong> ${production.citacoes || production.cited_by_count}</p>` : ''}
                            ${production.lattesID ? `<p><strong>Lattes ID:</strong> ${production.lattesID}</p>` : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            ${production.doi ? `<a href="https://doi.org/${production.doi}" target="_blank" class="btn btn-primary">Abrir DOI</a>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove modal anterior se existir
        const existingModal = document.getElementById('productionModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Adiciona novo modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('productionModal'));
        modal.show();
    }
});
