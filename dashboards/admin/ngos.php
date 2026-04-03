<?php
/**
 * Food-Saver - NGOs Management
 */
$ngos = $db->query("SELECT * FROM ngos ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-hands-helping"></i> NGOs Management
    </h1>
    <button class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add NGO
    </button>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>NGO Name</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Join Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ngos as $ngo): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ngo['ngo_name']); ?></td>
                    <td><?php echo htmlspecialchars($ngo['contact_email'] ?? 'N/A'); ?></td>
                    <td>
                        <?php
                            $status = $ngo['status'];
                            $statusClass = 'badge-' . $status;
                            if (in_array($status, ['blocked', 'suspended'], true)) {
                                $statusClass = 'badge-blocked';
                            }
                        ?>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($ngo['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline" type="button"
                            data-details='<?php echo htmlspecialchars(json_encode([
                                "NGO Name" => $ngo["ngo_name"],
                                "Contact Person" => $ngo["contact_person"] ?? "N/A",
                                "Email" => $ngo["email_contact"] ?? "N/A",
                                "Phone" => $ngo["phone"] ?? "N/A",
                                "Location" => ($ngo["city"] ?? "N/A") . ", " . ($ngo["state"] ?? "N/A"),
                                "Service Areas" => $ngo["service_areas"] ?? "N/A",
                                "Registration No" => $ngo["registration_number"] ?? "N/A",
                                "Join Date" => date("M d, Y H:i", strtotime($ngo["created_at"])),
                                "Status" => ucfirst($ngo["status"] ?? "Active")
                            ]), ENT_QUOTES, "UTF-8"); ?>'
                            onclick="viewAnyDetails('NGO Details', this)">
                            View
                        </button>
                        <button class="btn btn-sm btn-outline" type="button"
                                onclick="handleAction('<?php echo $ngo['status'] === 'blocked' ? 'unblock' : 'block'; ?>', 'ngo', <?php echo (int)$ngo['id']; ?>, this)">
                            <?php echo $ngo['status'] === 'blocked' ? 'Unblock' : 'Block'; ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


