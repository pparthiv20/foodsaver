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
                        <?php
                            $status = $user['status'] ?? 'active';
                            $statusClass = 'badge-active';
                            if (in_array($status, ['blocked', 'suspended'], true)) {
                                $statusClass = 'badge-blocked';
                            }
                        ?>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline" type="button"
                            data-details='<?php echo htmlspecialchars(json_encode([
                                "Full Name" => $user["full_name"],
                                "Email" => $user["email"],
                                "Phone" => $user["phone"] ?? "N/A",
                                "Address" => ($user["address"] ?? "N/A") . ", " . ($user["city"] ?? "N/A") . ", " . ($user["state"] ?? "N/A"),
                                "Role" => ucfirst($user["user_type"] ?? "User"),
                                "Join Date" => date("M d, Y H:i", strtotime($user["created_at"])),
                                "Status" => ucfirst($user["status"] ?? "Active")
                            ]), ENT_QUOTES, "UTF-8"); ?>'
                            onclick="viewAnyDetails('User Details', this)">
                            View
                        </button>
                        <button class="btn btn-sm btn-outline" type="button"
                                onclick="handleAction('<?php echo ($user['status'] ?? 'active') === 'blocked' ? 'unblock' : 'block'; ?>', 'user', <?php echo (int)$user['id']; ?>, this)">
                            <?php echo ($user['status'] ?? 'active') === 'blocked' ? 'Unblock' : 'Block'; ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


