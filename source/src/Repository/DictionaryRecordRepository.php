<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\DictionaryRecordEntity;
use Doctrine\DBAL\Connection;
use DateTimeImmutable;
use DateInterval;

class DictionaryRecordRepository
{
    public function __construct(
        private Connection $connection
    ) {}

    public function getByRecordId(int $recordId): DictionaryRecordEntity
    {
        $query = <<<SQL
            SELECT
                *
            FROM "dictionary"
            WHERE record_id = {$recordId}
            SQL;

        $records = $this->connection->fetchAllAssociative($query);
        $rawRecord = $records[0] ?? null;
        if (is_null($rawRecord)) {
            throw new \Exception("DictionaryRecord wasn't found");
        }

        return $this->buildEntity($rawRecord);
    }

    public function findByKey(string $key): ?DictionaryRecordEntity
    {
        $query = <<<SQL
            SELECT
                *
            FROM "dictionary"
            WHERE key = '{$key}'
            SQL;

        $records = $this->connection->fetchAllAssociative($query);
        $rawRecord = $records[0] ?? null;
        if (is_null($rawRecord)) {
            return null;
        }

        return $this->buildEntity($rawRecord);
    }

    public function save(DictionaryRecordEntity $record): DictionaryRecordEntity
    {
        $key = $record->getKey();
        $links = $record->getLinks();
        $sqlLinks = count($links) ? "'{json_encode($links)}'" : 'NULL';

        $query = <<<SQL
            INSERT INTO "dictionary"(key, meaning, links)
            VALUES(
                '{$key}',
                '{$record->getMeaning()}',
                {$sqlLinks}
            )
            SQL;

        $this->connection->executeQuery($query);

        $realRecord = $this->findByKey($key);
        if (is_null($realRecord)) {
            throw new \Exception("Couldn't save DictionaryRecordEntity");
        }

        return $realRecord;
    }

    private function buildEntity(array $rawData): DictionaryRecordEntity
    {
        $links = json_decode($rawData["links"] ?? "[]", true);

        return new DictionaryRecordEntity(
            (int)$rawData["record_id"],
            $rawData["key"],
            $rawData["meaning"],
            $links
        );
    }
}