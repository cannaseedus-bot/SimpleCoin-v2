<?php
/**
 * ASX Wallet Generator and Encryptor
 *
 * Generates a 256-bit private key, derives a pseudo address, encrypts the
 * payload with AES-256-GCM, and persists the encrypted JSON locally.
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $password = trim($_POST['password'] ?? '');
    if ($password === '') {
        throw new InvalidArgumentException('Password is required to encrypt the wallet.');
    }

    $wallet = generateWallet();
    $encrypted = encryptWallet($wallet, $password);
    persistEncryptedWallet($encrypted, $wallet['address']);

    echo json_encode([
        'wallet_address' => $wallet['address'],
        'status' => 'created'
    ]);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create wallet', 'message' => $error->getMessage()]);
}

/**
 * @return array{privateKey: string, address: string}
 * @throws Exception
 */
function generateWallet(): array
{
    $privateKey = bin2hex(random_bytes(32));
    $address = '0x' . substr(hash('sha256', $privateKey), 0, 40);

    return [
        'privateKey' => $privateKey,
        'address' => $address,
    ];
}

/**
 * @param array{privateKey: string, address: string} $wallet
 * @return array{iv: string, salt: string, tag: string, data: string}
 */
function encryptWallet(array $wallet, string $password): array
{
    $payload = json_encode($wallet, JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        throw new RuntimeException('Unable to encode wallet payload.');
    }

    $iv = random_bytes(12);
    $salt = random_bytes(16);
    $key = hash_pbkdf2('sha256', $password, $salt, 100_000, 32, true);

    $tag = '';
    $ciphertext = openssl_encrypt($payload, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($ciphertext === false || $tag === '') {
        throw new RuntimeException('Wallet encryption failed.');
    }

    return [
        'iv' => bin2hex($iv),
        'salt' => bin2hex($salt),
        'tag' => bin2hex($tag),
        'data' => bin2hex($ciphertext),
    ];
}

/**
 * Persist the encrypted wallet JSON in a local (non-public) directory.
 */
function persistEncryptedWallet(array $encryptedWallet, string $address): void
{
    $walletDir = __DIR__ . '/wallets';
    if (!is_dir($walletDir) && !mkdir($walletDir, 0750, true) && !is_dir($walletDir)) {
        throw new RuntimeException('Unable to create wallet storage directory.');
    }

    $outputPath = $walletDir . '/' . $address . '.json';
    $json = json_encode($encryptedWallet, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Unable to encode encrypted wallet for storage.');
    }

    if (file_put_contents($outputPath, $json, LOCK_EX) === false) {
        throw new RuntimeException('Unable to write encrypted wallet to disk.');
    }
}

