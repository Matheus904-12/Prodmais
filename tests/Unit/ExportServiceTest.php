<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Domain/Services/ExportService.php';

class ExportServiceTest extends TestCase
{
    private \ExportService $service;

    protected function setUp(): void
    {
        $this->service = new \ExportService();
    }

    private function sampleArticle(): array
    {
        return [
            'title' => 'Biotecnologia Aplicada à Saúde',
            'researcher_name' => 'Maria da Silva',
            'year' => 2024,
            'type' => 'Artigo Publicado',
            'journal' => 'Revista Brasileira de Ciências',
            'volume' => '10',
            'pages' => '1-20',
            'doi' => '10.1234/abcd',
            'email' => 'maria@umc.br',
            'cpf' => '123.456.789-00',
        ];
    }

    public function testExportBibTeXContainsExpectedFields(): void
    {
        $bibtex = $this->service->exportBibTeX([$this->sampleArticle()]);

        $this->assertStringContainsString('@article{', $bibtex);
        $this->assertStringContainsString('title = {Biotecnologia Aplicada à Saúde}', $bibtex);
        $this->assertStringContainsString('author = {Maria da Silva}', $bibtex);
        $this->assertStringContainsString('journal = {Revista Brasileira de Ciências}', $bibtex);
        $this->assertStringContainsString('doi = {10.1234/abcd}', $bibtex);
    }

    public function testExportBibTeXAnonymizesPersonalDataButKeepsAuthor(): void
    {
        $bibtex = $this->service->exportBibTeX([$this->sampleArticle()]);

        $this->assertStringNotContainsString('maria@umc.br', $bibtex);
        $this->assertStringNotContainsString('123.456.789-00', $bibtex);
        $this->assertStringContainsString('Maria da Silva', $bibtex);
    }

    public function testExportBibTeXEscapesSpecialCharacters(): void
    {
        $production = $this->sampleArticle();
        $production['title'] = 'Custo & Benefício 50% em R$';

        $bibtex = $this->service->exportBibTeX([$production]);

        $this->assertStringContainsString('Custo \\& Benef', $bibtex);
        $this->assertStringContainsString('\\%', $bibtex);
        $this->assertStringContainsString('\\$', $bibtex);
    }

    public function testExportBibTeXMapsBookType(): void
    {
        $production = [
            'title' => 'Livro X',
            'researcher_name' => 'João Pereira',
            'year' => 2023,
            'type' => 'Livro',
            'publisher' => 'Editora UMC',
            'city' => 'Mogi das Cruzes',
            'isbn' => '978-0-000-00000-0',
        ];

        $bibtex = $this->service->exportBibTeX([$production]);

        $this->assertStringContainsString('@book{', $bibtex);
        $this->assertStringContainsString('publisher = {Editora UMC}', $bibtex);
        $this->assertStringContainsString('isbn = {978-0-000-00000-0}', $bibtex);
    }

    public function testExportRISContainsExpectedTags(): void
    {
        $ris = $this->service->exportRIS([$this->sampleArticle()]);

        $this->assertStringContainsString('TY  - JOUR', $ris);
        $this->assertStringContainsString('TI  - Biotecnologia Aplicada à Saúde', $ris);
        $this->assertStringContainsString('AU  - Maria da Silva', $ris);
        $this->assertStringContainsString('PY  - 2024', $ris);
        $this->assertStringContainsString('ER  - ', $ris);
    }

    public function testExportCSVReturnsEmptyStringForEmptyInput(): void
    {
        $this->assertSame('', $this->service->exportCSV([]));
    }

    public function testExportCSVContainsHeaderAndRow(): void
    {
        $csv = $this->service->exportCSV([$this->sampleArticle()]);
        $lines = explode("\n", trim($csv));

        $this->assertCount(2, $lines);
        $this->assertStringContainsString('Título;Autor;Ano', $lines[0]);
        $this->assertStringContainsString('Biotecnologia Aplicada à Saúde', $lines[1]);
        $this->assertStringContainsString('Maria da Silva', $lines[1]);
    }

    public function testExportCSVDoesNotLeakEmail(): void
    {
        $csv = $this->service->exportCSV([$this->sampleArticle()]);

        $this->assertStringNotContainsString('maria@umc.br', $csv);
    }

    public function testExportJSONProducesValidAnonymizedJson(): void
    {
        $json = $this->service->exportJSON([$this->sampleArticle()]);
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertSame('Biotecnologia Aplicada à Saúde', $decoded[0]['title']);
        $this->assertArrayNotHasKey('email', $decoded[0]);
        $this->assertArrayNotHasKey('cpf', $decoded[0]);
    }

    public function testExportXMLProducesValidStructure(): void
    {
        $xml = $this->service->exportXML([$this->sampleArticle()]);
        $simplexml = simplexml_load_string($xml);

        $this->assertNotFalse($simplexml);
        $this->assertSame('Biotecnologia Aplicada à Saúde', (string) $simplexml->production[0]->title);
        $this->assertStringNotContainsString('maria@umc.br', $xml);
    }

    public function testExportXMLSanitizesInvalidFieldNames(): void
    {
        $production = ['researcher_name' => 'Maria', 'ano-de-publicacao' => 2024];

        $xml = $this->service->exportXML([$production]);

        $this->assertStringContainsString('<ano_de_publicacao>2024</ano_de_publicacao>', $xml);
    }

    public function testPrepareForORCIDMapsFieldsCorrectly(): void
    {
        $works = $this->service->prepareForORCID([$this->sampleArticle()]);

        $this->assertCount(1, $works);
        $this->assertSame('Biotecnologia Aplicada à Saúde', $works[0]['title']['title']['value']);
        $this->assertSame('journal-article', $works[0]['type']);
        $this->assertSame('10.1234/abcd', $works[0]['external-ids']['external-id'][0]['external-id-value']);
        $this->assertSame('2024', $works[0]['publication-date']['year']['value']);
    }

    public function testPrepareForORCIDOmitsDoiWhenAbsent(): void
    {
        $production = $this->sampleArticle();
        unset($production['doi']);

        $works = $this->service->prepareForORCID([$production]);

        $this->assertSame([], $works[0]['external-ids']['external-id']);
    }

    public function testMultipleProductionsAreAllExported(): void
    {
        $productions = [$this->sampleArticle(), $this->sampleArticle()];
        $productions[1]['title'] = 'Segundo Artigo';

        $bibtex = $this->service->exportBibTeX($productions);

        $this->assertStringContainsString('Biotecnologia Aplicada à Saúde', $bibtex);
        $this->assertStringContainsString('Segundo Artigo', $bibtex);
    }
}
