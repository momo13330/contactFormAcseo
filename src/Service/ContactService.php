<?php

namespace App\Service;


use App\Entity\Contact;
use App\Entity\Message;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use function PHPUnit\Framework\directoryExists;
use function PHPUnit\Framework\throwException;


class ContactService
{

    public function __construct(
        protected ContactRepository $contactRepository,
        protected EntityManagerInterface $entityManager,
        protected ParameterBagInterface $parameterBag,
        protected FileService $fileService
    )
    {
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public function processingContactForm(array $data)
    {
        // Check si tout les champs du formulaire de contact sont remplie
        // si non on vas pas plus loin
        if (!$data['name'] || !$data['email'] || !$data['message']) {
            throw new \Exception("Tout les champs du formulaire sont requis");
        }

        // on verifie que c'est bien un email valide (type mail)
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
            throw new \Exception("L'email renseigné n'est pas correct");
        }

        //check si le contact à déjà emis une demande
        $contact = $this->contactRepository->findOneBy(['email'=> $data['email']]);

        //si le contact n'a jamais fait de demande on le créer
        if(!$contact){
            $contact = $this->contactFactory($data['email']);
        }

        //puis on creer l'entité message
        $message = $this->messageFactory($contact, $data);
        $contact->addMessage($message);

        // on cree le fichier json correspondant au message
        $messageSavedInFile = $this->createMessageJsonFile($contact, $message, $data);
        if(!$messageSavedInFile){
            throw new \Exception("Une erreur est survenue votre message n'a pas pu être envoyé");
        }

        $this->entityManager->flush();
    }

    public function contactFactory(string $email): Contact
    {
        // on creer un nouveau contact
        $contact = new Contact();
        $contact->setEmail($email);
        // on gerene le nom du repertoire pour cet utilisateur
        $folderName = $this->generateFolderName($email);
        $contact->setFolderName($folderName);

        $this->entityManager->persist($contact);

        return $contact;
    }

    private function generateFolderName(string $email): string
    {
        // recuperation des 3 premiere lettre de l'email pour garder un ordre dans les dossier
        $threeFirstLettre = substr($email, 0, 3);
        $emailHash = md5($email . uniqid("", true));
        $folderName = $threeFirstLettre.'_'.$emailHash;

        // avant de renvoyé le nom on verifie que le nom repertoire n'est pas déjà attribué a un contact
        $folderNameExist = $this->contactRepository->findOneBy(['folderName' => $folderName]);
        if($folderNameExist){
            // on refait un hash sur l'email
            $emailHash2 = md5($email . uniqid("", true));
            $folderName = $threeFirstLettre.'_'.$emailHash2;
        }
        return $folderName;
    }


    public function messageFactory(Contact $contact, array $data)
    {
        $message = new Message();
        $message->setContact($contact);
        $message->setDate(new \DateTime());
        $fileName = (new \DateTime())->format('Ymdhis');
        $message->setFileName($fileName.'.json');

        $this->entityManager->persist($message);

        return $message;
    }

    public function createMessageJsonFile(Contact $contact, Message $message, array $data): bool
    {
        // on encode l'array data en json
        $dataJson = json_encode($data, JSON_THROW_ON_ERROR);
        $kernelDir = $this->parameterBag->get('kernel.project_dir');
        $directoryPath = $kernelDir.'/Files/Contacts/'.$contact->getFolderName();

        return $this->fileService->saveJsonFile($directoryPath, $message->getFileName(), $dataJson);
    }



}