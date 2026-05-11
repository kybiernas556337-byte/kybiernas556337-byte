<?php
// dashboard.php - Dashboard & READ (display all transactions)

require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin(); // Session protection

$pdo    = getConnection();
$userId = getCurrentUserId();

// Summary stats — Prepared Statements
$stmt = $pdo->prepare("SELECT
    SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense,
    COUNT(*) AS total_records
    FROM transactions WHERE user_id = ?");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

$balance      = ($stats['total_income'] ?? 0) - ($stats['total_expense'] ?? 0);
$totalIncome  = $stats['total_income'] ?? 0;
$totalExpense = $stats['total_expense'] ?? 0;
$totalRecords = $stats['total_records'] ?? 0;

// Fetch all transactions for current user
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC, created_at DESC");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll();

// Handle delete flash message
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Expense Tracker</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-page">

<!-- Navbar -->
<nav class="navbar">
    <div class="nav-brand">
        <span>💰</span> Expense Tracker
    </div>
    <div class="nav-user">
        <span>👤 <?= htmlspecialchars(getCurrentUsername()) ?></span>
        <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>
    </div>
</nav>

<div class="app-container">

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-balance">
            <div class="stat-label">Net Balance</div>
            <div class="stat-value <?= $balance >= 0 ? 'positive' : 'negative' ?>">
                ₱<?= number_format($balance, 2) ?>
            </div>
        </div>
        <div class="stat-card stat-income">
            <div class="stat-label">Total Income</div>
            <div class="stat-value positive">₱<?= number_format($totalIncome, 2) ?></div>
        </div>
        <div class="stat-card stat-expense">
            <div class="stat-label">Total Expenses</div>
            <div class="stat-value negative">₱<?= number_format($totalExpense, 2) ?></div>
        </div>
        <div class="stat-card stat-count">
            <div class="stat-label">Transactions</div>
            <div class="stat-value"><?= $totalRecords ?></div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="section-header">
        <h2>Transaction History</h2>
        <a href="create.php" class="btn btn-primary">+ Add Transaction</a>
    </div>

    <!-- Flash Message -->
    <?php if ($flash): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <!-- Transactions Table (READ) -->
    <div class="table-wrapper">
        <?php if (empty($transactions)): ?>
            <div class="empty-state">
                <p>No transactions yet. <a href="create.php">Add your first one!</a></p>
            </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $i => $t): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($t['transaction_date']) ?></td>
                    <td><?= htmlspecialchars($t['title']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($t['category']) ?></span></td>
                    <td>
                        <span class="type-badge type-<?= $t['type'] ?>">
                            <?= ucfirst($t['type']) ?>
                        </span>
                    </td>
                    <td class="amount <?= $t['type'] === 'income' ? 'positive' : 'negative' ?>">
                        <?= $t['type'] === 'income' ? '+' : '-' ?>₱<?= number_format($t['amount'], 2) ?>
                    </td>
                    <td class="actions">
                        <a href="update.php?id=<?= $t['id'] ?>" class="btn btn-edit btn-sm">Edit</a>
                        <a href="delete.php?id=<?= $t['id'] ?>"
                           class="btn btn-delete btn-sm"
                           onclick="return confirm('Are you sure you want to delete this transaction?')">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
