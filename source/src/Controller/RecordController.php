<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\RecordEntity;

class RecordController extends AbstractController
{
    function __construct(
        private Connection $connection
    ) {}

    #[Route('/')]
    public function getRecord(): Response
    {
        // TODO: implement Repository/DataMapper design pattern
        $users = $this->connection->fetchAllAssociative("SELECT * FROM \"user\" WHERE email='mail@mail.com'");
        $user = $users[0] ?? null;
        if (is_null($user)) {
            throw new \Exception("User wasn't found");
        }
        $userName = $user["email"] ?? "<unknown>";
        $userId = (int)$user["id"];

        $query = <<<SQL
            SELECT
                d."key",
                d.record_id,
                CASE WHEN ud.meaning IS NULL THEN d.meaning ELSE ud.meaning END AS meaning,
                CASE WHEN ud.links IS NULL THEN d.links ELSE ud.links END AS links,
                state
            FROM "user-dictionary" ud
            INNER JOIN dictionary d ON d.record_id = ud.record_id
            WHERE ud.user_id = {$userId}
            ORDER BY RANDOM()
            LIMIT 1
            SQL;

        $records = $this->connection->fetchAllAssociative($query);
        $rawRecord = $records[0];
        $links = json_decode($rawRecord["links"] ?? "[]", true);
        $record = new RecordEntity($userId, (int)$rawRecord["record_id"], $rawRecord["key"], $rawRecord["meaning"], $links);

        return $this->render('index.html.twig', [
            'username' => $userName,
            "record" => $record,
        ]);
    }
}