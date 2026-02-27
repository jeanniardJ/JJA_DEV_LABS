<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:setup-2fa',
    description: 'Génère le QR Code 2FA pour un utilisateur ou réinitialise son secret.',
)]
class UserSetup2faCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private GoogleAuthenticatorInterface $totpAuthenticator,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, "L'email de l'utilisateur");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error(sprintf('Utilisateur %s non trouvé.', $email));
            return Command::FAILURE;
        }

        // Generate secret if not exists
        if (!$user->getGoogleAuthenticatorSecret()) {
            $user->setGoogleAuthenticatorSecret($this->totpAuthenticator->generateSecret());
            $this->entityManager->flush();
            $io->success('Nouveau secret 2FA généré.');
        }

        $qrCodeUrl = $this->totpAuthenticator->getQRContent($user);
        
        $io->section('Configuration 2FA');
        $io->writeln('Scannez ce contenu dans votre application Google Authenticator :');
        $io->note($qrCodeUrl);
        
        $io->info('Ou utilisez cette URL pour voir le QR Code (Générateur externe) :');
        $io->writeln(sprintf('https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=%s', urlencode($qrCodeUrl)));

        return Command::SUCCESS;
    }
}
