import { EventEmitter } from 'events';

export default class AgoraWebSocketService extends EventEmitter {
    constructor() {
        super();
        this.socket = null;
        this.appId = '411395197';
        this.wsAddress = 'wss://msync-api-41.chat.agora.io';
        this.connected = false;
        this.userId = null;
        this.channels = new Set();
        this.pendingMessages = [];
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000; // 3 seconds
    }

    /**
     * Initialize WebSocket connection
     * @param {string} userId - Current user ID
     * @param {string} token - Authentication token
     */
    initialize(userId, token) {
        this.userId = userId;
        this.token = token;
        this.connect();
    }

    connect() {
        if (this.socket) {
            this.socket.close();
        }

        const wsUrl = `${this.wsAddress}/${this.appId}/${this.userId}?token=${this.token}`;
        this.socket = new WebSocket(wsUrl);

        this.socket.onopen = () => this.handleOpen();
        this.socket.onmessage = (event) => this.handleMessage(event);
        this.socket.onclose = () => this.handleClose();
        this.socket.onerror = (error) => this.handleError(error);
    }

    handleOpen() {
        console.log('WebSocket connected');
        this.connected = true;
        this.reconnectAttempts = 0;
        this.emit('connected');
        
        // Resubscribe to channels and resend pending messages
        this.channels.forEach(channel => this.subscribe(channel));
        this.processPendingMessages();
    }

    handleMessage(event) {
        try {
            const message = JSON.parse(event.data);
            console.log('WebSocket message received:', message);
            
            // Emit message type as event
            if (message.cmd) {
                this.emit(message.cmd, message);
            }
            
            // Emit raw message
            this.emit('message', message);
        } catch (error) {
            console.error('Error processing WebSocket message:', error);
        }
    }

    handleClose() {
        console.log('WebSocket disconnected');
        this.connected = false;
        this.emit('disconnected');
        this.attemptReconnect();
    }

    handleError(error) {
        console.error('WebSocket error:', error);
        this.emit('error', error);
    }

    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
            
            console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts}) in ${delay}ms`);
            
            setTimeout(() => {
                this.connect();
            }, delay);
        } else {
            console.error('Max reconnection attempts reached');
            this.emit('reconnect_failed');
        }
    }

    /**
     * Send a message through WebSocket
     * @param {Object} message - Message object to send
     * @param {boolean} [queueIfDisconnected=true] - Queue message if disconnected
     */
    send(message, queueIfDisconnected = true) {
        const messageStr = JSON.stringify(message);
        
        if (this.connected) {
            this.socket.send(messageStr);
        } else if (queueIfDisconnected) {
            console.log('Queueing message (disconnected):', message);
            this.pendingMessages.push(message);
        } else {
            throw new Error('WebSocket is not connected');
        }
    }

    processPendingMessages() {
        if (this.pendingMessages.length > 0) {
            console.log(`Processing ${this.pendingMessages.length} pending messages`);
            
            // Send all pending messages
            while (this.pendingMessages.length > 0) {
                const message = this.pendingMessages.shift();
                this.send(message, false);
            }
        }
    }

    /**
     * Subscribe to a channel
     * @param {string} channel - Channel name to subscribe to
     */
    subscribe(channel) {
        if (!this.connected) {
            this.channels.add(channel);
            return;
        }

        const message = {
            cmd: 'SUB',
            channel: channel
        };

        this.send(message);
        this.channels.add(channel);
    }

    /**
     * Unsubscribe from a channel
     * @param {string} channel - Channel name to unsubscribe from
     */
    unsubscribe(channel) {
        if (!this.connected) {
            this.channels.delete(channel);
            return;
        }

        const message = {
            cmd: 'UNSUB',
            channel: channel
        };

        this.send(message);
        this.channels.delete(channel);
    }

    /**
     * Send a chat message
     * @param {string} channel - Channel to send message to
     * @param {Object} data - Message data
     */
    sendMessage(channel, data) {
        const message = {
            cmd: 'SEND',
            channel: channel,
            data: data
        };

        this.send(message);
    }

    /**
     * Clean up WebSocket connection
     */
    disconnect() {
        if (this.socket) {
            this.socket.close();
            this.socket = null;
        }
        this.connected = false;
        this.channels.clear();
        this.pendingMessages = [];
    }
}

// Create a singleton instance
export const agoraWebSocketService = new AgoraWebSocketService();
