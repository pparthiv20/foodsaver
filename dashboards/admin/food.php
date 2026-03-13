<?php
/**
 * Food-Saver - Food Listings Management
 */
$foodListings = $db->query("
    SELECT f.*, r.restaurant_name 
    FROM food_listings f 
    JOIN restaurants r ON f.restaurant_id = r.id 
    ORDER BY f.created_at DESC 
    LIMIT 20
")->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-apple-alt"></i> Food Listings
    </h1>
    <button class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> New Listing
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Food Item</th>
                    <th>Restaurant</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Posted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($foodListings as $food): ?>
                <tr>
                    <td><?php echo htmlspecialchars($food['food_name']); ?></td>
                    <td><?php echo htmlspecialchars($food['restaurant_name']); ?></td>
                    <td><?php echo htmlspecialchars($food['quantity']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $food['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $food['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($food['created_at'])); ?></td>
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

.badge-available {
    background: rgba(16, 185, 129, 0.1);
    color: #047857;
}

.badge-claimed {
    background: rgba(59, 130, 246, 0.1);
    color: #1e40af;
}

.badge-picked_up {
    background: rgba(139, 92, 246, 0.1);
    color: #5b21b6;
}

.badge-delivered {
    background: rgba(34, 197, 94, 0.1);
    color: #166534;
}
</style>
