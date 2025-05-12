<?php
// This file contains the chat widget code for Nail Architect with database integration
// Include this file before the closing </body> tag on all pages

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$user_name = $is_logged_in ? ($_SESSION['user_first_name'] ?? 'User') : 'Guest';
?>

<!-- Chat Widget HTML -->
<div id="chat-widget">
    <div id="chat-button">
        <i class="fa fa-comments"></i>
        <span id="unread-count" class="hidden">0</span>
    </div>
    <div id="chat-container" class="hidden">
        <div id="chat-header">
            <div id="chat-title">Nail Architect Chat</div>
            <div id="chat-controls">
                <div id="chat-minimize"><i class="fa fa-minus"></i></div>
                <div id="chat-close"><i class="fa fa-times"></i></div>
            </div>
        </div>
        <div id="chat-messages">
            <div class="chat-message bot">
                <div class="chat-avatar">NA</div>
                <div class="chat-bubble">
                    Welcome to Nail Architect! How can I help you today?
                </div>
            </div>
        </div>

        <!-- Live Agent Button -->
        <div id="quick-actions">
            <div class="agent-row">
                <button id="chat-with-agent" class="agent-btn">Chat with Live Agent</button>
            </div>
        </div>

        <div id="chat-input-container">
            <div id="hamburger-menu">
                <div class="hamburger-icon">
                    <i class="fa fa-bars"></i>
                </div>
                <div id="quick-questions" class="hidden">
                    <div class="question-row">
                        <button class="quick-btn" data-question="What are your business hours?">Business Hours</button>
                        <button class="quick-btn" data-question="What services do you offer?">Services</button>
                    </div>
                    <div class="question-row">
                        <button class="quick-btn" data-question="How much do your services cost?">Pricing</button>
                        <button class="quick-btn" data-question="How do I book an appointment?">Book Appointment</button>
                    </div>
                    <div class="question-row">
                        <button class="quick-btn" data-question="Where are you located?">Location</button>
                        <button class="quick-btn" data-question="Do you accept walk-ins?">Walk-ins</button>
                    </div>
                    <div class="question-row clear-row">
                        <button id="clear-chat" class="clear-btn">Clear Chat History</button>
                    </div>
                </div>
            </div>
            <input type="text" id="chat-input" placeholder="Type your message...">
            <button id="chat-send"><i class="fa fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<!-- Chat Widget CSS -->
