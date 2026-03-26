<!DOCTYPE html>
<html>
<head>
    <title>WebSocket Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        #output { 
            height: 300px; 
            border: 1px solid #ccc; 
            padding: 10px; 
            margin: 10px 0; 
            overflow-y: auto;
            background: #f9f9f9;
        }
        .form-group { margin-bottom: 15px; }
        input[type="text"], button { padding: 8px; margin-right: 10px; }
        button { cursor: pointer; }
        .status { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .connected { background: #d4edda; color: #155724; }
        .disconnected { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>WebSocket Test</h1>
    
    <div class="status" id="status">Status: Disconnected</div>
    
    <div class="form-group">
        <label for="userId">User ID to send to:</label>
        <input type="text" id="userId" value="1">
    </div>
    
    <div class="form-group">
        <label for="message">Message:</label>
        <input type="text" id="message" placeholder="Enter message">
        <button onclick="sendMessage()">Send Message</button>
    </div>
    
    <div id="output"></div>
    
    <script>
        const output = document.getElementById('output');
        const statusEl = document.getElementById('status');
        let socket;
        
        // Get CSRF token for API requests
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function log(message) {
            const timestamp = new Date().toLocaleTimeString();
            output.innerHTML += `[${timestamp}] ${message}<br>`;
            output.scrollTop = output.scrollHeight;
        }
        
        function connectWebSocket() {
            // Close existing connection if any
            if (socket) {
                socket.close();
            }
            
            // Create new WebSocket connection
            socket = new WebSocket('ws://localhost:8080/app/duos-key');
            
            socket.onopen = function(e) {
                statusEl.textContent = 'Status: Connected';
                statusEl.className = 'status connected';
                log('Connected to WebSocket server');
                
                // Get current user ID from the page or use a default
                const userId = document.getElementById('userId').value || '1';
                
                // Subscribe to private channel
                const subscribeMsg = {
                    event: 'subscribe',
                    data: {
                        channel: `private-chat.${userId}`,
                        auth: {
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
                            }
                        }
                    }
                };
                
                socket.send(JSON.stringify(subscribeMsg));
                log(`Subscribed to channel: chat.${userId}`);
            };
            
            socket.onmessage = function(event) {
                log('Received: ' + event.data);
            };
            
            socket.onclose = function(event) {
                statusEl.textContent = 'Status: Disconnected';
                statusEl.className = 'status disconnected';
                
                if (event.wasClean) {
                    log(`Connection closed cleanly, code=${event.code} reason=${event.reason}`);
                } else {
                    log('Connection died. Attempting to reconnect in 3 seconds...');
                    setTimeout(connectWebSocket, 3000);
                }
            };
            
            socket.onerror = function(error) {
                log(`Error: ${error.message}`);
            };
        }
        
        function sendMessage() {
            const message = document.getElementById('message').value;
            const userId = document.getElementById('userId').value || '1';
            
            if (!message) {
                alert('Please enter a message');
                return;
            }
            
            // Send message via API (which will broadcast via WebSocket)
            fetch('/test-broadcast', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
                },
                body: JSON.stringify({
                    message: message,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                log(`Sent: ${message} to user ${userId}`);
                document.getElementById('message').value = '';
            })
            .catch(error => {
                log(`Error sending message: ${error.message}`);
            });
        }
        
        // Connect when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user is logged in
            if (!localStorage.getItem('token')) {
                const token = prompt('Enter your auth token (from local storage)');
                if (token) {
                    localStorage.setItem('token', token);
                } else {
                    alert('Auth token is required to test WebSocket');
                    return;
                }
            }
            
            connectWebSocket();
            
            // Reconnect if connection is lost
            setInterval(() => {
                if (!socket || socket.readyState === WebSocket.CLOSED) {
                    connectWebSocket();
                }
            }, 5000);
            
            // Allow sending message with Enter key
            document.getElementById('message').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
    </script>
</body>
</html>
