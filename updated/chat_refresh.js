// chat_refresh.js
class ChatRefresh {
    constructor() {
        this.pollingInterval = null;
        this.pollingDelay = 5000; // 5 seconds
        this.isPolling = false;
        this.lastMessageId = 0;
        this.adminUserId = null;
        this.initialize();
    }

    initialize() {
        // Get admin user ID from session
        this.adminUserId = document.body.getAttribute('data-user-id') || 
                          (window.currentUser ? window.currentUser.id : null);
        
        // Start polling if we're in chat section
        if (this.isInChatSection()) {
            this.startPolling();
        }
        
        // Listen for section changes
        this.setupSectionObserver();
    }

    isInChatSection() {
        const chatSection = document.getElementById('chat');
        return chatSection && chatSection.style.display !== 'none';
    }

    setupSectionObserver() {
        // Observe URL hash changes for section switching
        window.addEventListener('hashchange', () => {
            if (this.isInChatSection()) {
                this.startPolling();
            } else {
                this.stopPolling();
            }
        });

        // Also check when section is shown via showSection function
        const originalShowSection = window.showSection;
        if (typeof originalShowSection === 'function') {
            window.showSection = (sectionId) => {
                originalShowSection(sectionId);
                if (sectionId === 'chat') {
                    setTimeout(() => this.startPolling(), 500);
                } else {
                    this.stopPolling();
                }
            };
        }
    }

    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.loadInitialMessages();
        
        this.pollingInterval = setInterval(() => {
            this.fetchNewMessages();
        }, this.pollingDelay);
        
