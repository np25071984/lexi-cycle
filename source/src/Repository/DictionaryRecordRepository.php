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
            SELECT *
            FROM "dictionary"
            WHERE record_id = :record_id
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('record_id', $recordId);
        $result = $stmt->executeQuery();

        $records = $result->fetchAllAssociative($query);
        $rawRecord = $records[0] ?? null;
        if (is_null($rawRecord)) {
            throw new \Exception("DictionaryRecord wasn't found");
        }

        return $this->buildEntity($rawRecord);
    }

    public function findByKey(string $key): ?DictionaryRecordEntity
    {
        $query = <<<SQL
            SELECT *
            FROM "dictionary"
            WHERE key = :key
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('key', $key);
        $result = $stmt->executeQuery();

        $records = $result->fetchAllAssociative($query);
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

        $query = <<<SQL
            INSERT INTO "dictionary"(key, meaning, links)
            VALUES(
                :key,
                :meaning,
                :sql_links
            )
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('key', $key);
        $stmt->bindValue('meaning', $record->getMeaning());
        if (count($links)) {
            $linksEncoded = json_encode($links);
            $stmt->bindValue('sql_links', $linksEncoded);
        } else {
            $stmt->bindValue('sql_links', null);
        }
        $stmt->executeQuery();

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