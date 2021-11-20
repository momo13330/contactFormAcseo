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
        $form = $this->createForm(ContactType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dd($data);
            $contactService->processingContactForm($data);
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }


}