<style>
    #chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        font-family: 'Poppins', sans-serif;
    }

    #chat-button {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(to right, #e6a4a4, #d98d8d);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        font-size: 24px;
        position: relative;
    }

    #unread-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #ff0000;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    #unread-count.hidden {
        display: none;
    }

    #chat-button:hover {
        background-color: #d9bbb0;
        transform: translateY(-3px);
    }

    #chat-container {
        position: absolute;
        bottom: 70px;
        right: 0;
        width: 320px;
        height: 450px;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: all 0.3s ease;
        opacity: 1;
        transform: translateY(0);
    }

    #chat-container.hidden {
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
    }

    #chat-header {
        padding: 12px 15px;
        background: linear-gradient(to right, rgb(222, 131, 131), rgb(111, 33, 50));
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #chat-title {
        font-weight: 500;
    }

    #chat-controls {
        display: flex;
        gap: 10px;
    }

    #chat-controls div {
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    #chat-controls div:hover {
        opacity: 1;
    }

    #chat-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background-color: #F2E9E9;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* Quick Actions Styles */
    #quick-actions {
        padding: 10px;
        background-color: #f9f2f2;
        border-top: 1px solid #eee;
    }

    /* Live Agent Button */
    .agent-row {
        display: flex;
        justify-content: center;
    }

    .agent-btn {
        width: 100%;
        padding: 10px 0;
        background: linear-gradient(to right, #c23d3d, #a52929);
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .agent-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .agent-btn.connected {
        background: linear-gradient(to right, #27ae60, #2ecc71);
    }

    #chat-input-container {
        padding: 12px;
        display: flex;
        gap: 8px;
        border-top: 1px solid #eee;
        align-items: center;
    }

    #hamburger-menu {
        position: relative;
    }

    .hamburger-icon {
        width: 32px;
        height: 32px;
        background-color: #f5f5f5;
        border-radius: 50%;
        color: #d98d8d;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #e0c5b7;
    }

    .hamburger-icon:hover {
        background-color: #e6a4a4;
        color: white;
    }

    #quick-questions {
        position: absolute;
        bottom: 100%;
        left: 0;
        margin-bottom: 5px;
        width: 280px;
        padding: 10px;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: all 0.3s ease;
        opacity: 1;
        transform: translateY(0);
    }

    #quick-questions.hidden {
        opacity: 0;
        transform: translateY(10px);
        pointer-events: none;
    }

    .question-row {
        display: flex;
        gap: 8px;
        justify-content: space-between;
    }

    .quick-btn {
        flex: 1;
        padding: 8px 0;
        background-color: white;
        border: 1px solid #e6a4a4;
        border-radius: 12px;
        color: #d98d8d;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        font-family: 'Poppins', sans-serif;
    }

    .quick-btn:hover {
        background-color: #e6a4a4;
        color: white;
    }

    /* Clear Chat Button */
    .clear-row {
        margin-top: 2px;
    }

    .clear-btn {
        width: 100%;
        padding: 8px 0;
        background-color: transparent;
        border: 1px dashed #d0d0d0;
        border-radius: 12px;
        color: #777;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        font-family: 'Poppins', sans-serif;
    }

    .clear-btn:hover {
        background-color: #f0f0f0;
        color: #555;
    }

    #chat-input {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #e0c5b7;
        border-radius: 20px;
        outline: none;
        font-family: 'Poppins', sans-serif;
    }

    #chat-input:focus {
        border-color: #e6a4a4;
    }

    #chat-send {
        background: linear-gradient(to right, #d98d8d, #e6a4a4);
        color: white;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    #chat-send:hover {
        background-color: #d9bbb0;
    }

    .chat-message {
        display: flex;
        gap: 8px;
        max-width: 85%;
    }

    .chat-message.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .chat-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #e0c5b7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 500;
        color: #333;
    }

    .chat-message.user .chat-avatar {
        background-color: #c78c8c;
        color: white;
    }

    .chat-message.agent .chat-avatar {
        background-color: #9b59b6;
        color: white;
    }

    .chat-bubble {
        background-color: white;
        padding: 10px 12px;
        border-radius: 15px;
        border-top-left-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        font-size: 14px;
    }

    .chat-message.user .chat-bubble {
        background-color: #e6a4a4;
        color: white;
        border-radius: 15px;
        border-top-right-radius: 5px;
        border-top-left-radius: 15px;
    }

    /* Notification styles */
    .chat-notification {
        text-align: center;
        margin: 8px 0;
        padding: 5px 10px;
        background-color: rgba(0, 0, 0, 0.05);
        border-radius: 12px;
        color: #888;
        font-size: 12px;
        animation: fadeIn 0.3s ease-out forwards;
    }

    .date-separator {
        text-align: center;
        margin: 15px 0;
        position: relative;
    }

    .date-separator::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 40%;
        height: 1px;
        background-color: #ddd;
    }

    .date-separator::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        width: 40%;
        height: 1px;
        background-color: #ddd;
    }

    .date-text {
        background-color: #f2e9e9;
        padding: 0 15px;
        display: inline-block;
        position: relative;
        font-size: 12px;
        color: #888;
    }

    /* Agent message indicator */
    .agent-indicator {
        font-size: 11px;
        color: #9b59b6;
        margin-top: 2px;
    }

    /* Animation for messages */
    @keyframes messageIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .chat-message {
        animation: messageIn 0.3s ease-out forwards;
    }

    @media (max-width: 768px) {
        #chat-container {
            width: 290px;
            height: 450px;
        }

        .quick-btn {
            font-size: 11px;
            padding: 6px 0;
        }

        .agent-btn {
            font-size: 13px;
            padding: 8px 0;
        }

        .clear-btn {
            font-size: 11px;
            padding: 6px 0;
        }

        #quick-questions {
            width: 250px;
        }
    }
</style>

