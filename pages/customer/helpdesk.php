<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require '../../database/connection.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../customer/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Helpdesk</title>

    <link rel="stylesheet" href="../../assets/css/pages/customer/helpdesk.css" />
    <script src="../../assets/js/components/layer.js" defer></script>

    <style>
        .chatbox {
          width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1.5rem;
        }
        .message {
            max-width: 80%;
            padding: .75rem 0.5rem;
            border-radius: 1rem;
            line-height: 1.4;
        }
        .bot {
            background: var(--neutral-200);
            align-self: flex-start;
        }
        .user {
            background: var(--color-primary);
            color: var(--neutral-100);
            align-self: flex-end;
        }
        .chat-input {
          width: 100%;
            display: flex;
            justify-content: space-between;
            gap: .5rem;
            padding: 1rem;
            background: var(--neutral-200);

            & input[type="text"] {
              height: 100%;
              flex: 1;
              font-weight: 800;
              font-family: var(--font-sans);
              line-height: 1;
              font-size: var(--size-base);
              color: var(--neutral-800);
              outline: none;
              border: none;
              padding: var(--pad-sm);

              &::placeholder {
                color: var(--neutral-400);
                font-weight: 400;
              }
            }
        }
        .suggested {
            display: flex;
            gap: .5rem;
            padding: 1rem;
            flex-wrap: wrap;
        }
        .btn-faq {
            background: var(--color-secondary);
            padding: var(--pad-sm) var(--pad-md);
            border-radius: .5rem;
            cursor: pointer;
            font-size: .85rem;
        }
        .btn-faq:hover {
            background: var(--color-secondary-dark);
            color: var(--neutral-100);
        }
    </style>

</head>

<body class="customer">

    <main class="page">

        <header class="header header-page">
            <div class="context">
                <h1>Helpdesk</h1>
            </div>
            <div class="actions right">
              <button
                onclick="parent.navigate('./feedback.php')"
                class="btn btn-primary subnav"
              >
                <span class="btn-label">Feedback</span>
                <i class="bx bxs-star btn-icon"></i>
              </button>
            </div>
        </header>

        <main class="main-container main-scrollable">
            <main class="main">

                <!-- Suggested Questions -->
                <div class="suggested">
                    <div class="btn-faq" onclick="sendFAQ('How do I track my order?')">
                        How do I track my order?
                    </div>
                    <div class="btn-faq" onclick="sendFAQ('How long is delivery time?')">
                        How long is delivery time?
                    </div>
                    <div class="btn-faq" onclick="sendFAQ('How can I contact the branch?')">
                        How can I contact the branch?
                    </div>
                </div>

                <!-- Chat Container -->
                <div id="chatbox" class="chatbox">
                    <div class="message bot">
                        Hello! How can we assist you today?
                    </div>
                </div>

            </main>
        </main>

        <!-- Chat Input -->
        <div class="chat-input">
            <input 
                type="text" 
                id="userInput" 
                placeholder="Type your message..." 
                class="input full"
                onkeypress="if(event.key === 'Enter') sendMessage()"
            >
            <button class="btn btn-primary" onclick="sendMessage()"><span class="btn-label">Send</span><i class="bx bxs-paper-plane btn-icon"></i></button>
        </div>

    </main>

<script>
    const chatbox = document.getElementById("chatbox");

    const answers = {
        "How do I track my order?": 
            "You can track all your orders in the *Activity* page. It shows each order's real-time progress until it is received.",
        
        "How long is delivery time?": 
            "Delivery usually takes **20-45 minutes**, depending on the branch load and distance.",
        
        "How can I contact the branch?": 
            "You may contact the branch by opening your order receipt. The branch phone number is displayed there."
    };

    function addMessage(text, sender) {
        let div = document.createElement("div");
        div.className = `message ${sender}`;
        div.innerHTML = text;
        chatbox.appendChild(div);
        chatbox.scrollTop = chatbox.scrollHeight;
    }

    function sendMessage() {
        const input = document.getElementById("userInput");
        const text = input.value.trim();
        if (!text) return;

        addMessage(text, "user");
        input.value = "";

        setTimeout(() => {
            respond(text);
        }, 300);
    }

    function sendFAQ(question) {
        addMessage(question, "user");
        setTimeout(() => respond(question), 300);
    }

    function respond(query) {
        let reply = answers[query] 
            || "I'm sorry, I can only answer a few questions for now! Please try one of the suggested questions above.";

        addMessage(reply, "bot");
    }
</script>

</body>
</html>
