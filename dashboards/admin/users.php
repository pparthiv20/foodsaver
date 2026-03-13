<?php
/**
 * Food-Saver - Users Management
 */
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i> Users Management
    </h1>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Join Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                    <td><?php echo ucfirst($user['user_type'] ?? 'user'); ?></td>
                    <td>
                        <span class="badge badge-active">Active</span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline">View</button>
                        <button class="btn btn-sm btn-outline">Edit</button>
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

.badge-active {
    background: rgba(16, 185, 129, 0.1);
    color: #047857;
}
</style>
