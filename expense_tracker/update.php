<?php
// update.php - UPDATE Operation (Edit record)

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

// Fetch existing transaction — Prepared Statement (only owner can edit)
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header("Location: dashboard.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $amount   = trim($_POST['amount'] ?? '');
    $type     = $_POST['type'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $date     = $_POST['transaction_date'] ?? '';

    // Input validation
    if (empty($title) || empty($amount) || empty($type) || empty($category) || empty($date)) {
        $error = "All required fields must be filled.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a positive number (no negative values).";
    } elseif (!in_array($type, ['income', 'expense'])) {
        $error = "Invalid transaction type.";
    } else {
        // UPDATE using Prepared Statement
        $stmt = $pdo->prepare(
            "UPDATE transactions SET title=?, amount=?, type=?, category=?, description=?, transaction_date=?
             WHERE id=? AND user_id=?"
        );

        if ($stmt->execute([$title, $amount, $type, $category, $desc, $date, $id, $userId])) {
            $_SESSION['flash'] = "Transaction updated successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Failed to update. Please try again.";
        }
    }

    // Update local data for re-display
    $transaction = array_merge($transaction, [
        'title' => $title, 'amount' => $amount, 'type' => $type,
        'category' => $category, 'description' => $desc, 'transaction_date' => $date
    ]);
}

$categories = ['Food', 'Transport', 'Bills', 'Health', 'Entertainment', 'Shopping', 'Salary', 'Freelance', 'Investment', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Transaction — Expense Tracker</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-page">

<nav class="navbar">
    <div class="nav-brand"><a href="dashboard.php">💰 Expense Tracker</a></div>
    <div class="nav-user">
        <span>👤 <?= htmlspecialchars(getCurrentUsername()) ?></span>
        <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
    </div>
</nav>

<div class="app-container">
    <div class="form-page-header">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h2>Edit Transaction</h2>
    </div>

    <div class="form-card">
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="update.php?id=<?= $id ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Title <span class="req">*</span></label>
                    <input type="text" id="title" name="title"
                           value="<?= htmlspecialchars($transaction['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount (₱) <span class="req">*</span></label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                           value="<?= htmlspecialchars($transaction['amount']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type <span class="req">*</span></label>
                    <select id="type" name="type" required>
                        <option value="income"  <?= $transaction['type'] === 'income'  ? 'selected' : '' ?>>Income</option>
                        <option value="expense" <?= $transaction['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category">Category <span class="req">*</span></label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= $transaction['category'] === $cat ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="transaction_date">Date <span class="req">*</span></label>
                <input type="date" id="transaction_date" name="transaction_date"
                       value="<?= htmlspecialchars($transaction['transaction_date']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" rows="3"><?= htmlspecialchars($transaction['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
