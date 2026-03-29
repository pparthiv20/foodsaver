/**
 * Food-Saver - Micro Interactions & Celebration Effects
 * Adds delightful animations and user feedback throughout the application
 */

// ==================== THANK YOU MODAL FOR DONATIONS ====================
function showDonationThankYouModal(amount, transactionId = null) {
    const modal = document.createElement('div');
    modal.className = 'donation-thank-you-modal';
    modal.id = 'donationThankYouModal';
    
    const modalContent = document.createElement('div');
    modalContent.className = 'donation-thank-you-content';
    
    const html = `
        <div class="donation-thank-you-header">
            <button type="button" class="modal-close-btn" onclick="closeDonationThankYouModal()">&times;</button>
        </div>
        <div class="donation-thank-you-body">
            <div class="thank-you-celebration">
                <div class="celebration-icon">
                    <i class="fas fa-heart pulse"></i>
                </div>
            </div>
            
            <h1 class="thank-you-title">Thank You for Your Generosity! 🙏</h1>
            
            <div class="donation-details">
                <div class="detail-row">
                    <span class="detail-label">You've Donated:</span>
                    <span class="detail-value">₹${amount.toLocaleString()}</span>
                </div>
                ${transactionId ? `
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value detail-id">${transactionId}</span>
                </div>
                ` : ''}
            </div>
            
            <div class="thank-you-message">
                <p>Your contribution will help us fight food waste and feed those in need. Together, we're making a real difference in our community!</p>
            </div>
            
            <div class="thank-you-impact">
                <h3>Your Impact:</h3>
                <div class="impact-stats">
                    <div class="impact-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <div class="impact-number">5-10</div>
                            <div class="impact-text">People Fed</div>
                        </div>
                    </div>
                    <div class="impact-item">
                        <i class="fas fa-leaf"></i>
                        <div>
                            <div class="impact-number">2.5kg</div>
                            <div class="impact-text">Waste Reduced</div>
                        </div>
                    </div>
                    <div class="impact-item">
                        <i class="fas fa-heartbeat"></i>
                        <div>
                            <div class="impact-number">2500</div>
                            <div class="impact-text">Calories Shared</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="thank-you-actions">
                <button type="button" class="btn btn-primary" onclick="closeDonationThankYouModal()">
                    Continue
                </button>
                <button type="button" class="btn btn-outline" onclick="shareDonation('${amount}')">
                    <i class="fas fa-share-alt"></i> Share Your Impact
                </button>
            </div>
            
            <div class="thank-you-newsletter">
                <p>Stay updated on the impact you're making:</p>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                    <input type="email" class="form-control" placeholder="Email address" id="thankyouEmail" style="flex: 1; padding: 0.75rem; border: 1px solid #E5E7EB; border-radius: 6px;">
                    <button type="button" class="btn btn-primary" onclick="subscribeToUpdates()">Subscribe</button>
                </div>
            </div>
        </div>
    `;
    
    modalContent.innerHTML = html;
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Trigger celebration animation
    setTimeout(() => {
        triggerConfetti();
    }, 300);
    
    // Trigger pulse animation
    const pulseElement = modal.querySelector('.pulse');
    if (pulseElement) {
        pulseElement.style.animation = 'pulse 1.5s ease-in-out infinite';
    }
}

function closeDonationThankYouModal() {
    const modal = document.getElementById('donationThankYouModal');
    if (modal) {
        modal.classList.add('fade-out');
        setTimeout(() => modal.remove(), 300);
    }
}

function shareDonation(amount) {
    const text = `I just donated ₹${amount} through Food-Saver to help fight food waste and feed those in need. Join me in making a difference! #FoodSaver #FightFoodWaste`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Food-Saver Donation',
            text: text
        });
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Share message copied to clipboard!', 'success');
        });
    }
}

