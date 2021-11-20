<?php

namespace App\Service;

class ContactService
{

    public function processingContactForm(array $data)
    {
        // Check si tout les champs du form sont remplie
        if (!$data['name'] || !$data['email'] || !$data['message']) {

        }
    }

}