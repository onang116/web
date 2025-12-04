// notification.js
class NotificationSystem {
    constructor() {
        this.container = null;
        this.pollingInterval = null;
        this.notifications = [];
        this.lastCheck = null;
        this.soundEnabled = true;
        this.desktopNotifications = false;
        this.initialize();
    }

    initialize() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notificationContainer')) {
            this.createContainer();
        }
        
        this.container = document.getElementById('notificationContainer');
        
        // Load saved preferences
        this.loadPreferences();
        
        // Request notification permission
        this.requestNotificationPermission();
        
        // Start polling for new notifications
        this.startPolling();
        
        // Set up click handlers
        this.setupEventListeners();
    }

    createContainer() {
        const container = document.createElement('div');
        container.id = 'notificationContainer';
        container.className = 'notification-container';
        document.body.appendChild(container);
    }

    loadPreferences() {
        const soundPref = localStorage.getItem('notificationSound');
        const desktopPref = localStorage.getItem('desktopNotifications');
        
        if (soundPref !== null) this.soundEnabled = soundPref === 'true';
        if (desktopPref !== null) this.desktopNotifications = desktopPref === 'true';
    }

    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                localStorage.setItem('desktopNotifications', permission === 'granted');
                this.desktopNotifications = permission === 'granted';
            });
        }
    }

    startPolling() {
        // Check every 30 seconds
        this.pollingInterval = setInterval(() => {
            this.checkNotifications();
        }, 30000);
        
        // Initial check
        this.checkNotifications();
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }

    async checkNotifications() {
        try {
            const response = await fetch('notification.php');
            const data = await response.json();
            
            if (data.success) {
                this.processNotifications(data.notifications);
                this.updateBadges(data.total_count, data.unread_total);
            }
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }

    processNotifications(newNotifications) {
        // Filter out notifications we already have
        const existingIds = this.notifications.map(n => n.id || n.timestamp);
        const uniqueNotifications = newNotifications.filter(notification => {
            const id = notification.id || (notification.type + notification.message + notification.time);
            return !existingIds.includes(id);
        });

        // Show new notifications
        uniqueNotifications.forEach(notification => {
            this.showNotification(notification);
            this.notifications.push({
                ...notification,
                id: notification.id || (notification.type + notification.message + notification.time),
                timestamp: Date.now()
            });
        });

        // Keep only last 50 notifications
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(-50);
        }

        // Update UI
        this.updateNotificationList();
    }

    showNotification(notification) {
        // Create notification element
        const notificationElement = this.createNotificationElement(notification);
        
        // Add to container
        this.container.insertBefore(notificationElement, this.container.firstChild);
        
        // Play sound if enabled
        if (this.soundEnabled && notification.priority !== 'low') {
            this.playNotificationSound(notification.priority);
        }
        
        // Show desktop notification if enabled
        if (this.desktopNotifications && notification.priority === 'high') {
            this.showDesktopNotification(notification);
        }
        
        // Auto-remove after 10 seconds (for high priority) or 20 seconds
        const removeTime = notification.priority === 'high' ? 10000 : 20000;
        setTimeout(() => {
            if (notificationElement.parentNode) {
                notificationElement.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (notificationElement.parentNode) {
                        notificationElement.remove();
                    }
                }, 300);
            }
        }, removeTime);
    }

    createNotificationElement(notification) {
        const element = document.createElement('div');
        element.className = `notification-item notification-${notification.priority || 'normal'}`;
        element.dataset.type = notification.type;
        element.dataset.id = notification.id || Date.now();
        
        const iconColor = notification.color || '#0d4a9e';
        const badge = notification.count > 1 ? `<span class="notification-badge">${notification.count}</span>` : '';
        
        element.innerHTML = `
            <div class="notification-dot" style="background: ${iconColor};"></div>
            <button class="notification-close">&times;</button>
            
            <div class="notification-header">
                <div class="notification-icon" style="background: ${iconColor};">
                    <i class="${notification.icon || 'fas fa-bell'}"></i>
                </div>
                <div class="notification-title">${notification.title}</div>
                <div class="notification-time">${notification.time}</div>
                ${badge}
            </div>
            
            <div class="notification-message">${notification.message}</div>
            
            <div class="notification-actions">
                <button class="notification-btn notification-btn-primary" onclick="notificationSystem.handleAction('${notification.type}', '${notification.link}')">
                    <i class="fas fa-eye"></i> View
                </button>
                ${notification.type === 'chat' ? `
                    <button class="notification-btn notification-btn-secondary" onclick="notificationSystem.markAsRead('${notification.type}')">
                        <i class="fas fa-check"></i> Mark as read
                    </button>
                ` : ''}
            </div>
        `;
        
        // Add click handler for the whole notification
        element.addEventListener('click', (e) => {
            if (!e.target.closest('.notification-close') && !e.target.closest('.notification-btn')) {
                this.handleAction(notification.type, notification.link);
            }
        });
        
        // Add close button handler
        element.querySelector('.notification-close').addEventListener('click', (e) => {
            e.stopPropagation();
            element.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => element.remove(), 300);
        });
        
        return element;
    }

    handleAction(type, link) {
        switch (type) {
            case 'clearance':
                if (window.location.pathname.includes('admin_dashboard.php')) {
                    window.showSection('clearance');
                } else {
                    window.location.href = link || 'my_requests.php';
                }
                break;
                
            case 'complaint':
                if (window.location.pathname.includes('admin_dashboard.php')) {
                    window.showSection('complaints');
                } else {
                    window.location.href = link || 'my_complaints.php';
                }
                break;
                
            case 'chat':
                if (window.location.pathname.includes('admin_dashboard.php')) {
                    window.showSection('chat');
                } else {
                    const chatButton = document.getElementById('chatButton');
                    if (chatButton) {
                        chatButton.click();
                    }
                }
                break;
                
            case 'event':
                if (window.location.pathname.includes('admin_dashboard.php')) {
                    window.showSection('events');
                } else {
                    const eventsBtn = document.getElementById('eventsBtn') || document.getElementById('viewEventsBtn');
                    if (eventsBtn) {
                        eventsBtn.click();
                    }
                }
                break;
                
            default:
                if (link && link.startsWith('#')) {
                    if (window.showSection) {
                        window.showSection(link.substring(1));
                    }
                } else if (link) {
                    window.location.href = link;
                }
        }
        
        // Mark as read
        this.markAsRead(type);
    }

    markAsRead(type) {
        // Send request to mark notifications as read
        fetch('mark_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ type: type })
        });
        
        // Remove notifications of this type from UI
        document.querySelectorAll(`.notification-item[data-type="${type}"]`).forEach(item => {
            item.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => item.remove(), 300);
        });
    }

    updateBadges(totalCount, unreadTotal) {
        // Update sidebar badge for admin
        const adminBadge = document.querySelector('a[href="#"] .badge');
        if (adminBadge && unreadTotal !== undefined) {
            if (unreadTotal > 0) {
                adminBadge.textContent = unreadTotal > 9 ? '9+' : unreadTotal;
                adminBadge.style.display = 'inline-block';
            } else {
                adminBadge.style.display = 'none';
            }
        }
        
        // Update browser title
        this.updateBrowserTitle(totalCount);
        
        // Update notification icon in topbar
        this.updateNotificationIcon(totalCount);
    }

    updateBrowserTitle(count) {
        const baseTitle = document.title.replace(/^\(\d+\)\s*/, '');
        if (count > 0) {
            document.title = `(${count}) ${baseTitle}`;
        } else {
            document.title = baseTitle;
        }
    }

    updateNotificationIcon(count) {
        const notificationIcons = document.querySelectorAll('.notification-icon');
        notificationIcons.forEach(icon => {
            const badge = icon.querySelector('.notification-badge') || 
                         (() => {
                             const b = document.createElement('span');
                             b.className = 'notification-badge';
                             icon.appendChild(b);
                             return b;
                         })();
            
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    playNotificationSound(priority) {
        const audio = new Audio();
        
        switch (priority) {
            case 'high':
                audio.src = 'sounds/high_priority.mp3';
                audio.volume = 0.7;
                break;
            case 'medium':
                audio.src = 'sounds/medium_priority.mp3';
                audio.volume = 0.5;
                break;
            default:
                audio.src = 'sounds/notification.mp3';
                audio.volume = 0.3;
        }
        
        audio.play().catch(e => console.log('Audio play failed:', e));
    }

    showDesktopNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: 'favicon.ico',
                tag: notification.type
            });
        }
    }

    updateNotificationList() {
        // This would update a dedicated notifications panel
        const panel = document.getElementById('notificationsPanel');
        if (!panel) return;
        
        if (this.notifications.length === 0) {
            panel.innerHTML = `
                <div class="notification-empty">
                    <i class="far fa-bell"></i>
                    <h3>No notifications</h3>
                    <p>You're all caught up!</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        this.notifications.forEach(notification => {
            html += this.createNotificationElement(notification).outerHTML;
        });
        
        panel.innerHTML = html;
    }

    setupEventListeners() {
        // Keyboard shortcut for notifications (Ctrl+Shift+N)
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'N') {
                e.preventDefault();
                this.showNotificationsPanel();
            }
        });
    }

    showNotificationsPanel() {
        // Create or show notifications panel
        let panel = document.getElementById('notificationsPanel');
        
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'notificationsPanel';
            panel.className = 'notifications-panel';
            panel.style.cssText = `
                position: fixed;
                top: 60px;
                right: 20px;
                width: 350px;
                max-height: 500px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                z-index: 10000;
                overflow: hidden;
                display: none;
            `;
            
            document.body.appendChild(panel);
        }
        
        // Toggle panel visibility
        if (panel.style.display === 'block') {
            panel.style.display = 'none';
        } else {
            panel.style.display = 'block';
            this.updateNotificationList();
        }
    }

    clearAllNotifications() {
        fetch('clear_notifications.php', { method: 'POST' })
            .then(() => {
                this.notifications = [];
                this.updateNotificationList();
                this.updateBadges(0, 0);
                document.querySelectorAll('.notification-item').forEach(item => item.remove());
            });
    }

    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        localStorage.setItem('notificationSound', this.soundEnabled);
    }

    toggleDesktopNotifications() {
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                this.desktopNotifications = !this.desktopNotifications;
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    this.desktopNotifications = permission === 'granted';
                });
            }
            localStorage.setItem('desktopNotifications', this.desktopNotifications);
        }
    }
}

// Initialize notification system
window.notificationSystem = new NotificationSystem();

// Helper function to manually show a notification
function showNotification(title, message, type = 'info', link = null) {
    const notification = {
        title: title,
        message: message,
        type: type,
        icon: type === 'success' ? 'fas fa-check-circle' : 
              type === 'error' ? 'fas fa-exclamation-circle' : 
              type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle',
        color: type === 'success' ? '#2ed573' : 
               type === 'error' ? '#ff4757' : 
               type === 'warning' ? '#ffa502' : '#0d4a9e',
        link: link,
        time: 'Just now',
        priority: type === 'error' ? 'high' : 'normal'
    };
    
    window.notificationSystem.showNotification(notification);
}