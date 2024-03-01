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
        $query = "SELECT * FROM \"user\" WHERE email = :email";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('email', $email);
        $result = $stmt->executeQuery();
        $users = $result->fetchAllAssociative($query);

        $rawUser = $users[0] ?? null;
        if (is_null($rawUser)) {
            return null;
        }

        return $this->buildEntity($rawUser);
    }

    public function getUserById(int $id): UserEntity
    {
        $query = "SELECT * FROM \"user\" WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue('id', $id);
        $result = $stmt->executeQuery();
        $users = $result->fetchAllAssociative($query);

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