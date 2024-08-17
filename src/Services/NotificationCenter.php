<?php

namespace Alnv\CatalogManagerBundle\Services;

use Contao\File;
use Contao\Files;
use Contao\FilesModel;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\NotificationCenter as NC;
use Terminal42\NotificationCenterBundle\Parcel\StampCollection;
use Terminal42\NotificationCenterBundle\Receipt\ReceiptCollection;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class NotificationCenter
{
    public function __construct(readonly private NC $notificationCenter)
    {
    }

    public function sendNotification($intNotificationId, $arrTokens = []): ReceiptCollection
    {
        return $this->notificationCenter->sendNotification($intNotificationId, $arrTokens);
    }

    public function getNotificationsForNotificationType(string $typeName): array
    {
        return $this->notificationCenter->getNotificationsForNotificationType($typeName);
    }

    public function getBulkyGoodsStorage(): BulkyItemStorage
    {
        return $this->notificationCenter->getBulkyGoodsStorage();
    }

    public function sendNotificationWithStamps(int $id, StampCollection $stamps): ReceiptCollection
    {
        return $this->notificationCenter->sendNotificationWithStamps($id, $stamps);
    }

    public function createBasicStampsForNotification(int $id, TokenCollection|array $tokens, string|null $locale = null): StampCollection
    {
        return $this->notificationCenter->createBasicStampsForNotification($id, $tokens, $locale);
    }

    public function getVoucher($strUuid): string
    {

        $objFile = FilesModel::findByUuid($strUuid);
        if (!$objFile) {
            return '';
        }

        $dirFile = new File($objFile->path);
        $objStream = Files::getInstance()->fopen($objFile->path, 'r+');

        return $this->getBulkyGoodsStorage()->store(FileItem::fromStream($objStream, $dirFile->name, $dirFile->mime, $dirFile->size));
    }
}