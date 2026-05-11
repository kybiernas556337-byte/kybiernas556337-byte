<?php
// create.php - CREATE Operation (Insert using form)

require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin(); // Session protection

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $amount   = trim($_POST['amount'] ?? '');
    $type     = $_POST['type'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $date     = $_POST['transaction_date'] ?? '';
    $userId   = getCurrentUserId();

    // Input validation
    if (empty($title) || empty($amount) || empty($type) || empty($category) || empty($date)) {
        $error = "All required fields must be filled.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Amount must be a positive number (no negative values).";
    } elseif (!in_array($type, ['income', 'expense'])) {
        $error = "Invalid transaction type.";
    } else {
        $pdo = getConnection();

        // INSERT using Prepared Statement
        $stmt = $pdo->prepare(
            "INSERT INTO transactions (user_id, title, amount, type, category, description, transaction_date)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt->execute([$userId, $title, $amount, $type, $category, $desc, $date])) {
            $_SESSION['flash'] = "Transaction added successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Failed to add transaction. Please try again.";
        }
    }
}

$categories = ['Food', 'Transport', 'Bills', 'Health', 'Entertainment', 'Shopping', 'Salary', 'Freelance', 'Investment', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Transaction — Expense Tracker</title>
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
        <h2>Add New Transaction</h2>
    </div>

    <div class="form-card">
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="create.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Title <span class="req">*</span></label>
                    <input type="text" id="title" name="title"
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                           placeholder="e.g. Grocery shopping" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount (₱) <span class="req">*</span></label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                           value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>"
                           placeholder="0.00" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type <span class="req">*</span></label>
                    <select id="type" name="type" required>
                        <option value="">-- Select Type --</option>
                        <option value="income"  <?= ($_POST['type'] ?? '') === 'income'  ? 'selected' : '' ?>>Income</option>
                        <option value="expense" <?= ($_POST['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Expense</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category">Category <span class="req">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="transaction_date">Date <span class="req">*</span></label>
                <input type="date" id="transaction_date" name="transaction_date"
                       value="<?= htmlspecialchars($_POST['transaction_date'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Additional notes..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Transaction</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
