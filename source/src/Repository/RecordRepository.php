<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\RecordEntity;
use App\Entity\UserEntity;
use Doctrine\DBAL\Connection;
use App\State\StateFactory;
use DateTimeImmutable;
use DateInterval;

class RecordRepository
{
    public function __construct(
        private Connection $connection,
        private StateFactory $stateFactory,
    ) {}

    public function findRecord(int $userId): ?RecordEntity
    {
        $query = <<<SQL
            SELECT
                d."key",
                d.record_id,
                CASE WHEN ud.meaning IS NULL THEN d.meaning ELSE ud.meaning END AS meaning,
                ud.due,
                CASE WHEN ud.links IS NULL THEN d.links ELSE ud.links END AS links,
                state
            FROM "user-dictionary" ud
            INNER JOIN dictionary d ON d.record_id = ud.record_id
            WHERE ud.user_id = {$userId} AND ud.due < NOW()
            ORDER BY ud.due ASC
            LIMIT 1
            SQL;

        $records = $this->connection->fetchAllAssociative($query);
        $rawRecord = $records[0] ?? null;
        if (is_null($rawRecord)) {
            return null;
        }

        return $this->buildEntity($userId, $rawRecord);
    }

    /**
     * @return RecordEntity[]
     */
    public function getRecords(int $userId): array
    {
        $query = <<<SQL
            SELECT
                d."key",
                d.record_id,
                CASE WHEN ud.meaning IS NULL THEN d.meaning ELSE ud.meaning END AS meaning,
                ud.due,
                CASE WHEN ud.links IS NULL THEN d.links ELSE ud.links END AS links,
                state
            FROM "user-dictionary" ud
            INNER JOIN dictionary d ON d.record_id = ud.record_id
            WHERE ud.user_id = {$userId}
            ORDER BY due ASC
            LIMIT 10
            SQL;

        $records = [];
        $rawRecords = $this->connection->fetchAllAssociative($query);
        foreach ($rawRecords as $rawRecord) {
            $records[] = $this->buildEntity($userId, $rawRecord);
        }

        return $records;
    }

    public function getByUserAndRecordId(int $userId, int $recordId): RecordEntity
    {
        $query = <<<SQL
            SELECT
                d."key",
                d.record_id,
                CASE WHEN ud.meaning IS NULL THEN d.meaning ELSE ud.meaning END AS meaning,
                ud.due,
                CASE WHEN ud.links IS NULL THEN d.links ELSE ud.links END AS links,
                state
            FROM "user-dictionary" ud
            INNER JOIN dictionary d ON d.record_id = ud.record_id
            WHERE ud.user_id = {$userId} AND ud.record_id = {$recordId}
            SQL;

        $records = $this->connection->fetchAllAssociative($query);
        $rawRecord = $records[0];
        if (is_null($rawRecord)) {
            throw new \Exception("Record wasn't found");
        }

        return $this->buildEntity($userId, $rawRecord);
    }

    public function updateState(UserEntity $user, RecordEntity $record): void
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

    private function buildEntity(int $userId, array $rawData): RecordEntity
    {
        $links = json_decode($rawData["links"] ?? "[]", true);
        $state = $this->stateFactory->getState($rawData["state"]);

        return new RecordEntity(
            (int)$rawData["record_id"],
            $userId,
            $state,
            $rawData["key"],
            $rawData["meaning"],
            new DateTimeImmutable($rawData["due"], new \DateTimeZone('UTC')),
            $links
        );
    }
}