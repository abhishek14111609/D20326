<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-token" content="{{ auth()->user()->createToken('chat-demo')->plainTextToken ?? '' }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <title>Real-time Chat Demo - Duos</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            height: calc(100vh - 40px);
        }

        /* Conversations Sidebar */
        .conversations-sidebar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .sidebar-header h2 {
            color: #1a1a1a;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            color: #6c757d;
            font-size: 14px;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-bottom: 8px;
        }

        .conversation-item:hover {
            background: #f8f9fa;
        }

        .conversation-item.active {
            background: #007bff;
            color: white;
        }

        .conversation-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
        }

        .conversation-info {
            flex: 1;
        }

        .conversation-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .conversation-preview {
            font-size: 12px;
            opacity: 0.7;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-meta {
            text-align: right;
            font-size: 11px;
            opacity: 0.7;
        }

        /* Chat Area */
        .chat-area {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .chat-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
            text-align: center;
            padding: 40px;
        }

        .chat-placeholder i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .chat-placeholder h3 {
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .chat-placeholder p {
            max-width: 300px;
            line-height: 1.5;
        }

        /* Connection Status */
        .connection-status {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1000;
            transition: all 0.3s;
        }

        .connection-status.connected {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .connection-status.connecting {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .connection-status.disconnected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Demo Users */
        .demo-users {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .demo-users h4 {
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .demo-user {
            display: flex;
            align-items: center;
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-bottom: 5px;
        }

        .demo-user:hover {
            background: #f8f9fa;
        }

        .demo-user-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 8px;
            object-fit: cover;
        }

        .demo-user-name {
            font-size: 12px;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .demo-container {
                grid-template-columns: 1fr;
                height: auto;
                min-height: calc(100vh - 40px);
            }

            .conversations-sidebar {
                order: 2;
                height: 300px;
            }

            .chat-area {
                order: 1;
                height: 500px;
            }
        }
    </style>
</head>
<body>
    <!-- Connection Status Indicator -->
    <div class="connection-status" id="connection-status">
        <i class="fas fa-circle"></i> Connecting...
    </div>

    <div class="demo-container">
        <!-- Conversations Sidebar -->
        <div class="conversations-sidebar">
            <div class="sidebar-header">
                <h2>Real-time Chat</h2>
                <p>Instant messaging powered by Agora RTM</p>
            </div>

            <div class="conversations-list" id="conversations-list">
                <!-- Conversations will be loaded here -->
            </div>

            <div class="demo-users">
                <h4>Demo Users</h4>
                <div class="demo-user" onclick="startDemoChat(1, 'Alice Johnson', '/assets/img/user1.jpg')">
                    <img src="/assets/img/user1.jpg" alt="Alice" class="demo-user-avatar">
                    <span class="demo-user-name">Alice Johnson</span>
                </div>
                <div class="demo-user" onclick="startDemoChat(2, 'Bob Smith', '/assets/img/user2.jpg')">
                    <img src="/assets/img/user2.jpg" alt="Bob" class="demo-user-avatar">
                    <span class="demo-user-name">Bob Smith</span>
                </div>
                <div class="demo-user" onclick="startDemoChat(3, 'Charlie Brown', '/assets/img/user3.jpg')">
                    <img src="/assets/img/user3.jpg" alt="Charlie" class="demo-user-avatar">
                    <span class="demo-user-name">Charlie Brown</span>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <div class="chat-placeholder" id="chat-placeholder">
                <i class="fas fa-comments"></i>
                <h3>Welcome to Real-time Chat</h3>
                <p>Select a conversation from the sidebar or start a demo chat with one of the demo users to experience instant, zero-loading messaging.</p>
            </div>
            <div id="chat-container" style="display: none;"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/js/rtm-service.js"></script>
    <script src="/js/chat-ui.js"></script>
    <script>
        let chatUI = null;
        let currentConversation = null;

        // Initialize demo
        document.addEventListener('DOMContentLoaded', async () => {
            await initializeDemo();
        });

        async function initializeDemo() {
            // Wait for RTM service to be ready
            const checkRtmReady = () => {
                return new Promise((resolve) => {
                    const check = () => {
                        if (window.RtmService && window.RtmService.isReady()) {
                            resolve();
                        } else {
                            setTimeout(check, 100);
                        }
                    };
                    check();
                });
            };

            try {
                await checkRtmReady();
                updateConnectionStatus('connected');
                
                // Bind RTM connection state changes
                window.RtmService.on('connectionState', (data) => {
                    updateConnectionStatus(data.state.toLowerCase());
                });

                // Load conversations
                await loadConversations();
                
                console.log('Demo initialized successfully');
            } catch (error) {
                console.error('Failed to initialize demo:', error);
                updateConnectionStatus('disconnected');
            }
        }

        function updateConnectionStatus(state) {
            const statusElement = document.getElementById('connection-status');
            const icon = statusElement.querySelector('i');
            
            statusElement.className = `connection-status ${state}`;
            
            switch (state) {
                case 'connected':
                    statusElement.innerHTML = '<i class="fas fa-circle"></i> Connected';
                    break;
                case 'connecting':
                    statusElement.innerHTML = '<i class="fas fa-circle"></i> Connecting...';
                    break;
                case 'disconnected':
                    statusElement.innerHTML = '<i class="fas fa-circle"></i> Disconnected';
                    break;
            }
        }

        async function loadConversations() {
            try {
                const response = await fetch('/api/rtm/conversations', {
                    headers: {
                        'Authorization': `Bearer ${window.RtmService.getAuthToken()}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    renderConversations(data.data.conversations);
                }
            } catch (error) {
                console.error('Failed to load conversations:', error);
            }
        }

        function renderConversations(conversations) {
            const conversationsList = document.getElementById('conversations-list');
            
            if (conversations.length === 0) {
                conversationsList.innerHTML = '<p style="text-align: center; color: #6c757d; font-size: 14px; margin-top: 20px;">No conversations yet. Start a demo chat!</p>';
                return;
            }

            conversationsList.innerHTML = conversations.map(conversation => `
                <div class="conversation-item" onclick="openConversation(${conversation.other_user.id}, ${JSON.stringify(conversation.other_user).replace(/"/g, '&quot;')})">
                    <img src="${conversation.other_user.profile_image || '/assets/img/default-avatar.png'}" alt="${conversation.other_user.name}" class="conversation-avatar">
                    <div class="conversation-info">
                        <div class="conversation-name">${conversation.other_user.name}</div>
                        <div class="conversation-preview">${conversation.last_message?.message || 'No messages yet'}</div>
                    </div>
                    <div class="conversation-meta">
                        ${conversation.last_message ? new Date(conversation.last_message.created_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}) : ''}
                        ${conversation.unread_count > 0 ? `<div style="background: #007bff; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; margin-top: 5px;">${conversation.unread_count}</div>` : ''}
                    </div>
                </div>
            `).join('');
        }

        async function openConversation(userId, userInfo) {
            // Hide placeholder
            document.getElementById('chat-placeholder').style.display = 'none';
            
            // Show chat container
            const chatContainer = document.getElementById('chat-container');
            chatContainer.style.display = 'block';

            // Initialize chat UI if not already done
            if (!chatUI) {
                chatUI = new ChatUI('chat-container', {
                    theme: 'light',
                    showTypingIndicator: true,
                    showReadReceipts: true,
                    enableMessageStatus: true
                });
            }

            // Open conversation
            await chatUI.openConversation(userId, userInfo);
            
            // Update active conversation in sidebar
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            currentConversation = { userId, userInfo };
        }

        async function startDemoChat(userId, userName, userAvatar) {
            const userInfo = {
                id: userId,
                name: userName,
                avatar: userAvatar,
                status: 'online'
            };

            await openConversation(userId, userInfo);
            
            // Scroll to chat area on mobile
            if (window.innerWidth <= 768) {
                document.querySelector('.chat-area').scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768 && currentConversation) {
                document.querySelector('.conversations-sidebar').style.display = 'none';
            } else {
                document.querySelector('.conversations-sidebar').style.display = 'block';
            }
        });

        // Auto-refresh conversations every 30 seconds
        setInterval(loadConversations, 30000);

        // Handle page visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && window.RtmService) {
                // Refresh connection when page becomes visible
                window.RtmService.refreshToken();
            }
        });
    </script>
</body>
</html>
