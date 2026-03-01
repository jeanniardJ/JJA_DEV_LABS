<?php

namespace App\Command;

use App\Entity\AppointmentAvailability;
use App\Service\ConfigurationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-config',
    description: 'Initialise les variables de configuration par défaut dans la base de données.',
)]
class AppInitConfigCommand extends Command
{
    public function __construct(
        private ConfigurationService $configService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1. Config Variables
        $defaultConfigs = [
            [
                'key' => 'admin_email',
                'value' => 'jonathanjeanniard@sfr.fr',
                'desc' => 'Email utilisé pour l\'envoi des réponses aux leads.'
            ],
            [
                'key' => 'gemini_system_prompt',
                'value' => "Tu es l'assistant IA de Jonathan Jeanniard, expert en développement Symfony et cybersécurité. Ta mission est d'aider Jonas à répondre à ses leads de manière technique, professionnelle et concise. Réponds toujours en français, avec un ton empathique mais expert. Ne propose pas de code inutile, concentre-toi sur la valeur métier et la sécurité.",
                'desc' => 'Prompt système envoyé à l\'IA Gemini pour formater les réponses.'
            ],
            [
                'key' => 'site_banner_message',
                'value' => 'BIENVENUE_DANS_LE_LABORATOIRE_D_AUDIT_CYBER',
                'desc' => 'Message défilant sur la page d\'accueil.'
            ],
            [
                'key' => 'github_url',
                'value' => 'https://github.com/jeanniardJ',
                'desc' => 'Lien vers le profil GitHub affiché dans le footer.'
            ],
        ];

        $io->section('Initialisation des variables');
        foreach ($defaultConfigs as $config) {
            $this->configService->set($config['key'], $config['value'], $config['desc']);
            $io->writeln(sprintf('Variable <info>%s</info> initialisée.', $config['key']));
        }

        // 2. Default Availabilities (Mon-Fri, 9-17)
        $io->section('Initialisation des disponibilités RDV');
        
        $this->entityManager->createQuery('DELETE FROM App\Entity\AppointmentAvailability')->execute();

        for ($day = 1; $day <= 5; $day++) {
            $avail = new AppointmentAvailability();
            $avail->setDayOfWeek($day);
            $avail->setStartTime(new \DateTimeImmutable('09:00'));
            $avail->setEndTime(new \DateTimeImmutable('17:00'));
            $avail->setSlotDuration(30);
            $this->entityManager->persist($avail);
        }
        $this->entityManager->flush();
        $io->writeln('Plages horaires : Lundi au Vendredi, 09:00 - 17:00.');

        $io->success('Configuration du noyau initialisée avec succès.');

        return Command::SUCCESS;
    }
}
