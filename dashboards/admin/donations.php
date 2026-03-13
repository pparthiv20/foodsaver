<?php
/**
 * Food-Saver - Donations Management
 */
$donations = $db->query("
    SELECT d.*, u.full_name
    FROM donations d
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY d.created_at DESC
    LIMIT 20
")->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-donate"></i> Donations
    </h1>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Donor Name</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Transaction ID</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($donations as $donation): ?>
                <tr>
                    <td><?php echo htmlspecialchars($donation['full_name'] ?? 'Anonymous'); ?></td>
                    <td>₹<?php echo number_format($donation['amount'], 2); ?></td>
                    <td><?php echo ucfirst($donation['payment_method']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $donation['status']; ?>">
                            <?php echo ucfirst($donation['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($donation['transaction_id'] ?? 'N/A'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.table {
    width: 100%;
    border-collapse: collapse;
}

.table thead {
    background: var(--gray-50);
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
}

.table th {
    font-weight: 600;
    color: var(--gray-700);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-completed {
    background: rgba(16, 185, 129, 0.1);
    color: #047857;
}

.badge-pending {
    background: rgba(245, 158, 11, 0.1);
    color: #b45309;
}

.badge-failed {
    background: rgba(239, 68, 68, 0.1);
    color: #991b1b;
}
</style>
