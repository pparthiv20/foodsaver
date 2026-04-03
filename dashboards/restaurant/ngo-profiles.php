<?php
/**
 * Food-Saver - Restaurant: View NGO Profiles
 * Browse and view NGO profiles for restaurants
 */
$ngos = $db->query("
    SELECT * FROM ngos 
    WHERE status = 'approved' 
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-hands-helping"></i> NGO Profiles
    </h1>
    <p class="page-subtitle">Connect with NGOs that distribute your donations</p>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="flex gap-3 flex-wrap">
            <input type="search" class="form-control" placeholder="Search NGOs..." id="searchNGO">
            <select class="form-control" style="width: auto;" id="filterCity">
                <option value="">All Cities</option>
                <option value="Mumbai">Mumbai</option>
                <option value="Delhi">Delhi</option>
                <option value="Bangalore">Bangalore</option>
                <option value="Hyderabad">Hyderabad</option>
                <option value="Pune">Pune</option>
                <option value="Chennai">Chennai</option>
            </select>
        </div>
    </div>
</div>

<!-- NGO Grid -->
<div class="grid grid-3">
    <?php foreach ($ngos as $ngo): ?>
    <div class="card ngo-card scroll-animate">
        <div class="card-body">
            <!-- Header -->
            <div class="flex justify-between items-start mb-3">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-heart text-danger fa-lg"></i>
                        <h3 class="text-lg font-bold"><?php echo htmlspecialchars($ngo['ngo_name']); ?></h3>
                    </div>
                    <p class="text-gray text-sm">
                        <i class="fas fa-map-marker-alt"></i> 
                        <?php echo htmlspecialchars($ngo['city'] ?? 'N/A') . ', ' . htmlspecialchars($ngo['state'] ?? 'N/A'); ?>
                    </p>
                </div>
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Verified
                </span>
            </div>

            <!-- Description -->
            <p class="text-sm text-gray mb-3">
                <?php echo htmlspecialchars(substr($ngo['description'] ?? 'Dedicated NGO working to reduce food waste', 0, 100)); ?>...
            </p>

            <!-- Contact Info -->
            <div class="contact-info mb-3">
                <div class="contact-item">
                    <span class="label">Contact Person:</span>
                    <span class="value"><?php echo htmlspecialchars($ngo['contact_person'] ?? 'N/A'); ?></span>
                </div>
                <div class="contact-item">
                    <span class="label">Phone:</span>
                    <span class="value"><?php echo htmlspecialchars($ngo['phone'] ?? 'N/A'); ?></span>
                </div>
                <div class="contact-item">
                    <span class="label">Email:</span>
                    <span class="value text-primary">
                        <a href="mailto:<?php echo htmlspecialchars($ngo['email_contact']); ?>">
                            <?php echo htmlspecialchars($ngo['email_contact']); ?>
                        </a>
                    </span>
                </div>
                <div class="contact-item">
                    <span class="label">Service Areas:</span>
                    <span class="value"><?php echo htmlspecialchars($ngo['service_areas'] ?? 'Multiple areas'); ?></span>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid mb-4">
                <?php 
                    $peopleServed = $db->query("SELECT COALESCE(SUM(people_served), 0) FROM food_listings WHERE status = 'delivered' AND claimed_by = " . $ngo['id'])->fetchColumn();
                    $foodsClaimed = $db->query("SELECT COUNT(*) FROM food_listings WHERE claimed_by = " . $ngo['id'])->fetchColumn();
                ?>
                <div class="stat">
                    <div class="stat-value"><?php echo number_format($peopleServed); ?></div>
                    <div class="stat-label">People Served</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo number_format($foodsClaimed); ?></div>
                    <div class="stat-label">Foods Claimed</div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
                <button class="btn btn-primary btn-sm flex-1" onclick="contactNGO('<?php echo htmlspecialchars($ngo['ngo_name']); ?>', '<?php echo htmlspecialchars($ngo['email_contact']); ?>')">
                    <i class="fas fa-envelope"></i> Contact
                </button>
                <button class="btn btn-outline btn-sm flex-1" 
                    data-details='<?php echo htmlspecialchars(json_encode([
                        "NGO Name" => $ngo["ngo_name"],
                        "Location" => ($ngo["city"] ?? "N/A") . ", " . ($ngo["state"] ?? "N/A"),
                        "Service Areas" => $ngo["service_areas"] ?? "Multiple areas",
                        "Contact Person" => $ngo["contact_person"] ?? "N/A",
                        "Phone" => $ngo["phone"] ?? "N/A",
                        "Email" => $ngo["email_contact"] ?? "N/A",
                        "Registration No" => $ngo["registration_number"] ?? "N/A",
                        "Description" => $ngo["description"] ?? "N/A"
                    ]), ENT_QUOTES, "UTF-8"); ?>'
                    onclick="viewAnyDetails('NGO Details', this)">
                    <i class="fas fa-eye"></i> View
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($ngos)): ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
        <i class="fas fa-inbox fa-3x text-gray-300 mb-2"></i>
        <p class="text-gray">No verified NGOs available at the moment.</p>
    </div>
    <?php endif; ?>
</div>



<script>
function contactNGO(ngoName, email) {
    window.location.href = `mailto:${email}?subject=Food Donation Inquiry from Restaurant`;
}

function viewNGODetails(ngoId) {
    showNotification('Viewing ' + event.target.closest('.ngo-card').querySelector('h3').textContent, 'info');
}

// Search functionality
document.getElementById('searchNGO')?.addEventListener('input', function(e) {
    const searchText = e.target.value.toLowerCase();
    document.querySelectorAll('.ngo-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchText) ? '' : 'none';
    });
});

// Filter by city
document.getElementById('filterCity')?.addEventListener('change', function(e) {
    const city = e.target.value.toLowerCase();
    document.querySelectorAll('.ngo-card').forEach(card => {
        if (!city) {
            card.style.display = '';
        } else {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(city) ? '' : 'none';
        }
    });
});
</script>
