    public function getPesquisadores() {
        $stmt = $this->pdo->query("SELECT * FROM pesquisadores ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
<?php
/**
 * DatabaseService - Persistência relacional para dados do sistema
 * Suporte a SQLite (default) e MySQL (fácil migração)
 */
class DatabaseService {
    private $pdo;

    public function __construct($config = null) {
        $dbPath = $config['data_paths']['db'] ?? __DIR__.'/../data/prodmais.sqlite';
        $dsn = "sqlite:$dbPath";
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initTables();
    }

    private function initTables() {
        // Pesquisadores
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS pesquisadores (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT,
            lattesID TEXT,
            orcidID TEXT,
            email TEXT,
            instituicao TEXT,
            ppg TEXT,
            area_concentracao TEXT,
            campus TEXT
        )");
        // Projetos
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS projetos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo TEXT,
            ano_inicio INTEGER,
            ano_fim INTEGER,
            situacao TEXT,
            financiamento TEXT,
            equipe TEXT,
            ppg TEXT
        )");
        // Produções
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS producoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo TEXT,
            autores TEXT,
            ano INTEGER,
            tipo TEXT,
            doi TEXT,
            issn TEXT,
            qualis TEXT,
            ppg TEXT,
            area_concentracao TEXT,
            idioma TEXT,
            citacoes INTEGER,
            openalex_id TEXT
        )");
    }

    // CRUD Pesquisadores
    public function addPesquisador($dados) {
        $stmt = $this->pdo->prepare("INSERT INTO pesquisadores (nome, lattesID, orcidID, email, instituicao, ppg, area_concentracao, campus) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $dados['nome_completo'] ?? '',
            $dados['lattesID'] ?? '',
            $dados['orcidID'] ?? '',
            $dados['email'] ?? '',
            $dados['instituicao'] ?? '',
            $dados['ppg'] ?? '',
            $dados['area_concentracao'] ?? '',
            $dados['campus'] ?? ''
        ]);
        return $this->pdo->lastInsertId();
    }
    public function updatePesquisador($dados) {
        $stmt = $this->pdo->prepare("UPDATE pesquisadores SET nome=?, orcidID=?, email=?, instituicao=?, ppg=?, area_concentracao=?, campus=? WHERE lattesID=?");
        $stmt->execute([
            $dados['nome_completo'] ?? '',
            $dados['orcidID'] ?? '',
            $dados['email'] ?? '',
            $dados['instituicao'] ?? '',
            $dados['ppg'] ?? '',
            $dados['area_concentracao'] ?? '',
            $dados['campus'] ?? '',
            $dados['lattesID'] ?? ''
        ]);
    }
    // CRUD Projetos
    public function addProjeto($dados) {
        $stmt = $this->pdo->prepare("INSERT INTO projetos (titulo, ano_inicio, ano_fim, situacao, financiamento, equipe, ppg) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $dados['titulo'] ?? '',
            $dados['ano_inicio'] ?? null,
            $dados['ano_fim'] ?? null,
            $dados['situacao'] ?? '',
            $dados['financiamento'] ?? '',
            json_encode($dados['equipe'] ?? []),
            $dados['ppg'] ?? ''
        ]);
        return $this->pdo->lastInsertId();
    }
    public function updateProjeto($dados) {
        $stmt = $this->pdo->prepare("UPDATE projetos SET ano_inicio=?, ano_fim=?, situacao=?, financiamento=?, equipe=?, ppg=? WHERE titulo=?");
        $stmt->execute([
            $dados['ano_inicio'] ?? null,
            $dados['ano_fim'] ?? null,
            $dados['situacao'] ?? '',
            $dados['financiamento'] ?? '',
            json_encode($dados['equipe'] ?? []),
            $dados['ppg'] ?? '',
            $dados['titulo'] ?? ''
        ]);
    }
    // CRUD Produções
    public function addProducao($dados) {
        $stmt = $this->pdo->prepare("INSERT INTO producoes (titulo, autores, ano, tipo, doi, issn, qualis, ppg, area_concentracao, idioma, citacoes, openalex_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $dados['titulo'] ?? '',
            $dados['autores'] ?? '',
            $dados['ano'] ?? null,
            $dados['tipo'] ?? '',
            $dados['doi'] ?? '',
            $dados['issn'] ?? '',
            $dados['qualis'] ?? '',
            $dados['ppg'] ?? '',
            $dados['area_concentracao'] ?? '',
            $dados['idioma'] ?? '',
            $dados['citacoes'] ?? 0,
            $dados['openalex_id'] ?? ''
        ]);
        return $this->pdo->lastInsertId();
    }
    public function updateProducao($dados) {
        $stmt = $this->pdo->prepare("UPDATE producoes SET autores=?, ano=?, tipo=?, issn=?, qualis=?, ppg=?, area_concentracao=?, idioma=?, citacoes=?, openalex_id=? WHERE titulo=? AND doi=?");
        $stmt->execute([
            $dados['autores'] ?? '',
            $dados['ano'] ?? null,
            $dados['tipo'] ?? '',
            $dados['issn'] ?? '',
            $dados['qualis'] ?? '',
            $dados['ppg'] ?? '',
            $dados['area_concentracao'] ?? '',
            $dados['idioma'] ?? '',
            $dados['citacoes'] ?? 0,
            $dados['openalex_id'] ?? '',
            $dados['titulo'] ?? '',
            $dados['doi'] ?? ''
        ]);
    }
    // ... Métodos de consulta e atualização podem ser adicionados conforme necessário ...
}
