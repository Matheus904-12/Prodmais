<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/Core/Anonymizer.php';

class AnonymizerTest extends TestCase
{
    private \Anonymizer $anonymizer;

    protected function setUp(): void
    {
        $this->anonymizer = new \Anonymizer('salt-de-teste');
    }

    public function testMinimalAnonymizationRemovesCriticalFieldsOnly(): void
    {
        $data = [
            'cpf' => '123.456.789-00',
            'email' => 'pesquisador@umc.br',
            'researcher_name' => 'Maria da Silva',
            'title' => 'Artigo sobre biotecnologia',
        ];

        $result = $this->anonymizer->anonymize($data, ['level' => 'minimal']);

        $this->assertArrayNotHasKey('cpf', $result);
        $this->assertArrayNotHasKey('email', $result);
        $this->assertSame('Maria da Silva', $result['researcher_name']);
        $this->assertSame('Artigo sobre biotecnologia', $result['title']);
    }

    public function testStandardAnonymizationTurnsNomeCompletoIntoInitials(): void
    {
        // 'researcher_name' não está em $sensitiveFields — só 'nome_completo' é
        // anonimizado no nível standard (o nome do pesquisador é preservado de
        // propósito nas exportações, que usam nível 'minimal').
        $data = ['nome_completo' => 'Maria da Silva Santos'];

        $result = $this->anonymizer->anonymize($data, ['level' => 'standard']);

        $this->assertSame('M. D. S. S.', $result['nome_completo']);
    }

    public function testStandardAnonymizationPreservesResearcherNameUnaffected(): void
    {
        $data = ['researcher_name' => 'Maria da Silva Santos'];

        $result = $this->anonymizer->anonymize($data, ['level' => 'standard']);

        $this->assertSame('Maria da Silva Santos', $result['researcher_name']);
    }

    public function testStandardAnonymizationRemovesEmailAndCpf(): void
    {
        $data = [
            'researcher_name' => 'Maria da Silva',
            'email' => 'maria@umc.br',
            'cpf' => '123.456.789-00',
            'title' => 'Artigo X',
        ];

        $result = $this->anonymizer->anonymize($data, ['level' => 'standard']);

        $this->assertArrayNotHasKey('email', $result);
        $this->assertArrayNotHasKey('cpf', $result);
        $this->assertSame('Artigo X', $result['title']);
    }

    public function testKeepFieldsOptionPreservesFieldFromRemoval(): void
    {
        $data = ['email' => 'maria@umc.br', 'title' => 'Artigo X'];

        $result = $this->anonymizer->anonymize($data, [
            'level' => 'minimal',
            'keep_fields' => ['email'],
        ]);

        $this->assertSame('maria@umc.br', $result['email']);
    }

    public function testFullAnonymizationHashesResearcherNameConsistently(): void
    {
        $data = ['researcher_name' => 'Maria da Silva', 'title' => 'Artigo X'];

        $result1 = $this->anonymizer->anonymize($data, ['level' => 'full']);
        $result2 = $this->anonymizer->anonymize($data, ['level' => 'full']);

        $this->assertSame($result1['researcher_name'], $result2['researcher_name']);
        $this->assertNotSame('Maria da Silva', $result1['researcher_name']);
        $this->assertSame('Artigo X', $result1['title']);
    }

    public function testFullAnonymizationWithoutPreserveStatisticsRemovesName(): void
    {
        $anonymizer = new \Anonymizer('salt-de-teste', false);
        $data = ['researcher_name' => 'Maria da Silva'];

        $result = $anonymizer->anonymize($data, ['level' => 'full']);

        $this->assertArrayNotHasKey('researcher_name', $result);
    }

