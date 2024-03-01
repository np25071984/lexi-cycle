<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserDictionaryRecordEntity;
use App\Entity\UserEntity;
use Doctrine\DBAL\Connection;
use App\State\StateFactory;
use DateTimeImmutable;
use DateInterval;
use DateTimeZone;

class UserDictionaryRecordRepository
{
    public function __construct(
        private Connection $connection,
        private StateFactory $stateFactory,
    ) {}

    public function findRecordToReview(int $userId): ?UserDictionaryRecordEntity
    {
        $query = <<<SQL
            SELECT
                d."key",
                d.record_id,
                CASE WHEN ud.meaning IS NULL THEN d.meaning ELSE ud.meaning END AS meaning,
                ud.due,
                u.timezone,
                CASE WHEN ud.links IS NULL THEN d.links ELSE ud.links END AS links,
                state
            FROM "user-dictionary" ud
            INNER JOIN dictionary d ON d.record_id = ud.record_id
            INNER JOIN "user" u ON u.id = ud.user_id
            WHERE ud.user_id = :user_id AND ud.due < NOW()
            ORDER BY ud.due ASC
            LIMIT 1
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('user_id', $userId);
        $result = $stmt->executeQuery();

        $records = $result->fetchAllAssociative($query);
        $rawRecord = $records[0] ?? null;
        if (is_null($rawRecord)) {
            return null;
        }

        return $this->buildEntity($userId, $rawRecord);
    }

    /**
     * @return UserDictionaryRecordEntity[]
     */
    public function getRecords(int $userId, int $offset, int $limit): array
    {
        $query = <<<SQL
            SELECT
                d."key",
                d.record_id,
                CASE WHEN ud.meaning IS NULL THEN d.meaning ELSE ud.meaning END AS meaning,
                ud.due,
                u.timezone,
                CASE WHEN ud.links IS NULL THEN d.links ELSE ud.links END AS links,
                state
            FROM "user-dictionary" ud
            INNER JOIN dictionary d ON d.record_id = ud.record_id
            INNER JOIN "user" u ON u.id = ud.user_id
            WHERE ud.user_id = :user_id
            ORDER BY due ASC
            OFFSET :offset
            LIMIT :limit
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('offset', $offset);
        $stmt->bindValue('limit', $limit);
        $result = $stmt->executeQuery();

        $records = [];
        $rawRecords = $result->fetchAllAssociative($query);
        foreach ($rawRecords as $rawRecord) {
            $records[] = $this->buildEntity($userId, $rawRecord);
        }

        return $records;
    }

    public function getRecordsCount(int $userId): int
    {
        $query = <<<SQL
            SELECT COUNT(*) AS cnt
            FROM "user-dictionary"
            WHERE user_id = :user_id
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('user_id', $userId);
        $result = $stmt->executeQuery();

        $rawResult = $result->fetchAllAssociative($query);
        return (int)$rawResult[0]["cnt"];
    }

    public function findByUserAndRecordId(int $userId, int $recordId): ?UserDictionaryRecordEntity
    {
        $query = <<<SQL
            SELECT
                d."key",
                d.record_id,
                CASE WHEN ud.meaning IS NULL THEN d.meaning ELSE ud.meaning END AS meaning,
                ud.due,
                u.timezone,
                CASE WHEN ud.links IS NULL THEN d.links ELSE ud.links END AS links,
                state
            FROM "user-dictionary" ud
            INNER JOIN dictionary d ON d.record_id = ud.record_id
            INNER JOIN "user" u ON u.id = ud.user_id
            WHERE ud.user_id = :user_id AND ud.record_id = :record_id
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('record_id', $recordId);
        $result = $stmt->executeQuery();

        $records = $result->fetchAllAssociative($query);
        $rawRecord = $records[0] ?? null;
        if (is_null($rawRecord)) {
            return null;
        }

        return $this->buildEntity($userId, $rawRecord);
    }

