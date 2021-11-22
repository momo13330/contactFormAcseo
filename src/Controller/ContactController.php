<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Message;
use App\Form\Contact\ContactType;
use App\Service\ContactService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, ContactService $contactService): Response
    {
        try {
            $form = $this->createForm(ContactType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                $contactService->processingContactForm($form->getData());

                $this->addFlash('success', 'Votre message à bien été transmit');
                return $this->redirectToRoute('contact');
            }

            return $this->render('contact/index.html.twig', [
                'form' => $form->createView()
            ]);

        } catch (\Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());
            return $this->redirectToRoute('contact');
        }
    }

    #[Route('admin/contact/list', name: 'contact_list')]
    #[IsGranted("ROLE_ADMIN")]
    public function listContact(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $contacts = $entityManager->getRepository(Contact::class)->findAllOrdered();

        return $this->render('contact/list.html.twig', [
            'contacts' => $contacts
        ]);
    }

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
    public function viewMessageContact(Request $request): Response
    {
        $messageId = $request->get('messageId');
        $data = [];
        if ($messageId) {
            $entityManager = $this->getDoctrine()->getManager();
            $message = $entityManager->find(Message::class, $messageId);
            if ($message) {
                // récuperation du fichier correspondant au message
                $fileDir = $this->getParameter('kernel.project_dir') . '/Files/Contacts/' . $message->getContact()?->getFolderName();
                $finder = new Finder();
                $finder->files()->in($fileDir);
                foreach ($finder as $file) {
                    if($file->getFilename() === $message->getFileName()){
                        $content = $file->getContents();
                        $data = json_decode($content, true);
                    }
                }
            }
        }

        return $this->render('contact/_viewMessageEmbed.html.twig', [
            'id' => $messageId,
            'data' => $data
        ]);
    }

}