    public function testStatisticalAnonymizationKeepsOnlyAggregateFields(): void
    {
        $data = [
            'researcher_name' => 'Maria da Silva',
            'email' => 'maria@umc.br',
            'year' => 2024,
            'type' => 'Artigo Publicado',
            'institution' => 'UMC',
        ];

        $result = $this->anonymizer->anonymize($data, ['level' => 'statistical']);

        $this->assertArrayNotHasKey('researcher_name', $result);
        $this->assertArrayNotHasKey('email', $result);
        $this->assertSame(2024, $result['year']);
        $this->assertSame('Artigo Publicado', $result['type']);
        $this->assertArrayHasKey('anonymous_id', $result);
    }

    public function testStatisticalAnonymizationGeneratesSameIdForSameInput(): void
    {
        $data = ['researcher_lattes_id' => '1234567890123456', 'title' => 'X', 'year' => 2024];

        $result1 = $this->anonymizer->anonymize($data, ['level' => 'statistical']);
        $result2 = $this->anonymizer->anonymize($data, ['level' => 'statistical']);

        $this->assertSame($result1['anonymous_id'], $result2['anonymous_id']);
    }

    public function testInvalidAnonymizationLevelThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->anonymizer->anonymize(['title' => 'X'], ['level' => 'nivel_invalido']);
    }

    public function testHashPreservingFormatKeepsLattesIdLength(): void
    {
        $data = ['researcher_lattes_id' => '1234567890123456'];

        $result = $this->anonymizer->anonymize($data, ['level' => 'standard']);

        $this->assertSame(16, strlen($result['researcher_lattes_id']));
        $this->assertMatchesRegularExpression('/^\d{16}$/', $result['researcher_lattes_id']);
    }

    public function testHashPreservingFormatKeepsOrcidPattern(): void
    {
        $data = ['researcher_lattes_id' => '0000-0001-2345-6789'];

        $result = $this->anonymizer->anonymize($data, ['level' => 'standard']);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{4}-\d{4}-\d{4}$/', $result['researcher_lattes_id']);
    }

    public function testAnonymizeAuthorsListNested(): void
    {
        $data = [
            'authors' => [
                ['name' => 'Maria da Silva'],
                ['name' => 'João Pereira'],
            ],
        ];

        $result = $this->anonymizer->anonymize($data, ['level' => 'standard']);

        $this->assertSame('M. D. S.', $result['authors'][0]['name']);
        $this->assertSame('J. P.', $result['authors'][1]['name']);
    }

    public function testContainsPersonalDataDetectsSensitiveFields(): void
    {
        $this->assertTrue($this->anonymizer->containsPersonalData(['email' => 'x@y.com']));
        $this->assertFalse($this->anonymizer->containsPersonalData(['title' => 'Artigo X', 'year' => 2024]));
    }

    public function testGenerateAnonymizationReportClassifiesFields(): void
    {
        $original = ['email' => 'x@y.com', 'title' => 'Artigo X', 'nome_completo' => 'Maria da Silva'];
        $anonymized = $this->anonymizer->anonymize($original, ['level' => 'standard']);

        $report = $this->anonymizer->generateAnonymizationReport($original, $anonymized);

        $this->assertContains('email', $report['removed_fields']);
        $this->assertContains('title', $report['preserved_fields']);
        $this->assertContains('nome_completo', $report['anonymized_fields']);
    }

    public function testAnonymizeBatchYieldsProgressForEachChunk(): void
    {
        $items = array_fill(0, 5, ['email' => 'x@y.com', 'title' => 'Artigo']);

        $batches = iterator_to_array($this->anonymizer->anonymizeBatch($items, ['level' => 'minimal'], 2));

        $this->assertCount(3, $batches);
        $this->assertSame(2, $batches[0]['progress']['processed']);
        $this->assertSame(5, $batches[2]['progress']['processed']);
        $this->assertSame(100.0, $batches[2]['progress']['percentage']);
    }

    public function testAnonymizeStaticLegacyMethod(): void
    {
        $result = \Anonymizer::anonymize_static(['cpf' => '123', 'title' => 'Artigo X']);

        $this->assertArrayNotHasKey('cpf', $result);
        $this->assertSame('Artigo X', $result['title']);
    }
}
