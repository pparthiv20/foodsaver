<?php
/**
 * Food-Saver - Restaurants Management
 */
$restaurants = $db->query("SELECT * FROM restaurants ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-utensils"></i> Restaurants Management
    </h1>
    <button class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add Restaurant
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Restaurant Name</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Join Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $restaurant): ?>
                <tr>
                    <td><?php echo htmlspecialchars($restaurant['restaurant_name']); ?></td>
                    <td><?php echo htmlspecialchars($restaurant['address'] ?? 'N/A'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $restaurant['status']; ?>">
                            <?php echo ucfirst($restaurant['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($restaurant['created_at'])); ?></td>
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

.badge-approved {
    background: rgba(16, 185, 129, 0.1);
    color: #047857;
}

.badge-pending {
    background: rgba(245, 158, 11, 0.1);
    color: #b45309;
}

.badge-rejected {
    background: rgba(239, 68, 68, 0.1);
    color: #991b1b;
}
</style>
