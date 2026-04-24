<?php

class JsonStorageService
{
    private $dbPath;

    public function __construct(string $dbPath)
    {
        $this->dbPath = $dbPath;
    }

    public function recreateStorage(array $documents)
    {
        file_put_contents($this->dbPath, json_encode($documents, JSON_PRETTY_PRINT));
    }

    public function search(array $filters = []): array
    {
        if (!file_exists($this->dbPath)) {
            return [];
        }

        $data = json_decode(file_get_contents($this->dbPath), true);

        if (empty($filters)) {
            return $data;
        }

        $results = array_filter($data, function ($item) use ($filters) {
            foreach ($filters as $key => $value) {
                if (empty($value)) continue;
                if (!isset($item[$key]) || $item[$key] != $value) {
                    return false;
                }
            }
            return true;
        });

        return array_values($results);
    }
}