        console.log('Chat polling started');
    }

    stopPolling() {
        if (!this.isPolling) return;
        
        clearInterval(this.pollingInterval);
        this.isPolling = false;
        console.log('Chat polling stopped');
    }

    async loadInitialMessages() {
        try {
            const response = await fetch('get_recent_chats.php?limit=50');
            const data = await response.json();
            
            if (data.success) {
                this.lastMessageId = data.messages.length > 0 ? 
                    Math.max(...data.messages.map(m => m.id)) : 0;
                this.updateChatInterface(data);
                this.updateNotificationBadge(data.unread_count);
            }
        } catch (error) {
            console.error('Error loading initial messages:', error);
        }
    }

    async fetchNewMessages() {
        try {
            const response = await fetch(`get_recent_chats.php?limit=20&offset=0`);
            const data = await response.json();
            
            if (data.success) {
                const newMessages = data.messages.filter(msg => msg.id > this.lastMessageId);
                
                if (newMessages.length > 0) {
                    this.lastMessageId = Math.max(...data.messages.map(m => m.id));
                    this.appendNewMessages(newMessages);
                    this.updateNotificationBadge(data.unread_count);
                    
                    // Play notification sound if there are new unread messages
                    if (newMessages.some(msg => !msg.is_read && msg.sender_type === 'resident')) {
                        this.playNotificationSound();
                        this.showDesktopNotification('New Chat Message', 
                            `You have ${newMessages.length} new message(s)`);
                    }
                }
                
                this.updateOnlineUsers(data.online_users);
                this.updateChatStats(data.chat_stats);
            }
        } catch (error) {
            console.error('Error fetching new messages:', error);
        }
    }

    updateChatInterface(data) {
        const chatMessages = document.getElementById('adminChatMessages');
        if (!chatMessages) return;

        // Clear existing messages
        chatMessages.innerHTML = '';
        
        // Add messages in chronological order
        data.messages.forEach(msg => {
            this.addMessageToChat(msg);
        });
        
        // Scroll to bottom
        this.scrollToBottom();
    }

    appendNewMessages(messages) {
        const chatMessages = document.getElementById('adminChatMessages');
        if (!chatMessages) return;

        // Add new messages
        messages.forEach(msg => {
            this.addMessageToChat(msg);
        });
        
        // Scroll to bottom if user is at bottom
        if (this.isAtBottom()) {
            this.scrollToBottom();
        }
    }

    addMessageToChat(message) {
        const chatMessages = document.getElementById('adminChatMessages');
        if (!chatMessages) return;

        const messageElement = document.createElement('div');
        messageElement.className = `chat-message ${message.sender_type}`;
        messageElement.dataset.messageId = message.id;
        
        const isCurrentUser = message.sender_id === this.adminUserId;
        const senderName = isCurrentUser ? 'You' : message.sender_name;
        
        messageElement.innerHTML = `
            <strong>${senderName}</strong>
            <small style="color: #666;">(${message.formatted_time})</small>
            <p>${message.message}</p>
            ${!message.is_read && message.sender_type === 'resident' ? 
                '<span class="unread-indicator" style="color: #ff4757; font-size: 0.8em;">●</span>' : ''}
        `;
        
        chatMessages.appendChild(messageElement);
    }

    updateNotificationBadge(unreadCount) {
        // Update sidebar badge
        const chatBadge = document.querySelector('a[href="#chat"] .badge');
        if (chatBadge) {
            if (unreadCount > 0) {
                chatBadge.textContent = unreadCount;
                chatBadge.style.display = 'inline-block';
            } else {
                chatBadge.style.display = 'none';
            }
        }
        
        // Update topbar notification badge
        const notificationBadge = document.querySelector('.notification-badge');
        if (notificationBadge) {
            if (unreadCount > 0) {
                notificationBadge.textContent = unreadCount;
                notificationBadge.style.display = 'flex';
            } else {
                notificationBadge.style.display = 'none';
            }
        }
        
        // Update browser tab title
        this.updateBrowserTitle(unreadCount);
    }

    updateBrowserTitle(unreadCount) {
        const baseTitle = 'Barangay Dahat - Admin';
        if (unreadCount > 0) {
            document.title = `(${unreadCount}) ${baseTitle}`;
        } else {
            document.title = baseTitle;
        }
    }

    updateOnlineUsers(onlineUsers) {
        const onlineUsersContainer = document.getElementById('onlineUsersList');
        if (!onlineUsersContainer) return;

        if (onlineUsers.length === 0) {
            onlineUsersContainer.innerHTML = `
                <div style="text-align: center; color: #666; padding: 1rem;">
                    <i class="fas fa-user-slash" style="font-size: 2rem;"></i>
                    <p>No residents online</p>
                </div>
            `;
            return;
        }

        let html = '';
        onlineUsers.forEach(user => {
            html += `
                <div style="display: flex; align-items: center; gap: 10px; padding: 10px; border-bottom: 1px solid #eee;">
                    <div style="width: 40px; height: 40px; background-color: #0d4a9e; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        ${user.full_name.charAt(0).toUpperCase()}
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 500;">${user.full_name}</div>
                        <small style="color: #2ed573;">
                            <i class="fas fa-circle" style="font-size: 0.7rem;"></i> Online
                        </small>
                        <small style="color: #666; display: block;">Last active: ${user.formatted_last_active}</small>
                    </div>
                    <button onclick="startPrivateChat(${user.id})" style="background: none; border: none; color: #0d4a9e; cursor: pointer;" title="Start private chat">
                        <i class="fas fa-comment"></i>
                    </button>
                </div>
            `;
        });
        
        onlineUsersContainer.innerHTML = html;
    }

    updateChatStats(stats) {
        // Update statistics in the chat section
        const statsElement = document.getElementById('chatStats');
        if (statsElement) {
            statsElement.innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
                        <div style="font-size: 0.9rem; color: #666;">Today's Messages</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #0d4a9e;">${stats.today_messages || 0}</div>
                    </div>
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
                        <div style="font-size: 0.9rem; color: #666;">Resident Messages</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #ff7e30;">${stats.resident_messages || 0}</div>
                    </div>
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
                        <div style="font-size: 0.9rem; color: #666;">Official Messages</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #2ed573;">${stats.official_messages || 0}</div>
                    </div>
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
                        <div style="font-size: 0.9rem; color: #666;">Total Messages</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #ff4757;">${stats.total_messages || 0}</div>
                    </div>
                </div>
            `;
        }
    }

    scrollToBottom() {
        const chatMessages = document.getElementById('adminChatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    isAtBottom() {
        const chatMessages = document.getElementById('adminChatMessages');
        if (!chatMessages) return true;
        
        const tolerance = 100; // pixels
        return chatMessages.scrollTop + chatMessages.clientHeight >= chatMessages.scrollHeight - tolerance;
    }

    playNotificationSound() {
        const audio = new Audio('notification.mp3');
        audio.volume = 0.3;
        audio.play().catch(e => console.log('Audio play failed:', e));
    }

    showDesktopNotification(title, body) {
        if (!("Notification" in window)) return;
        
        if (Notification.permission === "granted") {
            new Notification(title, { body, icon: '/favicon.ico' });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    new Notification(title, { body, icon: '/favicon.ico' });
                }
            });
        }
    }

    sendMessage(message) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('chat_message', message);
            formData.append('official_chat_message', '1');
            
            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resolve(data);
                } else {
                    reject(data.message);
                }
            })
            .catch(error => reject(error));
        });
    }

    markAsRead(messageId) {
        fetch('mark_as_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message_id: messageId })
        });
    }

    clearChat() {
        if (confirm('Are you sure you want to clear all chat messages? This action cannot be undone.')) {
            fetch('clear_chat.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const chatMessages = document.getElementById('adminChatMessages');
                        if (chatMessages) {
                            chatMessages.innerHTML = '<p style="text-align: center; color: #666;">Chat cleared</p>';
                        }
                    }
                });
        }
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.chatRefresh = new ChatRefresh();
});