    public function updateState(UserEntity $user, UserDictionaryRecordEntity $record): void
    {
        $delayDays = $record->getState()->getDelay();
        $timeZone = $user->getTimezone();
        $today = new DateTimeImmutable('NOW', new \DateTimeZone($timeZone));
        // Show Record after 6:00 am user local time
        $due = $today->add(new DateInterval("P{$delayDays}D"))->setTime(6, 0);
        $dueString = $due->format('Y-m-d H:i:sP');

        $query = <<<SQL
            UPDATE "user-dictionary" ud
                SET "state" = ?,
                due = ?
            WHERE user_id = ? AND record_id = ?
            SQL;

        $this->connection->executeStatement(
            $query,
            [
                $record->getState()->getId(),
                $dueString,
                $record->getUserId(),
                $record->getRecordId()
            ]
        );
    }

    public function save(UserDictionaryRecordEntity $record): UserDictionaryRecordEntity
    {
        $links = $record->getLinks();
        $sqlDue = $record->getDue()->format('Y-m-d H:i:sP');

        $query = <<<SQL
            INSERT INTO "user-dictionary"(user_id, record_id, meaning, links, due, state)
            VALUES(
                :user_id,
                :record_id,
                :meaning,
                :sql_links,
                :sql_due,
                :state
            )
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('user_id', $record->getUserId());
        $stmt->bindValue('record_id', $record->getRecordId());
        $stmt->bindValue('meaning', $record->getMeaning());
        if (count($links)) {
            $linksEncoded = json_encode($links);
            $stmt->bindValue('sql_links', $linksEncoded);
        } else {
            $stmt->bindValue('sql_links', null);
        }
        $stmt->bindValue('sql_due', $sqlDue);
        $stmt->bindValue('state', $record->getState()->getId());
        $stmt->executeQuery();

        return $record;
    }

    public function update(int $origRecordId, UserDictionaryRecordEntity $record): UserDictionaryRecordEntity
    {
        $links = $record->getLinks();
        $sqlDue = $record->getDue()->format('Y-m-d H:i:sP');

        $query = <<<SQL
            UPDATE "user-dictionary" SET
                record_id = :record_id,
                meaning = :meaning,
                links = :sql_links,
                due = :due,
                state = :state
            WHERE user_id = :user_id
                AND record_id = :orig_record_id
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('record_id', $record->getRecordId());
        $stmt->bindValue('meaning', $record->getMeaning());
        if (count($links)) {
            $linksEncoded = json_encode($links);
            $stmt->bindValue('sql_links', $linksEncoded);
        } else {
            $stmt->bindValue('sql_links', null);
        }
        $stmt->bindValue('due', $sqlDue);
        $stmt->bindValue('state', $record->getState()->getId());
        $stmt->bindValue('user_id', $record->getUserId());
        $stmt->bindValue('orig_record_id', $origRecordId);
        $stmt->executeQuery();

        return $record;
    }

    public function deleteRecord(int $userId, int $recordId): void
    {
        $query = <<<SQL
            DELETE FROM "user-dictionary"
            WHERE user_id = :user_id AND record_id = :record_id
            SQL;
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('record_id', $recordId);

        $stmt->executeQuery();
    }

    private function buildEntity(int $userId, array $rawData): UserDictionaryRecordEntity
    {
        $links = json_decode($rawData["links"] ?? "[]", true);
        $state = $this->stateFactory->getState($rawData["state"]);
        // Timestamptz is stored as UTC in the Database
        $due = new DateTimeImmutable($rawData["due"], new DateTimeZone("UTC"));
        // We want to show dates in local timezone
        $due = $due->setTimezone(new DateTimeZone($rawData["timezone"]));

        return new UserDictionaryRecordEntity(
            (int)$rawData["record_id"],
            $userId,
            $state,
            $rawData["key"],
            $rawData["meaning"],
            $due,
            $links
        );
    }
}