<!-- Chat Widget JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get elements
        const chatButton = document.getElementById('chat-button');
        const chatContainer = document.getElementById('chat-container');
        const chatMinimize = document.getElementById('chat-minimize');
        const chatClose = document.getElementById('chat-close');
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');
        const chatMessages = document.getElementById('chat-messages');
        const quickButtons = document.querySelectorAll('.quick-btn');
        const unreadCount = document.getElementById('unread-count');

        // Get Chat with Agent and Clear Chat buttons
        const chatWithAgentBtn = document.getElementById('chat-with-agent');
        const clearChatBtn = document.getElementById('clear-chat');

        // Get hamburger menu elements
        const hamburgerIcon = document.querySelector('.hamburger-icon');
        const quickQuestionsMenu = document.getElementById('quick-questions');

        // Variables for chat state
        let isConnectedToAgent = false;
        let currentUserId = <?php echo json_encode($user_id); ?>;
        let currentUserName = <?php echo json_encode($user_name); ?>;
        let lastMessageId = 0;
        let pollingInterval = null;

        // Load messages from database if logged in
        if (currentUserId) {
            loadMessagesFromDatabase();
            // Start polling for new messages
            startPolling();
        } else {
            // Load from localStorage for guests
            loadMessagesFromLocalStorage();
        }

        // Toggle chat container
        chatButton.addEventListener('click', function() {
            chatContainer.classList.toggle('hidden');
            if (!chatContainer.classList.contains('hidden') && currentUserId) {
                markMessagesAsRead();
                unreadCount.classList.add('hidden');
            }
        });

        // Minimize chat
        chatMinimize.addEventListener('click', function() {
            chatContainer.classList.add('hidden');
        });

        // Close chat
        chatClose.addEventListener('click', function() {
            chatContainer.classList.add('hidden');
        });

        // Add event listeners to quick buttons
        quickButtons.forEach(button => {
            button.addEventListener('click', function() {
                const question = this.getAttribute('data-question');
                handleQuickQuestion(question);
            });
        });

        // Quick responses for the chatbot
        const quickResponses = {
            'hello': 'Hi there! How can I help you today?',
            'hi': 'Hello! How can I assist you with your nail needs?',
            'hours': 'We are open Monday-Friday 9am-7pm, Saturday 10am-6pm, and Sunday 11am-4pm.',
            'location': 'We are located at 123 Nail Avenue, Suite 101, Quezon City.',
            'services': 'We offer manicures, pedicures, gel nails, acrylic extensions, nail art, and more! Check our Services page for details.',
            'price': 'Our services start at ₱300 for a basic manicure. Full price list is available on our website under Services.',
            'book': 'You can book an appointment online through our website or by calling us at +63 2 8123 4567.',
            'cancel': 'To cancel or reschedule, please call us at least 24 hours in advance at +63 2 8123 4567.',
            'parking': 'Yes, we have free parking available for our clients.',
            'products': 'We use and sell premium nail care brands including OPI, Essie, and our own Nail Architect line.',
            'covid': 'We follow strict sanitation protocols. All tools are sterilized, and our staff wear masks and gloves.',
            'gift': 'Yes, we offer gift certificates that can be purchased in-store or online.',
            'walk-in': 'Walk-ins are welcome, but we recommend booking an appointment to ensure availability.',
            'thank': 'You\'re welcome! Is there anything else I can help you with?',
            'thanks': 'You\'re welcome! Is there anything else I can help you with?'
        };

        // Map quick questions to responses
        const quickQuestionMap = {
            'What are your business hours?': quickResponses['hours'],
            'What services do you offer?': quickResponses['services'],
            'How much do your services cost?': quickResponses['price'],
            'How do I book an appointment?': quickResponses['book'],
            'Where are you located?': quickResponses['location'],
            'Do you accept walk-ins?': quickResponses['walk-in']
        };

        // Handle quick question
        function handleQuickQuestion(question) {
            // Add user question to chat
            addMessage(question, 'user');

            if (!isConnectedToAgent) {
                // Get response for this question
                const response = quickQuestionMap[question];

                // Add bot response after a short delay
                setTimeout(() => {
                    addMessage(response, 'bot');
                }, 500);
            } else if (currentUserId) {
                // Send to database if connected to agent
                sendMessageToDatabase(question);
            }
        }

        // Toggle hamburger menu
        hamburgerIcon.addEventListener('click', function() {
            quickQuestionsMenu.classList.toggle('hidden');
        });

        // Close hamburger menu when clicking outside
        document.addEventListener('click', function(event) {
            const isHamburgerClick = hamburgerIcon.contains(event.target);
            const isMenuClick = quickQuestionsMenu.contains(event.target);

            if (!isHamburgerClick && !isMenuClick && !quickQuestionsMenu.classList.contains('hidden')) {
                quickQuestionsMenu.classList.add('hidden');
            }
        });

        // Close menu after selecting a quick question
        quickButtons.forEach(button => {
            button.addEventListener('click', function() {
                quickQuestionsMenu.classList.add('hidden');
            });
        });

        // Send message function
        function sendMessage() {
            const message = chatInput.value.trim();
            if (message === '') return;

            // Add user message
            addMessage(message, 'user');
            chatInput.value = '';

            if (isConnectedToAgent && currentUserId) {
                // Send to database for live agent
                sendMessageToDatabase(message);
            } else if (!isConnectedToAgent) {
                // Process bot response only if not connected to agent
                setTimeout(() => {
                    processResponse(message);
                }, 500);
            }
        }

        // Process bot response
        function processResponse(message) {
            // Don't process bot responses if connected to an agent
            if (isConnectedToAgent) {
                return;
            }

            const messageLower = message.toLowerCase();
            let response = "I'm not sure about that. Would you like to speak with one of our nail technicians? You can reach us at +63 2 8123 4567.";

            // Check for keywords in the quick responses
            for (const keyword in quickResponses) {
                if (messageLower.includes(keyword)) {
                    response = quickResponses[keyword];
                    break;
                }
            }

            // Special case for booking-related inquiries
            if (messageLower.includes('appointment') || messageLower.includes('schedule') || messageLower.includes('booking')) {
                response = 'Would you like to book an appointment now? You can use our online booking system or call us at +63 2 8123 4567.';
            }

            // Add bot response
            addMessage(response, 'bot');
        }

        // Add message to chat
        function addMessage(message, sender, senderName = null, timestamp = null) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('chat-message', sender);

            const avatarElement = document.createElement('div');
            avatarElement.classList.add('chat-avatar');
            
            if (sender === 'user') {
                avatarElement.textContent = currentUserName ? currentUserName.charAt(0).toUpperCase() : 'You';
            } else if (sender === 'agent') {
                avatarElement.textContent = 'NA';
                avatarElement.style.backgroundColor = '#9b59b6';
            } else {
                avatarElement.textContent = 'NA';
            }

            const messageContent = document.createElement('div');
            messageContent.classList.add('message-content');

            const bubbleElement = document.createElement('div');
            bubbleElement.classList.add('chat-bubble');
            bubbleElement.textContent = message;

            messageContent.appendChild(bubbleElement);

            // Add agent indicator if it's an agent message
            if (sender === 'agent') {
                const indicatorElement = document.createElement('div');
                indicatorElement.classList.add('agent-indicator');
                indicatorElement.textContent = '• Nail Architect Agent';
                messageContent.appendChild(indicatorElement);
            }

            // Add timestamp if provided
            if (timestamp) {
                const timeElement = document.createElement('div');
                timeElement.classList.add('message-time');
                timeElement.textContent = timestamp;
                messageContent.appendChild(timeElement);
            }

            messageElement.appendChild(avatarElement);
            messageElement.appendChild(messageContent);

            chatMessages.appendChild(messageElement);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Save messages locally if not logged in
            if (!currentUserId) {
                saveMessagesToLocalStorage();
            }
        }

        // Chat with Live Agent button
        chatWithAgentBtn.addEventListener('click', function() {
            if (!currentUserId) {
                // If not logged in, prompt to log in
                addMessage("Please log in to chat with a live agent.", 'bot');
                setTimeout(() => {
                    addMessage("You can log in using the user icon in the top right corner.", 'bot');
                }, 1000);
                return;
            }

            // Add user message requesting live agent
            addMessage("I'd like to chat with a live agent please.", 'user');

            // Send request to database
            sendMessageToDatabase("I'd like to chat with a live agent please.", "Live Chat Request");

            // Update UI
            setTimeout(() => {
                addMessage("Connecting you with a live agent. Please wait a moment while we transfer you to the next available specialist.", 'bot');
                isConnectedToAgent = true;
                chatWithAgentBtn.textContent = "Connected to Agent";
                chatWithAgentBtn.classList.add('connected');
                chatWithAgentBtn.disabled = true;
                
                // Increase polling frequency when connected to agent
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }
                pollingInterval = setInterval(checkForNewMessages, 2000); // Check every 2 seconds when connected
            }, 500);
        });

        // Send message to database
        function sendMessageToDatabase(message, subject = null) {
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('content', message);
            formData.append('subject', subject || 'Chat Widget Message');

            fetch('chat.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to send message:', data.message);
                    addMessage("Error sending message. Please try again.", 'bot');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                addMessage("Error sending message. Please try again.", 'bot');
            });
        }

        // Load messages from database
        function loadMessagesFromDatabase() {
            fetch('chat.php?action=get_messages')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages) {
                        // Clear existing messages
                        chatMessages.innerHTML = '';
                        
                        // Track if we've connected to an agent
                        let foundAgentResponse = false;
                        let skipWelcomeMessage = false;
                        
                        // Process messages
                        data.messages.forEach(msg => {
                            // Skip the agent request message completely
                            if (msg.content === "I'd like to chat with a live agent please.") {
                                skipWelcomeMessage = true;
                                isConnectedToAgent = true;
                                return;
                            }
                            
                            // Determine sender type based on sender_type from PHP
                            let sender;
                            if (msg.sender_type === 'user') {
                                sender = 'user';
                            } else if (msg.sender_type === 'salon') {
                                sender = 'agent';
                                foundAgentResponse = true;
                                isConnectedToAgent = true;
                                skipWelcomeMessage = true;
                            } else {
                                return; // Skip unknown sender types
                            }
                            
                            // Format timestamp with timezone consideration
                            const msgDate = new Date(msg.created_at);
                            // Use local time display
                            const timestamp = msgDate.toLocaleTimeString('en-US', { 
                                hour: 'numeric', 
                                minute: '2-digit',
                                hour12: true,
                                timeZone: 'Asia/Manila' // Set to Philippines timezone
                            });
                            
                            addMessage(msg.content, sender, null, timestamp);
                            lastMessageId = Math.max(lastMessageId, msg.id);
                        });
                        
                        // Add welcome message only if no messages exist
                        if (chatMessages.children.length === 0 && !skipWelcomeMessage) {
                            addMessage('Welcome to Nail Architect! How can I help you today?', 'bot');
                        }
                        
                        // Update agent button if connected
                        if (isConnectedToAgent || foundAgentResponse) {
                            chatWithAgentBtn.textContent = "Connected to Agent";
                            chatWithAgentBtn.classList.add('connected');
                            chatWithAgentBtn.disabled = true;
                            
                            // Increase polling frequency
                            if (pollingInterval) {
                                clearInterval(pollingInterval);
                            }
                            pollingInterval = setInterval(checkForNewMessages, 2000);
                        }
                    } else {
                        // Add welcome message if no messages
                        addMessage('Welcome to Nail Architect! How can I help you today?', 'bot');
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    addMessage('Welcome to Nail Architect! How can I help you today?', 'bot');
                });
        }

        // Check for new messages
        function checkForNewMessages() {
            if (!currentUserId || chatContainer.classList.contains('hidden')) {
                return;
            }

            fetch('chat.php?action=get_messages')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Find new messages
                        const newMessages = data.messages.filter(msg => msg.id > lastMessageId);
                        
                        newMessages.forEach(msg => {
                            // Skip the agent request message
                            if (msg.content === "I'd like to chat with a live agent please.") {
                                lastMessageId = Math.max(lastMessageId, msg.id);
                                return;
                            }
                            
                            // Only show new salon/agent messages
                            if (msg.sender_type === 'salon') {
                                const msgDate = new Date(msg.created_at);
                                const timestamp = msgDate.toLocaleTimeString('en-US', { 
                                    hour: 'numeric', 
                                    minute: '2-digit',
                                    hour12: true 
                                });
                                
                                addMessage(msg.content, 'agent', null, timestamp);
                                isConnectedToAgent = true;
                                
                                // Update agent button
                                chatWithAgentBtn.textContent = "Connected to Agent";
                                chatWithAgentBtn.classList.add('connected');
                                chatWithAgentBtn.disabled = true;
                            }
                            // Skip user's own messages (already displayed when sent)
                            
                            lastMessageId = Math.max(lastMessageId, msg.id);
                        });
                        
                        // Update unread count if chat is closed
                        if (chatContainer.classList.contains('hidden')) {
                            const unreadMsgs = data.messages.filter(msg => 
                                msg.sender_type === 'salon' && msg.read_status === 0
                            );
                            if (unreadMsgs.length > 0) {
                                unreadCount.textContent = unreadMsgs.length;
                                unreadCount.classList.remove('hidden');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking for new messages:', error);
                });
        }

        // Mark messages as read
        function markMessagesAsRead() {
            if (!currentUserId) return;

            fetch('chat.php?action=get_messages')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mark each unread message as read
                        data.messages.forEach(msg => {
                            if (msg.read_status === 0 && msg.sender_id === null) {
                                const formData = new FormData();
                                formData.append('action', 'mark_read');
                                formData.append('message_id', msg.id);
                                
                                fetch('chat.php', {
                                    method: 'POST',
                                    body: formData
                                });
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error marking messages as read:', error);
                });
        }

        // Start polling for new messages
        function startPolling() {
            // Poll every 5 seconds when not connected to agent
            pollingInterval = setInterval(() => {
                if (!chatContainer.classList.contains('hidden')) {
                    checkForNewMessages();
                }
            }, isConnectedToAgent ? 2000 : 5000);
        }

        // Stop polling
        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        // Save messages to localStorage (for guests)
        function saveMessagesToLocalStorage() {
            const messages = [];
            const messageElements = chatMessages.querySelectorAll('.chat-message');

            messageElements.forEach(element => {
                const sender = element.classList.contains('user') ? 'user' : 
                             element.classList.contains('agent') ? 'agent' : 'bot';
                const text = element.querySelector('.chat-bubble').textContent.trim();
                messages.push({ sender, text });
            });

            localStorage.setItem('nailArchitectChatMessages', JSON.stringify(messages));
        }

        // Load messages from localStorage (for guests)
        function loadMessagesFromLocalStorage() {
            const savedMessages = localStorage.getItem('nailArchitectChatMessages');

            if (savedMessages) {
                const messages = JSON.parse(savedMessages);

                // Clear default welcome message
                chatMessages.innerHTML = '';

                // Add saved messages
                messages.forEach(msg => {
                    addMessage(msg.text, msg.sender);
                });

                // Scroll to bottom
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Clear Chat History button
        clearChatBtn.addEventListener('click', function() {
            // Show confirmation before clearing
            const confirmClear = confirm("Are you sure you want to clear your chat history?");

            if (confirmClear) {
                // Close the hamburger menu
                quickQuestionsMenu.classList.add('hidden');

                // Clear messages
                chatMessages.innerHTML = '';

                // Add welcome message
                addMessage('Welcome to Nail Architect! How can I help you today?', 'bot');

                // Clear localStorage
                localStorage.removeItem('nailArchitectChatMessages');
                
                // Reset agent connection
                isConnectedToAgent = false;
                chatWithAgentBtn.textContent = "Chat with Live Agent";
                chatWithAgentBtn.classList.remove('connected');
                chatWithAgentBtn.disabled = false;
                lastMessageId = 0;

                // Reset polling interval
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                }
                startPolling();

                // Confirmation message
                const confirmation = document.createElement('div');
                confirmation.classList.add('chat-notification');
                confirmation.textContent = 'Chat history cleared';
                chatMessages.appendChild(confirmation);
                chatMessages.scrollTop = chatMessages.scrollHeight;

                // Remove confirmation after 3 seconds
                setTimeout(() => {
                    if (confirmation.parentNode === chatMessages) {
                        chatMessages.removeChild(confirmation);
                    }
                }, 3000);
            }
        });

        // Send message on click
        chatSend.addEventListener('click', sendMessage);

        // Send message on Enter key
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Function to check if user is logged out
        function checkUserLoggedOut() {
            window.clearChatOnLogout = function() {
                // Stop polling
                stopPolling();

                // Clear all messages
                chatMessages.innerHTML = '';

                // Add welcome message
                addMessage('Welcome to Nail Architect! How can I help you today?', 'bot');

                // Clear localStorage
                localStorage.removeItem('nailArchitectChatMessages');
                
                // Reset variables
                currentUserId = null;
                currentUserName = 'Guest';
                isConnectedToAgent = false;
                chatWithAgentBtn.textContent = "Chat with Live Agent";
                chatWithAgentBtn.classList.remove('connected');
                chatWithAgentBtn.disabled = false;
                lastMessageId = 0;
            };
        }

        // Initialize logout detection
        checkUserLoggedOut();

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopPolling();
        });
    });
</script>
</body>

</html>