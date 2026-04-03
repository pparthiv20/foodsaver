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
                        <?php
                            $status = $restaurant['status'];
                            $statusClass = 'badge-' . $status;
                            if ($status === 'blocked') {
                                $statusClass = 'badge-blocked';
                            }
                        ?>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($restaurant['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline" type="button"
                            data-details='<?php echo htmlspecialchars(json_encode([
                                "Restaurant Name" => $restaurant["restaurant_name"],
                                "Contact Person" => $restaurant["contact_person"] ?? "N/A",
                                "Email" => $restaurant["email"] ?? "N/A",
                                "Phone" => $restaurant["phone"] ?? "N/A",
                                "Location" => ($restaurant["city"] ?? "N/A") . ", " . ($restaurant["state"] ?? "N/A"),
                                "Cuisine" => $restaurant["cuisine_type"] ?? "N/A",
                                "Hours" => $restaurant["operating_hours"] ?? "N/A",
                                "Join Date" => date("M d, Y H:i", strtotime($restaurant["created_at"])),
                                "Status" => ucfirst($restaurant["status"] ?? "Active")
                            ]), ENT_QUOTES, "UTF-8"); ?>'
                            onclick="viewAnyDetails('Restaurant Details', this)">
                            View
                        </button>
                        <button class="btn btn-sm btn-outline" type="button"
                                onclick="handleAction('<?php echo $restaurant['status'] === 'blocked' ? 'unblock' : 'block'; ?>', 'restaurant', <?php echo (int)$restaurant['id']; ?>, this)">
                            <?php echo $restaurant['status'] === 'blocked' ? 'Unblock' : 'Block'; ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


