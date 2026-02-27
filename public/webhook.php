<?php
/**
 * GitHub Webhook Receiver - JJA DEV LAB
 *
 * Reçoit les push events de GitHub et déclenche le déploiement automatique.
 * Sécurisé par signature HMAC-SHA256 (secret partagé GitHub <-> serveur).
 *
 * Configuration GitHub :
 *   - Payload URL: https://votre-domaine.com/webhook.php
 *   - Content type: application/json
 *   - Secret: (même valeur que WEBHOOK_SECRET dans .env.prod.local)
 *   - Events: Just the push event
 */

// Charger le secret depuis l'env
$envFile = __DIR__ . '/../.env.prod.local';
$secret = null;
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with($line, 'WEBHOOK_SECRET=')) {
            $secret = trim(substr($line, strlen('WEBHOOK_SECRET=')), '"\'');
            break;
        }
    }
}

if (!$secret) {
    http_response_code(500);
    exit('Webhook secret not configured');
}

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Lire le payload
$payload = file_get_contents('php://input');
if (!$payload) {
    http_response_code(400);
    exit('Empty payload');
}

// Vérifier la signature GitHub (HMAC-SHA256)
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!$signature) {
    http_response_code(401);
    exit('Missing signature');
}

$expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(403);
    file_put_contents(
        __DIR__ . '/../var/log/webhook.log',
        sprintf("[%s] REJECTED: Invalid signature from %s\n", date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        FILE_APPEND
    );
    exit('Invalid signature');
}

// Décoder le payload
$data = json_decode($payload, true);
if (!$data) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Vérifier que c'est un push sur master
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/master') {
    http_response_code(200);
    exit('Ignored: not master branch (ref: ' . $ref . ')');
}

// Logger
$logFile = __DIR__ . '/../var/log/webhook.log';
$logEntry = sprintf(
    "[%s] DEPLOY triggered by %s (commit: %s)\n",
    date('Y-m-d H:i:s'),
    $data['pusher']['name'] ?? 'unknown',
    substr($data['after'] ?? '', 0, 7)
);
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Lancer le déploiement en arrière-plan
$deployScript = __DIR__ . '/../scripts/deploy.sh';
if (!file_exists($deployScript)) {
    http_response_code(500);
    exit('Deploy script not found');
}

// Exécution asynchrone pour ne pas bloquer la réponse GitHub
$cmd = sprintf('nohup bash %s >> %s 2>&1 &', escapeshellarg($deployScript), escapeshellarg(__DIR__ . '/../var/log/deploy.log'));
exec($cmd);

http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'message' => 'Deployment triggered',
    'commit' => substr($data['after'] ?? '', 0, 7),
    'timestamp' => date('c'),
]);
