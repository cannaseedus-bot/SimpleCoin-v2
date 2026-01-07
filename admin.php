<?php
session_start();
require_once __DIR__ . '/db.php';

$userEmail = $_SESSION['user_email'] ?? null;
if (!$userEmail) {
    http_response_code(401);
    echo 'Not logged in.';
    exit;
}

$pdo = getPdo();
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $userEmail]);
$user = $stmt->fetch();

if (!$user || (int)$user['is_admin'] !== 1) {
    http_response_code(403);
    echo 'Unauthorized.';
    exit;
}

$users = $pdo->query('SELECT email, name, metamask_address, coinbase_address, is_admin FROM users')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASX Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">ASX Admin Dashboard</h1>
        <div class="text-right">
            <div class="small">Signed in as</div>
            <strong><?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Link Wallets</div>
        <div class="card-body">
            <button class="btn btn-outline-primary mr-2 mb-2" id="link-metamask">Link MetaMask Wallet</button>
            <button class="btn btn-outline-secondary mb-2" id="link-coinbase">Link Coinbase Wallet</button>
            <div class="mt-3">
                <div class="small text-muted">Linked MetaMask:</div>
                <div id="metamask-display" class="font-monospace">
                    <?php echo htmlspecialchars($user['metamask_address'] ?? 'Not linked', ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="small text-muted mt-2">Linked Coinbase:</div>
                <div id="coinbase-display" class="font-monospace">
                    <?php echo htmlspecialchars($user['coinbase_address'] ?? 'Not linked', ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
            <p class="text-muted mb-0 small mt-3">Requires browser wallet extensions (MetaMask/Coinbase). Addresses are stored on the user record.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Mint NFT Reward</div>
        <div class="card-body">
            <form action="nft_mint.php" method="post" class="form-inline">
                <div class="form-group mr-2 mb-2">
                    <label for="wallet_address" class="sr-only">Wallet Address</label>
                    <input type="text" class="form-control" id="wallet_address" name="wallet_address" placeholder="User Wallet Address" required>
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="tier" class="sr-only">Tier</label>
                    <select class="form-control" id="tier" name="tier">
                        <option value="bronze">Bronze ($1)</option>
                        <option value="silver">Silver ($5)</option>
                        <option value="gold">Gold ($20)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">Mint NFT</button>
            </form>
            <p class="text-muted mb-0 small">Metadata files are read from <code>nft_rewards/</code> and mint logs are written to <code>mint_logs/</code>.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Users</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>MetaMask</th>
                            <th>Coinbase</th>
                            <th>Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['metamask_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['coinbase_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (int)$row['is_admin'] === 1 ? 'Yes' : 'No'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    function linkWallet(type) {
        if (typeof window.ethereum === 'undefined') {
            alert('No browser wallet detected. Please install MetaMask or Coinbase Wallet.');
            return;
        }

        window.ethereum.request({method: 'eth_requestAccounts'})
            .then(function (accounts) {
                if (!accounts || !accounts[0]) {
                    throw new Error('No wallet address returned.');
                }
                var address = accounts[0];

                var formData = new URLSearchParams();
                formData.append('type', type);
                formData.append('address', address);

                return fetch('link_wallet.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: formData.toString()
                }).then(function (response) {
                    if (!response.ok) {
                        return response.text().then(function (text) {
                            throw new Error(text || 'Failed to link wallet.');
                        });
                    }
                    return response.text();
                }).then(function (message) {
                    alert(message);
                    if (type === 'metamask') {
                        document.getElementById('metamask-display').textContent = address;
                    } else {
                        document.getElementById('coinbase-display').textContent = address;
                    }
                });
            })
            .catch(function (error) {
                console.error('Wallet link failed', error);
                alert(error.message || 'Failed to link wallet.');
            });
    }

    document.getElementById('link-metamask').addEventListener('click', function () {
        linkWallet('metamask');
    });
    document.getElementById('link-coinbase').addEventListener('click', function () {
        linkWallet('coinbase');
    });
</script>
</body>
</html>
