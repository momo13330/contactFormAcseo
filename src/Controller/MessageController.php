<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Message;
use App\Service\FileService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    #[Route('admin/contact/messages', name: 'contact_message_list', options: ['expose' => true], methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function listMessageContact(Request $request): Response
    {
        $contactId = $request->get('contactId');
        $messages = [];
        if ($contactId) {
            $entityManager = $this->getDoctrine()->getManager();
            $contact = $entityManager->find(Contact::class, $contactId);
            if ($contact) {
                $messages = $contact->getMessages();
            }
        }

        return $this->render('contact/_listMessageEmbed.html.twig', [
            'messages' => $messages
        ]);
    }

    #[Route('admin/message/view', name: 'message_view', options: ['expose' => true], methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function viewMessageContact(Request $request, FileService $fileService): Response
    {
        $data = [];
        $processed = false;
        $messageId = $request->get('messageId');
        if ($messageId) {
            $entityManager = $this->getDoctrine()->getManager();
            $message = $entityManager->find(Message::class, $messageId);
            if ($message) {
                $processed = $message->getProcessed();
                // récuperation du fichier correspondant au message
                $fileDir = $this->getParameter('kernel.project_dir') . '/Files/Contacts/' . $message->getContact()?->getFolderName();
                $data = $fileService->getJsonFileContent($message, $fileDir);
            }
        }

        return $this->render('contact/_viewMessageEmbed.html.twig', [
            'processed' => $processed,
            'messageId' => $messageId,
            'data' => $data
        ]);
    }

    #[Route('admin/message/{id}/processed', name: 'message_processed', options: ['expose' => true], methods: ['PUT'])]
    #[IsGranted("ROLE_ADMIN")]
    public function processedMessage(Request $request, Message $message): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $message->setProcessed(true);
        $entityManager->persist($message);
        $entityManager->flush();

        return new JsonResponse('Message traité', Response::HTTP_OK);
    }

    #[Route('admin/message/{id}/remove', name: 'message_remove', options: ['expose' => true], methods: ['DELETE'])]
    #[IsGranted("ROLE_ADMIN")]
    public function removeMessage(Request $request, Message $message, FileService $fileService): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($message);

        // si on supprime un message on supprime aussi le fichier correspondant
        $fileDir = $this->getParameter('kernel.project_dir') . '/Files/Contacts/' . $message->getContact()?->getFolderName();
        $filePath = $fileDir.'/'.$message->getFileName();
        // on passe par le service file pour supprimer le fichier
        $result = $fileService->removeFile($filePath);
        // si le message n'a pas était supprimé on arrete le processus est renvoie une erreur
        if(!$result){
            return new JsonResponse('Erreur ! Message non supprimé', Response::HTTP_BAD_REQUEST);
        }
        // si message est supprimé on on flush
        $entityManager->flush();
        return new JsonResponse('Message supprimé', Response::HTTP_OK);
    }
}
