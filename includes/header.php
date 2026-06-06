<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<?php
$currentUserRole = $_SESSION['user_role'] ?? null;
$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserName = $_SESSION['user_name'] ?? 'User';
$nameParts = explode(' ', $currentUserName);
$initials = '';
if (!empty($nameParts[0])) $initials = strtoupper(substr($nameParts[0], 0, 1));
if (isset($nameParts[1]) && !empty($nameParts[1])) $initials .= strtoupper(substr($nameParts[1], 0, 1));
if (!$initials) $initials = 'U';
?>
<nav class="navbar navbar-expand-lg bg-white fixed-top">
    <div class="container">
        <a class="navbar-brand text-gradient-primary" href="<?= BASE_URL ?>/index.php">
            <i class="bi bi-hospital"></i> Mindalano Hospital
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <?php if ($currentUserId):
                    $msgLink = BASE_URL . "/modules/$currentUserRole/messages.php";
                    $notifLink = BASE_URL . "/modules/$currentUserRole/notifications.php";
                    $dashLink = BASE_URL . "/modules/$currentUserRole/dashboard.php";

                    // Fetch unread counts from DB if available
                    $unreadMessages = 0;
                    $unreadNotifications = 0;
                    if (isset($pdo)) {
                        try {
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
                            $stmt->execute([$currentUserId]);
                            $unreadMessages = (int)$stmt->fetchColumn();

                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                            $stmt->execute([$currentUserId]);
                            $unreadNotifications = (int)$stmt->fetchColumn();
                        } catch (Exception $e) {}
                    }
                ?>
                    <li class="nav-item">
                        <a class="nav-icon-link" href="<?= $msgLink ?>" title="Messages">
                            <i class="bi bi-chat-dots fs-5"></i>
                            <?php if ($unreadMessages > 0): ?>
                                <span class="badge-dot"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-icon-link" href="<?= $notifLink ?>" title="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($unreadNotifications > 0): ?>
                                <span class="badge-dot"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <span class="nav-avatar"><?= $initials ?></span>
                            <span class="d-none d-lg-inline"><?= htmlspecialchars($currentUserName) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius: 12px; min-width: 220px;">
                            <li><h6 class="dropdown-header"><?= htmlspecialchars($currentUserName) ?></h6></li>
                            <li><a class="dropdown-item" href="<?= $dashLink ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <?php if ($currentUserRole === 'patient'): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/modules/patient/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= $msgLink ?>"><i class="bi bi-chat-dots me-2"></i>Messages <?php if ($unreadMessages > 0): ?><span class="badge bg-danger ms-1"><?= $unreadMessages ?></span><?php endif; ?></a></li>
                            <li><a class="dropdown-item" href="<?= $notifLink ?>"><i class="bi bi-bell me-2"></i>Notifications <?php if ($unreadNotifications > 0): ?><span class="badge bg-danger ms-1"><?= $unreadNotifications ?></span><?php endif; ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/includes/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary text-white px-4 ms-lg-2" href="<?= BASE_URL ?>/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div style="padding-top: 74px;">
