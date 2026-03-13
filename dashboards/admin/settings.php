<?php
/**
 * Food-Saver - Admin Settings Page
 * System configuration and preferences
 */
?>

<!-- Settings Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-cog"></i> System Settings
    </h1>
    <p class="page-subtitle">Configure platform settings and preferences</p>
</div>

<!-- Settings Tabs -->
<div class="tabs mb-4">
    <button class="tab-btn active" onclick="switchTab('general')">General Settings</button>
    <button class="tab-btn" onclick="switchTab('email')">Email Configuration</button>
    <button class="tab-btn" onclick="switchTab('security')">Security Settings</button>
    <button class="tab-btn" onclick="switchTab('api')">API Settings</button>
</div>

<!-- General Settings Tab -->
<div id="general" class="tab-content active">
    <div class="card">
        <div class="card-header">
            <h3>General Settings</h3>
        </div>
        <div class="card-body">
            <form class="form-grid">
                <div class="form-group">
                    <label class="form-label">Platform Name</label>
                    <input type="text" class="form-input" value="Food-Saver" placeholder="Enter platform name">
                </div>
                <div class="form-group">
                    <label class="form-label">Platform Tagline</label>
                    <input type="text" class="form-input" value="Reduce Waste, Save Lives" placeholder="Enter tagline">
                </div>
                <div class="form-group">
                    <label class="form-label">Support Email</label>
                    <input type="email" class="form-input" value="support@foodsaver.com" placeholder="support@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Support Phone</label>
                    <input type="tel" class="form-input" value="+91 1234567890" placeholder="+91 XXXXXXXXXX">
                </div>
                <div class="form-group">
                    <label class="form-label">Site Timezone</label>
                    <select class="form-input">
                        <option>UTC+5:30 (India Standard Time)</option>
                        <option>UTC+0:00 (GMT)</option>
                        <option>UTC-5:00 (EST)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Language</label>
                    <select class="form-input">
                        <option>English</option>
                        <option>Hindi</option>
                        <option>Tamil</option>
                    </select>
                </div>
            </form>
            <div class="form-actions mt-4">
                <button class="btn btn-primary" onclick="saveSetting('general')">
                    <i class="fas fa-save"></i> Save Settings
                </button>
                <button class="btn btn-outline">Reset to Defaults</button>
            </div>
        </div>
    </div>
</div>

<!-- Email Configuration Tab -->
<div id="email" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3>Email Configuration</h3>
            <p class="card-subtitle">Configure SMTP and email settings</p>
        </div>
        <div class="card-body">
            <form class="form-grid">
                <div class="form-group">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" class="form-input" value="smtp.gmail.com" placeholder="smtp.example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" class="form-input" value="587" placeholder="587">
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Username</label>
                    <input type="text" class="form-input" value="noreply@foodsaver.com" placeholder="username@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Password</label>
                    <input type="password" class="form-input" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label class="form-label">From Email</label>
                    <input type="email" class="form-input" value="noreply@foodsaver.com">
                </div>
                <div class="form-group">
                    <label class="form-label">From Name</label>
                    <input type="text" class="form-input" value="Food-Saver">
                </div>
            </form>
            <div class="form-actions mt-4">
                <button class="btn btn-primary" onclick="testEmail()">
                    <i class="fas fa-envelope"></i> Send Test Email
                </button>
                <button class="btn btn-primary" onclick="saveSetting('email')">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Security Settings Tab -->
<div id="security" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3>Security Settings</h3>
            <p class="card-subtitle">Configure security and privacy options</p>
        </div>
        <div class="card-body">
            <div class="settings-list">
                <div class="setting-item">
                    <div class="setting-info">
                        <h4 class="setting-title">Two-Factor Authentication</h4>
                        <p class="setting-description">Require 2FA for admin accounts</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <h4 class="setting-title">Session Timeout</h4>
                        <p class="setting-description">Auto-logout after inactivity (minutes)</p>
                    </div>
                    <div class="setting-control">
                        <input type="number" class="form-input" style="width: 100px;" value="30">
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <h4 class="setting-title">IP Whitelist</h4>
                        <p class="setting-description">Restrict access to specific IP addresses</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle">
                            <input type="checkbox">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <h4 class="setting-title">Data Encryption</h4>
                        <p class="setting-description">Encrypt sensitive data in database</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <h4 class="setting-title">HTTPS Only</h4>
                        <p class="setting-description">Force HTTPS for all connections</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-actions mt-4">
                <button class="btn btn-primary" onclick="saveSetting('security')">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- API Settings Tab -->
<div id="api" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3>API Settings</h3>
            <p class="card-subtitle">Configure API keys and access controls</p>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">API Key</label>
                <div class="input-group">
                    <input type="text" class="form-input" value="sk_live_abc123def456" readonly>
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>

            <div class="form-group mt-3">
                <label class="form-label">API Rate Limit (requests/hour)</label>
                <input type="number" class="form-input" value="1000">
            </div>

            <div class="form-group mt-3">
                <label class="form-label">Allowed API Endpoints</label>
                <div class="checkbox-group">
                    <label class="checkbox">
                        <input type="checkbox" checked> GET /api/restaurants
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" checked> GET /api/food-listings
                    </label>
                    <label class="checkbox">
                        <input type="checkbox"> POST /api/donations
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" checked> GET /api/stats
                    </label>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button class="btn btn-primary" onclick="regenerateAPIKey()">
                    <i class="fas fa-refresh"></i> Regenerate Key
                </button>
                <button class="btn btn-primary" onclick="saveSetting('api')">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.tabs {
    display: flex;
    gap: 1rem;
    border-bottom: 2px solid var(--gray-200);
    overflow-x: auto;
}

.tab-btn {
    padding: 1rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--gray-600);
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition-base);
    white-space: nowrap;
}

.tab-btn:hover {
    color: var(--primary-600);
}

.tab-btn.active {
    color: var(--primary-600);
    border-bottom-color: var(--primary-600);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 300ms ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 500;
    color: var(--gray-700);
    font-size: 0.95rem;
}

.form-input,
.form-input select {
    padding: 0.75rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: 0.95rem;
    transition: all var(--transition-fast);
}

.form-input:focus,
.form-input select:focus {
    outline: none;
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
}

.settings-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
}

.setting-info {
    flex: 1;
}

.setting-title {
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 0.25rem;
}

.setting-description {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.setting-control {
    flex-shrink: 0;
}

.toggle {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--gray-300);
    border-radius: 24px;
    transition: all 0.3s ease;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.toggle input:checked + .toggle-slider {
    background-color: var(--primary-500);
}

.toggle input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.input-group {
    display: flex;
    gap: 0.5rem;
}

.input-group .form-input {
    flex: 1;
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.95rem;
}

.checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.card-subtitle {
    font-size: 0.875rem;
    color: var(--gray-500);
    margin-top: 0.25rem;
    font-weight: 400;
}

.mt-3 {
    margin-top: 1rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .setting-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .setting-control {
        width: 100%;
    }

    .input-group {
        flex-direction: column;
    }
}
</style>

<script>
// Tab switching functionality
function switchTab(tabName) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
        event.target.classList.add('active');
    }
}

// Save settings function
function saveSetting(section) {
    showNotification(`${section} settings saved successfully!`, 'success');
}

// Test email function
function testEmail() {
    showNotification('Sending test email...', 'info');
    setTimeout(() => {
        showNotification('Test email sent successfully!', 'success');
    }, 2000);
}

// Regenerate API key
function regenerateAPIKey() {
    if (confirm('Are you sure? This will invalidate the current API key.')) {
        showNotification('API key regenerated successfully!', 'success');
    }
}
</script>
