<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Message;
use App\Form\Contact\ContactType;
use App\Service\ContactService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

}
