<?php
/**
 * Food-Saver - Feedback Management
 */
$feedback = $db->query("SELECT * FROM feedback ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-comments"></i> Feedback & Support
    </h1>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedback as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars(substr($item['subject'], 0, 50)); ?>...</td>
                    <td>
                        <span class="badge badge-<?php echo $item['status']; ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-medium">Medium</span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline">View</button>
                        <button class="btn btn-sm btn-outline">Reply</button>
                    </td>
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

.badge-new {
    background: rgba(59, 130, 246, 0.1);
    color: #1e40af;
}

.badge-resolved {
    background: rgba(16, 185, 129, 0.1);
    color: #047857;
}

.badge-pending {
    background: rgba(245, 158, 11, 0.1);
    color: #b45309;
}

.badge-medium {
    background: rgba(245, 158, 11, 0.1);
    color: #b45309;
}
</style>
