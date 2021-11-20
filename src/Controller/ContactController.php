<?php

namespace App\Controller;


use App\Form\Contact\ClientType;
use App\Form\Contact\ContactType;
use App\Service\ContactService;
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
            }

            return $this->render('contact/index.html.twig', [
                'form' => $form->createView()
            ]);

        } catch (\Exception $exception) {
            $this->addFlash('warning', $exception->getMessage());
            return $this->redirectToRoute('contact');
        }
    }


}