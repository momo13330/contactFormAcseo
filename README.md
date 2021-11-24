# contactFormAcseo

Contact form recrutement Aceso

Language PHP 8
Framwork Symfony 5.3

Commande utile

  création d'un utilisateur
	
        symfony console app:create-user YOUR-EMAIL PASSWORD ROLE
	
ROLE valable [ROLE_USER, ROLE_ADMIN]
        
Route access
  homepage -> public
  contact -> public
  login -> public
  
  contact_list ->  ROLE_ADMIN
 
