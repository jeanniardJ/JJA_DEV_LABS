<?php
/**
 * Pont PHP pour le CRON OVH
 * Permet d'exécuter le worker Messenger de Symfony via un fichier PHP.
 */

// 1. Définition du chemin vers la console Symfony
$console = __DIR__ . '/bin/console';

// 2. Configuration du worker
// --limit=50 : Traite 50 messages puis s'arrête proprement.
// --time-limit=300 : S'arrête après 5 minutes (300s) pour éviter d'être tué par OVH.
// --no-debug : Optimise les performances en désactivant le mode debug.
$command = "php $console messenger:consume async --limit=50 --time-limit=300 --no-debug";

echo "Démarrage du worker Messenger JJA DEV LAB...
";
echo "Commande : $command

";

// 3. Exécution de la commande
// passthru affiche le résultat en temps réel dans les logs OVH
passthru($command);

echo "
Worker terminé proprement.";
