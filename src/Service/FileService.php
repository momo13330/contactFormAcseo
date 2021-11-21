<?php

namespace App\Service;

use PHPUnit\Util\Exception;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use function PHPUnit\Framework\throwException;

class FileService
{
    public function saveJsonFile(string $directoryPath, string $fileName, string $dataJson): bool
    {
        try {
            $filesystem = new Filesystem();
            $finalPath = $directoryPath.'/'.$fileName;
            if(!$filesystem->exists($directoryPath)){
                $filesystem->mkdir($directoryPath, 755);
            }
            $filesystem->dumpFile($finalPath, $dataJson);
            return $filesystem->exists($finalPath);
        } catch (IOExceptionInterface $exception) {
            return false;
        }
    }
}