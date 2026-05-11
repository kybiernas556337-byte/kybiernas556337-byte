<?php
// delete.php - DELETE Operation (Remove record with confirmation)

require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin(); // Session protection

$pdo    = getConnection();
$userId = getCurrentUserId();
$id     = intval($_GET['id'] ?? 0);

if (!$id) {
    header("Location: dashboard.php");
    exit();
}

// Verify ownership and delete — Prepared Statement
$stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
if ($stmt->execute([$id, $userId]) && $stmt->rowCount() > 0) {
    $_SESSION['flash'] = "Transaction deleted successfully.";
} else {
    $_SESSION['flash'] = "Could not delete transaction.";
}

header("Location: dashboard.php");
exit();
?>
