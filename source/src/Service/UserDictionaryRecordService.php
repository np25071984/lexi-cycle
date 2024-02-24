<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\DictionaryRecordEntity;
use App\Repository\UserDictionaryRecordRepository;
use App\Repository\DictionaryRecordRepository;
use App\Entity\UserDictionaryRecordEntity;

class UserDictionaryRecordService
{
    public function __construct(
        private DictionaryRecordRepository $dictionaryRecordRepository,
        private UserDictionaryRecordRepository $userDictionaryRecordRepository,
    ) {}

    public function  createRecord(UserDictionaryRecordEntity $userDictionaryRecord): void {
        // TODO: Apply UnitOfWork to wrap it into a transaction
        $key = $userDictionaryRecord->getKey();

        $dictionaryRecord = $this->dictionaryRecordRepository->findByKey($key);
        if (is_null($dictionaryRecord)) {
            $record = new DictionaryRecordEntity(
                $userDictionaryRecord->getRecordId(),
                $key,
                $userDictionaryRecord->getMeaning(),
                $userDictionaryRecord->getLinks(),
            );
            $dictionaryRecord = $this->dictionaryRecordRepository->save($record);
        }

        $realUserDictionaryRecordEntity = new UserDictionaryRecordEntity(
            $dictionaryRecord->getRecordId(),
            $userDictionaryRecord->getUserId(),
            $userDictionaryRecord->getState(),
            $userDictionaryRecord->getKey(),
            $userDictionaryRecord->getMeaning(),
            $userDictionaryRecord->getDue(),
            $userDictionaryRecord->getLinks(),
        );

        $userDictionaryRecord = $this->userDictionaryRecordRepository->findByUserAndRecordId(
            $realUserDictionaryRecordEntity->getUserId(),
            $realUserDictionaryRecordEntity->getRecordId()
        );

        if (!is_null($userDictionaryRecord)) {
            // TODO: already exists
            return;
        }

        $this->userDictionaryRecordRepository->save($realUserDictionaryRecordEntity);
    }
}