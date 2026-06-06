<?php
require_once 'config/app.php';
require_once 'config/database.php';

$error = '';

$maxAttempts = 5;
$blockDuration = 900;

if (isset($_SESSION['login_block_until']) && time() < $_SESSION['login_block_until']) {
    $remaining = ceil(($_SESSION['login_block_until'] - time()) / 60);
    $error = "Too many login attempts. Try again in $remaining minute(s).";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']) ?: $user['username'];
            unset($_SESSION['login_attempts'], $_SESSION['login_block_until']);
            header('Location: modules/' . $user['role'] . '/dashboard.php');
            exit;
        } else {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] >= $maxAttempts) {
                $_SESSION['login_block_until'] = time() + $blockDuration;
                $_SESSION['login_attempts'] = 0;
            }
            $error = 'Invalid email/username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="card shadow-lg border-0">
            <div class="card-body">
                <div class="auth-brand">
                    <div class="brand-icon">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <h3>Welcome Back</h3>
                    <p>Sign in to your account to continue</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email or Username</label>
                        <input type="text" name="login" class="form-control form-control-lg" placeholder="Enter your email or username" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="loginPassword" class="form-control form-control-lg" placeholder="Enter your password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('loginPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0 text-muted">
                        Don't have an account? <a href="register.php" class="fw-semibold text-primary">Create one here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
