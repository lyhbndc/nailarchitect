<?php
// This file contains the chat widget code for Nail Architect
// Include this file before the closing </body> tag on all pages
?>

<!-- Chat Widget HTML -->
<div id="chat-widget">
    <div id="chat-button">
        <i class="fa fa-comments"></i>
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
        /* Adjusted height for hamburger menu */
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

        // Get Chat with Agent and Clear Chat buttons
        const chatWithAgentBtn = document.getElementById('chat-with-agent');
        const clearChatBtn = document.getElementById('clear-chat');

        // Get hamburger menu elements
        const hamburgerIcon = document.querySelector('.hamburger-icon');
        const quickQuestionsMenu = document.getElementById('quick-questions');

        // Load saved messages from localStorage
        loadMessages();

        // Toggle chat container
        chatButton.addEventListener('click', function() {
            chatContainer.classList.toggle('hidden');
            // Save chat state to localStorage
            localStorage.setItem('nailArchitectChatOpen', !chatContainer.classList.contains('hidden'));
        });

        // Minimize chat
        chatMinimize.addEventListener('click', function() {
            chatContainer.classList.add('hidden');
            // Save chat state to localStorage
            localStorage.setItem('nailArchitectChatOpen', false);
        });

        // Close chat
        chatClose.addEventListener('click', function() {
            chatContainer.classList.add('hidden');
            // Save chat state to localStorage
            localStorage.setItem('nailArchitectChatOpen', false);
        });

        // Add event listeners to quick buttons
        quickButtons.forEach(button => {
            button.addEventListener('click', function() {
                const question = this.getAttribute('data-question');
                handleQuickQuestion(question);
            });
        });

        // Handle quick question
        function handleQuickQuestion(question) {
            // Add user question to chat
            addMessage(question, 'user');

            // Get response for this question
            const response = quickQuestionMap[question];

            // Add bot response after a short delay
            setTimeout(() => {
                addMessage(response, 'bot');
            }, 500);
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

        // Send message function
        function sendMessage() {
            const message = chatInput.value.trim();
            if (message === '') return;

            // Add user message
            addMessage(message, 'user');
            chatInput.value = '';

            // Process response (after a small delay to simulate thinking)
            setTimeout(() => {
                processResponse(message);
            }, 500);
        }

        // Process bot response
        function processResponse(message) {
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
        function addMessage(message, sender) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('chat-message', sender);

            const avatarElement = document.createElement('div');
            avatarElement.classList.add('chat-avatar');
            avatarElement.textContent = sender === 'user' ? 'You' : 'NA';

            const bubbleElement = document.createElement('div');
            bubbleElement.classList.add('chat-bubble');
            bubbleElement.textContent = message;

            messageElement.appendChild(avatarElement);
            messageElement.appendChild(bubbleElement);

            chatMessages.appendChild(messageElement);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Save messages to localStorage
            saveMessages();
        }

        // Save messages to localStorage
        function saveMessages() {
            const messages = [];
            const messageElements = chatMessages.querySelectorAll('.chat-message');

            messageElements.forEach(element => {
                const sender = element.classList.contains('user') ? 'user' : 'bot';
                const text = element.querySelector('.chat-bubble').textContent.trim();
                messages.push({
                    sender,
                    text
                });
            });

            localStorage.setItem('nailArchitectChatMessages', JSON.stringify(messages));
        }

        // Load messages from localStorage
        function loadMessages() {
            const savedMessages = localStorage.getItem('nailArchitectChatMessages');

            if (savedMessages) {
                const messages = JSON.parse(savedMessages);

                // Clear default welcome message
                chatMessages.innerHTML = '';

                // Add saved messages
                messages.forEach(msg => {
                    const messageElement = document.createElement('div');
                    messageElement.classList.add('chat-message', msg.sender);

                    const avatarElement = document.createElement('div');
                    avatarElement.classList.add('chat-avatar');
                    avatarElement.textContent = msg.sender === 'user' ? 'You' : 'NA';

                    const bubbleElement = document.createElement('div');
                    bubbleElement.classList.add('chat-bubble');
                    bubbleElement.textContent = msg.text;

                    messageElement.appendChild(avatarElement);
                    messageElement.appendChild(bubbleElement);

                    chatMessages.appendChild(messageElement);
                });

                // Scroll to bottom
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Chat with Live Agent button
        chatWithAgentBtn.addEventListener('click', function() {
            // Add user message requesting live agent
            addMessage("I'd like to chat with a live agent please.", 'user');

            // Add response after short delay
            setTimeout(() => {
                addMessage("Connecting you with a live agent. Please wait a moment while we transfer you to the next available specialist.", 'bot');

                // Simulate agent connection after 2 seconds
                setTimeout(() => {
                    addMessage("Hello, this is Maria, a nail specialist at Nail Architect. How can I assist you today?", 'bot');

                    // Update the agent avatar to show it's a live person
                    const lastMessage = chatMessages.lastElementChild;
                    const avatar = lastMessage.querySelector('.chat-avatar');
                    if (avatar) {
                        avatar.textContent = 'MA';
                        avatar.style.backgroundColor = '#9b59b6';
                    }

                    // Optional: Display a notification that this is now a live chat
                    const notification = document.createElement('div');
                    notification.classList.add('chat-notification');
                    notification.textContent = '✓ Connected to live agent';
                    chatMessages.appendChild(notification);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 2000);
            }, 500);
        });

        // Clear Chat History button
        clearChatBtn.addEventListener('click', function() {
            // Show confirmation before clearing
            const confirmClear = confirm("Are you sure you want to clear your chat history?");

            if (confirmClear) {
                // Close the hamburger menu
                quickQuestionsMenu.classList.add('hidden');

                // Clear all messages except the welcome message
                chatMessages.innerHTML = '';

                // Add welcome message
                const welcomeMessage = document.createElement('div');
                welcomeMessage.classList.add('chat-message', 'bot');

                const welcomeAvatar = document.createElement('div');
                welcomeAvatar.classList.add('chat-avatar');
                welcomeAvatar.textContent = 'NA';

                const welcomeBubble = document.createElement('div');
                welcomeBubble.classList.add('chat-bubble');
                welcomeBubble.textContent = 'Welcome to Nail Architect! How can I help you today?';

                welcomeMessage.appendChild(welcomeAvatar);
                welcomeMessage.appendChild(welcomeBubble);

                chatMessages.appendChild(welcomeMessage);

                // Clear localStorage
                localStorage.removeItem('nailArchitectChatMessages');

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
            // This is a placeholder - replace with your actual logout detection method
            // For example, you might check a specific cookie or localStorage item

            // For demonstration, we'll add a global function that your logout process can call
            window.clearChatOnLogout = function() {
                // Clear all messages
                chatMessages.innerHTML = '';

                // Add welcome message
                const welcomeMessage = document.createElement('div');
                welcomeMessage.classList.add('chat-message', 'bot');

                const welcomeAvatar = document.createElement('div');
                welcomeAvatar.classList.add('chat-avatar');
                welcomeAvatar.textContent = 'NA';

                const welcomeBubble = document.createElement('div');
                welcomeBubble.classList.add('chat-bubble');
                welcomeBubble.textContent = 'Welcome to Nail Architect! How can I help you today?';

                welcomeMessage.appendChild(welcomeAvatar);
                welcomeMessage.appendChild(welcomeBubble);

                chatMessages.appendChild(welcomeMessage);

                // Clear localStorage
                localStorage.removeItem('nailArchitectChatMessages');
            };

            // You can call window.clearChatOnLogout() from your logout function
        }

        // Initialize logout detection
        checkUserLoggedOut();
    });
</script>