<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('doctor');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/messages.php';

$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();
if (!$doctor) { die('Doctor profile not found.'); }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['receiver_id'])) {
    $receiverId = (int)$_POST['receiver_id'];
    $message = trim($_POST['message']);
    $apptId = !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null;

    if ($message) {
        sendMessage($pdo, $userId, $receiverId, $message, $apptId);
        $success = 'Message sent!';
    } else {
        $error = 'Please write a message.';
    }
}

$conversations = getConversations($pdo, $userId);
$otherUserId = (int)($_GET['user'] ?? 0);
$messages = [];
$otherUser = null;
if ($otherUserId) {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, role FROM users WHERE id = ?");
    $stmt->execute([$otherUserId]);
    $otherUser = $stmt->fetch();
    if ($otherUser) {
        $messages = getMessages($pdo, $userId, $otherUserId);
        markMessagesRead($pdo, $userId, $otherUserId);
    }
}
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-chat-dots"></i> Conversations</h5></div>
            <div class="card-body p-0">
                <?php if (empty($conversations)): ?>
                    <p class="text-muted text-center p-3 mb-0">No conversations yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($conversations as $c): ?>
                        <a href="?user=<?= $c['other_user_id'] ?>" class="list-group-item list-group-item-action <?= ($otherUserId == $c['other_user_id']) ? 'active' : '' ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <strong><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                                <small><?= date('M d', strtotime($c['last_time'])) ?></small>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars(mb_substr($c['last_message'], 0, 50)) . (mb_strlen($c['last_message']) > 50 ? '...' : '') ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php if ($otherUser): ?>
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($otherUser['first_name'] . ' ' . $otherUser['last_name']) ?>
                    <?php else: ?>
                        <i class="bi bi-chat-dots"></i> Messages
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body" style="min-height: 400px; max-height: 500px; overflow-y: auto;">
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                <?php if (!$otherUser): ?>
                    <p class="text-muted text-center mt-5">Select a conversation to view messages.</p>
                <?php elseif (empty($messages)): ?>
                    <p class="text-muted text-center mt-5">No messages in this thread.</p>
                <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                    <div class="mb-3 <?= $m['sender_id'] === $userId ? 'text-end' : '' ?>">
                        <div class="d-inline-block p-3 rounded-3 <?= $m['sender_id'] === $userId ? 'bg-primary text-white' : 'bg-light' ?>" style="max-width: 80%;">
                            <p class="mb-1"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
                            <small class="<?= $m['sender_id'] === $userId ? 'text-white-50' : 'text-muted' ?>"><?= date('M d, h:i A', strtotime($m['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if ($otherUser): ?>
            <div class="card-footer">
                <form method="POST">
                    <input type="hidden" name="receiver_id" value="<?= $otherUserId ?>">
                    <div class="input-group">
                        <input type="text" name="message" class="form-control" placeholder="Type a reply..." required>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