function subscribeToUpdates() {
    const email = document.getElementById('thankyouEmail').value;
    if (!email) {
        showNotification('Please enter your email address', 'error');
        return;
    }
    
    // Here you would typically make an API call to subscribe the user
    showNotification('Thank you for subscribing! You'll receive updates on your impact.', 'success');
    closeDonationThankYouModal();
}

// ==================== CONFETTI CELEBRATION ====================
function triggerConfetti() {
    const confettiCount = 50;
    const colors = ['#10B981', '#F59E0B', '#EF4444', '#3B82F6', '#8B5CF6'];
    
    for (let i = 0; i < confettiCount; i++) {
        createConfetti(colors[Math.floor(Math.random() * colors.length)]);
    }
}

function createConfetti(color) {
    const confetti = document.createElement('div');
    confetti.className = 'confetti';
    confetti.style.backgroundColor = color;
    confetti.style.left = Math.random() * 100 + '%';
    confetti.style.top = '-10px';
    
    document.body.appendChild(confetti);
    
    const duration = 2000 + Math.random() * 1000;
    const xMovement = (Math.random() - 0.5) * 200;
    const rotation = Math.random() * 360;
    
    confetti.animate([
        { 
            transform: `translateX(0) translateY(0) rotate(0deg)`,
            opacity: 1
        },
        { 
            transform: `translateX(${xMovement}px) translateY(${window.innerHeight + 20}px) rotate(${rotation}deg)`,
            opacity: 0
        }
    ], {
        duration: duration,
        easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
        fill: 'forwards'
    });
    
    setTimeout(() => confetti.remove(), duration);
}

// ==================== BUTTON MICRO INTERACTIONS ====================
function addButtonInteractions() {
    // Ripple effect on button click
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('mousedown', function(e) {
            const ripple = document.createElement('span');
            ripple.className = 'btn-ripple';
            
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Button press animation
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        button.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// ==================== FORM INPUT ANIMATIONS ====================
function addFormInteractions() {
    document.querySelectorAll('.form-control').forEach(input => {
        // Focus animation
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('form-focused');
            if (this.parentElement.classList.contains('form-group')) {
                this.previousElementSibling?.classList.add('label-floating');
            }
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('form-focused');
            if (!this.value.trim()) {
                this.parentElement.classList.remove('form-focused');
            }
        });
        
        // Has value animation
        if (input.value) {
            input.parentElement.classList.add('form-focused');
        }
    });
}

// ==================== CARD HOVER INTERACTIONS ====================
function addCardInteractions() {
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow-md)';
        });
    });
}

// ==================== SCROLL TO TOP BUTTON ====================
function addScrollToTopButton() {
    const button = document.createElement('button');
    button.className = 'scroll-to-top-btn';
    button.innerHTML = '<i class="fas fa-chevron-up"></i>';
    button.style.display = 'none';
    document.body.appendChild(button);
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            button.style.display = 'flex';
        } else {
            button.style.display = 'none';
        }
    });
    
    button.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ==================== LOADING STATE ANIMATIONS ====================
function addLoadingStates() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Don't show loading for AJAX forms
            if (this.dataset.ajax) return;
            
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
                // Show spinner for minimum 500ms for better UX
                setTimeout(() => {
                    if (submitBtn.classList.contains('loading')) {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }, 500);
            }
        });
    });
}

// ==================== NOTIFICATION WITH ANIMATION ====================
function showAnimatedNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const html = `
        <div class="notification-content">
            <i class="fas fa-${
                type === 'success' ? 'check-circle' :
                type === 'error' ? 'exclamation-circle' :
                type === 'warning' ? 'exclamation-triangle' :
                'info-circle'
            }"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    notification.innerHTML = html;
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto dismiss
    if (duration > 0) {
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }
    
    return notification;
}

// ==================== INITIALIZATION ====================
function initMicroInteractions() {
    addButtonInteractions();
    addFormInteractions();
    addCardInteractions();
    addScrollToTopButton();
    addLoadingStates();
    
    // Check if we should show donation thank you modal
    if (sessionStorage.getItem('showDonationThankYou')) {
        const amount = sessionStorage.getItem('donationAmount');
        const transactionId = sessionStorage.getItem('transactionId');
        showDonationThankYouModal(amount, transactionId);
        sessionStorage.removeItem('showDonationThankYou');
        sessionStorage.removeItem('donationAmount');
        sessionStorage.removeItem('transactionId');
    }
}

// Initialize on document load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMicroInteractions);
} else {
    initMicroInteractions();
}
