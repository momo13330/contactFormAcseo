<?php

namespace App\Service;


use function PHPUnit\Framework\throwException;

class ContactService
{

    public function processingContactForm(array $data)
    {
        // Check si tout les champs du formulaire de contact sont remplie
        // si non on vas pas plus loin
        if (!$data['name'] || !$data['email'] || !$data['message']) {
            throw new \Exception("Tout les champs du formulaire sont requis");
        }

        //creation de

    }

}