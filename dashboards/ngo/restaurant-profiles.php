<?php
/**
 * Food-Saver - NGO: View Restaurant Profiles
 * Browse and view restaurant profiles for NGOs
 */
$restaurants = $db->query("
    SELECT * FROM restaurants 
    WHERE status = 'approved' 
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-utensils"></i> Restaurant Profiles
    </h1>
    <p class="page-subtitle">Discover restaurants donating surplus food</p>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="flex gap-3 flex-wrap">
            <input type="search" class="form-control" placeholder="Search restaurants..." id="searchRestaurant">
            <select class="form-control" style="width: auto;" id="filterCity">
                <option value="">All Cities</option>
                <option value="Mumbai">Mumbai</option>
                <option value="Delhi">Delhi</option>
                <option value="Bangalore">Bangalore</option>
                <option value="Hyderabad">Hyderabad</option>
                <option value="Pune">Pune</option>
                <option value="Chennai">Chennai</option>
            </select>
            <select class="form-control" style="width: auto;" id="filterCuisine">
                <option value="">All Cuisines</option>
                <option value="Indian">Indian</option>
                <option value="Chinese">Chinese</option>
                <option value="Continental">Continental</option>
                <option value="Vegetarian">Vegetarian</option>
                <option value="Multi-cuisine">Multi-cuisine</option>
            </select>
        </div>
    </div>
</div>

<!-- Restaurant Grid -->
<div class="grid grid-3">
    <?php foreach ($restaurants as $restaurant): ?>
    <div class="card restaurant-card scroll-animate">
        <div class="card-body">
            <!-- Header -->
            <div class="flex justify-between items-start mb-3">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-utensils text-orange fa-lg"></i>
                        <h3 class="text-lg font-bold"><?php echo htmlspecialchars($restaurant['restaurant_name']); ?></h3>
                    </div>
                    <p class="text-gray text-sm">
                        <i class="fas fa-map-marker-alt"></i> 
                        <?php echo htmlspecialchars($restaurant['city'] ?? 'N/A') . ', ' . htmlspecialchars($restaurant['state'] ?? 'N/A'); ?>
                    </p>
                </div>
                <span class="badge badge-success">
                    <i class="fas fa-check-circle"></i> Active
                </span>
            </div>

            <!-- Cuisine & Rating -->
            <div class="flex gap-2 mb-3">
                <span class="cuisine-tag">
                    <i class="fas fa-leaf"></i> <?php echo htmlspecialchars($restaurant['cuisine_type'] ?? 'Multi-cuisine'); ?>
                </span>
                <span class="rating-tag">
                    <i class="fas fa-star text-warning"></i> <?php echo number_format(rand(35, 50) / 10, 1); ?>
                </span>
            </div>

            <!-- Description -->
            <p class="text-sm text-gray mb-3">
                <?php echo htmlspecialchars(substr($restaurant['description'] ?? 'Committed to reducing food waste', 0, 100)); ?>...
            </p>

            <!-- Contact Info -->
            <div class="contact-info mb-3">
                <div class="contact-item">
                    <span class="label">Contact:</span>
                    <span class="value"><?php echo htmlspecialchars($restaurant['contact_person'] ?? 'N/A'); ?></span>
                </div>
                <div class="contact-item">
                    <span class="label">Phone:</span>
                    <span class="value"><?php echo htmlspecialchars($restaurant['phone'] ?? 'N/A'); ?></span>
                </div>
                <div class="contact-item">
                    <span class="label">Email:</span>
                    <span class="value text-primary">
                        <a href="mailto:<?php echo htmlspecialchars($restaurant['email']); ?>">
                            <?php echo htmlspecialchars($restaurant['email']); ?>
                        </a>
                    </span>
                </div>
                <div class="contact-item">
                    <span class="label">Hours:</span>
                    <span class="value"><?php echo htmlspecialchars($restaurant['operating_hours'] ?? '10 AM - 11 PM'); ?></span>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid mb-4">
                <?php 
                    $foodsPosted = $db->query("SELECT COUNT(*) FROM food_listings WHERE restaurant_id = " . $restaurant['id'])->fetchColumn();
                    $foodsDelivered = $db->query("SELECT COUNT(*) FROM food_listings WHERE restaurant_id = " . $restaurant['id'] . " AND status = 'delivered'")->fetchColumn();
                ?>
                <div class="stat">
                    <div class="stat-value"><?php echo number_format($foodsPosted); ?></div>
                    <div class="stat-label">Foods Posted</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo number_format($foodsDelivered); ?></div>
                    <div class="stat-label">Delivered</div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
                <button class="btn btn-primary btn-sm flex-1" onclick="contactRestaurant('<?php echo htmlspecialchars($restaurant['restaurant_name']); ?>', '<?php echo htmlspecialchars($restaurant['email']); ?>')">
                    <i class="fas fa-envelope"></i> Contact
                </button>
                <button class="btn btn-outline btn-sm flex-1" 
                    data-details='<?php echo htmlspecialchars(json_encode([
                        "Restaurant Name" => $restaurant["restaurant_name"],
                        "Location" => ($restaurant["city"] ?? "N/A") . ", " . ($restaurant["state"] ?? "N/A"),
                        "Cuisine" => $restaurant["cuisine_type"] ?? "Multi-cuisine",
                        "Contact Person" => $restaurant["contact_person"] ?? "N/A",
                        "Phone" => $restaurant["phone"] ?? "N/A",
                        "Email" => $restaurant["email"] ?? "N/A",
                        "Operating Hours" => $restaurant["operating_hours"] ?? "10 AM - 11 PM",
                        "Description" => $restaurant["description"] ?? "N/A"
                    ]), ENT_QUOTES, "UTF-8"); ?>'
                    onclick="viewAnyDetails('Restaurant Details', this)">
                    <i class="fas fa-eye"></i> View
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($restaurants)): ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
        <i class="fas fa-inbox fa-3x text-gray-300 mb-2"></i>
        <p class="text-gray">No verified restaurants available at the moment.</p>
    </div>
    <?php endif; ?>
</div>



<script>
function contactRestaurant(restaurantName, email) {
    window.location.href = `mailto:${email}?subject=Food Donation Inquiry from NGO`;
}

function viewRestaurantDetails(restaurantId) {
    showNotification('Viewing restaurant details...', 'info');
}

// Search functionality
document.getElementById('searchRestaurant')?.addEventListener('input', function(e) {
    const searchText = e.target.value.toLowerCase();
    filterRestaurants(searchText, '', '');
});

// Filter by city
document.getElementById('filterCity')?.addEventListener('change', function(e) {
    const city = e.target.value.toLowerCase();
    const cuisine = document.getElementById('filterCuisine').value.toLowerCase();
    filterRestaurants('', city, cuisine);
});

// Filter by cuisine
document.getElementById('filterCuisine')?.addEventListener('change', function(e) {
    const cuisine = e.target.value.toLowerCase();
    const city = document.getElementById('filterCity').value.toLowerCase();
    filterRestaurants('', city, cuisine);
});

function filterRestaurants(search, city, cuisine) {
    document.querySelectorAll('.restaurant-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        const matchesSearch = !search || text.includes(search);
        const matchesCity = !city || text.includes(city);
        const matchesCuisine = !cuisine || text.includes(cuisine);
        
        card.style.display = (matchesSearch && matchesCity && matchesCuisine) ? '' : 'none';
    });
}
</script>
