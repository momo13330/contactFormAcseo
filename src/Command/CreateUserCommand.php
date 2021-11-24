<?php

namespace App\Command;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService
    )
    {
        parent::__construct();
    }


    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-user';

    protected function configure()
    {
        parent::configure();
        // configure an argument
        $this->addArgument('email', InputArgument::REQUIRED, 'Email');
        $this->addArgument('password', InputArgument::REQUIRED, 'Password');
        $this->addArgument('role', InputArgument::REQUIRED, 'role');

        $this->setDescription("Création d'un utilisateur");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($user) {
            $output->writeln("<comment> WARNING </comment>: L'utilisateur {$email} exist déjà.");
        } else {

            $params = [];
            $params['plainPassword'] = $password;
            $params['email'] = $email;
            $params['roles'] = [$role];

            // test Email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $output->writeln("<error>Email invalid</error>");
                $helper = $this->getHelper('question');
                $question = new Question("Merci de renseigner un email valid : ", false);
                $email = $helper->ask($input, $output, $question);

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $output->writeln("<error>Email invalid : création impossible</error>");
                    return Command::INVALID;
                }
                $params['roles'] = $email;
            }

            // test Role
            $arrayRole = ['ROLE_USER', 'ROLE_ADMIN'];
            if (!in_array($role, $arrayRole)
            ) {
                $output->writeln("<error>Role invalid ! </error> Merci de choisir parmi un des rôles suivants. ");
                $helper = $this->getHelper('question');
                $question = new ChoiceQuestion(
                    'Role valide : (defaut : ROLE_USER ) ',
                    $arrayRole,
                    0
                );
                $question->setErrorMessage('La reponse est invalide.');
                $params['roles'] = $helper->ask($input, $output, $question);
            }

            $output->writeln("création de l'utilisateur {$email}");
            $user = $this->userService->factory($params);

            if ($user->getId()) {
                $output->writeln("<info>SUCCESS</info> : L'utilisateur {$email} a bien été créé");
            } else {
                $output->writeln("<error>ERROR </error> : Une erreur est survenue , l'utilisateur n'a pas été créé");
                return Command::FAILURE;
            }
        }
        return Command::SUCCESS;
    }
}