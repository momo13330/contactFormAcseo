<?php

namespace App\Service;

use App\Entity\Message;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileService
{
    public function saveJsonFile(string $directoryPath, string $fileName, string $dataJson): bool
    {
        try {
            $filesystem = new Filesystem();
            $finalPath = $directoryPath.$fileName;
            if(!$filesystem->exists($directoryPath)){
                $filesystem->mkdir($directoryPath);
            }
            $filesystem->dumpFile($finalPath, $dataJson);
            return $filesystem->exists($finalPath);
        } catch (IOExceptionInterface $exception) {
            return false;
        }
    }

    public function getJsonFileContent(Message $message, $fileDir): array
    {
        $finder = new Finder();
        $finder->files()->in($fileDir);
        $data = [];
        foreach ($finder as $file) {
            if($file->getFilename() === $message->getFileName()){
                $content = $file->getContents();
                $data = json_decode($content, true);
            }
        }
        return $data;
    }

    public function removeFile(string $filePath): bool
    {
        $fileDeleted = false;
        $fileExist = file_exists($filePath);
        if($fileExist){
           $fileDeleted = unlink($filePath);
        }
        return $fileDeleted;
    }

}