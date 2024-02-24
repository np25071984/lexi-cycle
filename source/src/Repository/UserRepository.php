<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserEntity;
use Doctrine\DBAL\Connection;

class UserRepository
{
    public function __construct(
        private Connection $connection
    ) {}

    public function findUserByEmail(string $email): ?UserEntity
    {
        $users = $this->connection->fetchAllAssociative(sprintf("SELECT * FROM \"user\" WHERE email='%s'", $email));
        $rawUser = $users[0] ?? null;
        if (is_null($rawUser)) {
            return null;
        }

        return $this->buildEntity($rawUser);
    }

    public function getUserById(int $id): UserEntity
    {
        $users = $this->connection->fetchAllAssociative(sprintf("SELECT * FROM \"user\" WHERE id=%d", $id));
        // $users = $this->connection->fetchAllAssociative("SELECT * FROM \"user\"");
        $rawUser = $users[0] ?? null;
        if (is_null($rawUser)) {
            throw new \Exception("User wasn't found");
        }

        return $this->buildEntity($rawUser);
    }

    private function buildEntity(array $rawData): UserEntity
    {
        return new UserEntity(
            (int)$rawData["id"],
            $rawData["email"],
            $rawData["password"],
            $rawData["firstname"],
            $rawData["lastname"],
            $rawData["timezone"]
        );
    }
}