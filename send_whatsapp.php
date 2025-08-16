<?php

session_start();

// Check if session variables are set
if (!isset($_SESSION['name']) || !isset($_SESSION['phone']) || !isset($_SESSION['serialNumber']) || !isset($_SESSION['qrCodeImageUrl'])) {
    echo "No data available to send.";
    exit();
}

$name = $_SESSION['name'];
$phone = preg_replace('/\D/', '', $_SESSION['phone']); // Remove non-digits
$serialNumber = $_SESSION['serialNumber'];
$qrCodeImageUrl = $_SESSION['qrCodeImageUrl'];

// Build WhatsApp message
$whatsappMessage = "Hello $name,\n\n"
    . "Thank you for registering with Shamandora Scout. Your Serial Number is: $serialNumber.\n"
    . "You can access your ticket here: $qrCodeImageUrl. Please save this number to view your ticket.\n\n"
    . "مرحباً $name،\n\n"
    . "شكراً لتسجيلك في Shamandora Scout. رقم التسلسل الخاص بك هو: $serialNumber.\n"
    . "يمكنك الوصول إلى تذكرتك هنا: $qrCodeImageUrl.\n"
    . "برجاء تسجيل رقم الهاتف المرسل منه الرساله حتي يمكنكم فتح اللينك.";

// WhatsApp application URL (works best for opening native app)
$whatsappAppUrl = "whatsapp://send?phone=$phone&text=" . urlencode($whatsappMessage);
// Fallback wa.me URL (universal fallback)
$whatsappFallbackUrl = "https://wa.me/$phone?text=" . urlencode($whatsappMessage);

// Clear session data
unset($_SESSION['name'], $_SESSION['phone'], $_SESSION['serialNumber'], $_SESSION['qrCodeImageUrl']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Send WhatsApp Message</title>
<style>
    body {
        font-family: 'Arial', sans-serif;
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        box-sizing: border-box;
    }
    .demo-page {
        background-color: #fff;
        padding: 30px 40px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        text-align: center;
        width: 100%;
        max-width: 600px;
        position: relative;
    }
    .whatsapp-logo {
        font-size: 48px;
        margin-bottom: 10px;
    }
    h2 {
        color: #25D366;
        margin-bottom: 20px;
        font-size: 24px;
    }
    .status-message {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 8px;
        font-weight: bold;
        border-left: 4px solid #25D366;
    }
    .app-status {
        background-color: #e8f5e8;
        color: #0d4f1c;
    }
    .countdown {
        font-size: 20px;
        color: #25D366;
        font-weight: bold;
        margin: 15px 0;
    }
    textarea {
        width: 100%;
        height: 200px;
        padding: 15px;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        resize: vertical;
        margin-bottom: 20px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        line-height: 1.4;
        box-sizing: border-box;
        background-color: #f8f9fa;
    }
    textarea:focus {
        border-color: #25D366;
        outline: none;
    }
    .button-container {
        margin-top: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
    }
    a.button, button {
        background-color: #25D366;
        color: #fff;
        padding: 15px 25px;
        border-radius: 25px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        display: inline-block;
        transition: all 0.3s ease;
        min-width: 160px;
        box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
    }
    a.button:hover, button:hover {
        background-color: #20b358;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
    }
    .primary-button {
        background: linear-gradient(135deg, #25D366, #20b358);
        font-size: 18px;
        padding: 18px 30px;
    }
    .copy-button {
        background-color: #2196F3;
        box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
    }
    .copy-button:hover {
        background-color: #1976D2;
        box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
    }
    .back-button {
        background-color: #f44336;
        box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
    }
    .back-button:hover {
        background-color: #e53935;
        box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
    }
    .copy-success {
        background-color: #4CAF50 !important;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3) !important;
    }
    .instructions {
        margin-top: 20px;
        padding: 15px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 8px;
        font-size: 14px;
        color: #555;
        text-align: left;
        border-left: 4px solid #25D366;
    }
    .loading-spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #25D366;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
        display: none;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .hidden {
        display: none;
    }
    @media (max-width: 480px) {
        .demo-page {
            padding: 20px 25px;
        }
        .button-container {
            flex-direction: column;
        }
        a.button, button {
            width: 100%;
            margin: 5px 0;
        }
    }
</style>
</head>
<body>

<div class="demo-page">
    <div class="whatsapp-logo">📱</div>
    <h2>WhatsApp Message Ready</h2>
    
    <div id="loadingSpinner" class="loading-spinner"></div>
    
    <div id="statusMessage" class="status-message app-status">
        <span id="statusText">Preparing to open WhatsApp application...</span>
    </div>

    <div id="countdownDisplay" class="countdown hidden">
        Opening WhatsApp in <span id="countdownNumber">5</span> seconds...
    </div>

    <!-- Message Box -->
    <textarea id="messageBox" readonly><?php echo htmlspecialchars($whatsappMessage); ?></textarea>

    <div class="button-container">
        <button id="openWhatsAppButton" class="button primary-button" onclick="openWhatsAppApp()">
            📲 Open WhatsApp Now
        </button>
        <button id="copyButton" class="copy-button" onclick="copyMessage()">
            📋 Copy Message
        </button>
        <button id="cancelAutoOpen" class="button" onclick="cancelAutoOpen()" style="display:none;">
            ⏸️ Cancel Auto-Open
        </button>
        <a href="index.php" class="button back-button">← Back</a>
    </div>

    <div id="instructions" class="instructions">
        <strong>📱 WhatsApp App Instructions:</strong><br>
        • The system will attempt to open your WhatsApp application directly<br>
        • If WhatsApp doesn't open automatically, click "Open WhatsApp Now"<br>
        • The message should be pre-filled in the chat with the contact<br>
        • If the app doesn't open, ensure WhatsApp is installed on your device<br><br>
        
        <strong>💡 Troubleshooting:</strong><br>
        • On desktop: Make sure WhatsApp Desktop is installed<br>
        • On mobile: Ensure WhatsApp is installed and updated<br>
        • If app doesn't open, copy the message and open WhatsApp manually
    </div>
</div>

<script>
    const whatsappAppUrl = "<?php echo $whatsappAppUrl; ?>";
    const whatsappFallbackUrl = "<?php echo $whatsappFallbackUrl; ?>";
    const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isDesktop = !isMobile;
    
    let copyButtonOriginalText = "📋 Copy Message";
    let autoOpenTimer;
    let countdownTimer;
    let countdownValue = 5;
    
    function initializePage() {
        const statusText = document.getElementById('statusText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const countdownDisplay = document.getElementById('countdownDisplay');
        const cancelButton = document.getElementById('cancelAutoOpen');
        
        // Show loading spinner initially
        loadingSpinner.style.display = 'block';
        
        setTimeout(() => {
            loadingSpinner.style.display = 'none';
            
            if (isMobile) {
                statusText.textContent = '📱 Mobile device detected - Ready to open WhatsApp app!';
                startAutoOpenCountdown();
            } else {
                statusText.textContent = '💻 Desktop detected - Will attempt to open WhatsApp Desktop app';
                startAutoOpenCountdown();
            }
        }, 1500);
    }
    
    function startAutoOpenCountdown() {
        const countdownDisplay = document.getElementById('countdownDisplay');
        const countdownNumber = document.getElementById('countdownNumber');
        const cancelButton = document.getElementById('cancelAutoOpen');
        
        countdownDisplay.classList.remove('hidden');
        cancelButton.style.display = 'inline-block';
        
        countdownTimer = setInterval(() => {
            countdownValue--;
            countdownNumber.textContent = countdownValue;
            
            if (countdownValue <= 0) {
                clearInterval(countdownTimer);
                countdownDisplay.classList.add('hidden');
                cancelButton.style.display = 'none';
                openWhatsAppApp();
            }
        }, 1000);
    }
    
    function cancelAutoOpen() {
        if (countdownTimer) {
            clearInterval(countdownTimer);
        }
        if (autoOpenTimer) {
            clearTimeout(autoOpenTimer);
        }
        
        const countdownDisplay = document.getElementById('countdownDisplay');
        const cancelButton = document.getElementById('cancelAutoOpen');
        const statusText = document.getElementById('statusText');
        
        countdownDisplay.classList.add('hidden');
        cancelButton.style.display = 'none';
        statusText.textContent = 'Auto-open cancelled. Click "Open WhatsApp Now" when ready.';
        countdownValue = 5; // Reset for next time
    }
    
    function openWhatsAppApp() {
        const statusText = document.getElementById('statusText');
        const openButton = document.getElementById('openWhatsAppButton');
        
        statusText.textContent = 'Opening WhatsApp application...';
        openButton.textContent = '🔄 Opening...';
        openButton.disabled = true;
        
        // First attempt: Try to open native app
        const appWindow = window.open(whatsappAppUrl, '_blank');
        
        // Fallback mechanism
        setTimeout(() => {
            // Reset button
            openButton.textContent = '📲 Open WhatsApp Now';
            openButton.disabled = false;
            
            // Check if app window was blocked or couldn't open
            if (!appWindow || appWindow.closed || typeof appWindow.closed == 'undefined') {
                statusText.textContent = 'Trying alternative method...';
                
                // Try direct location change for better app opening
                if (isMobile) {
                    window.location.href = whatsappAppUrl;
                } else {
                    // For desktop, try fallback URL
                    setTimeout(() => {
                        const fallbackWindow = window.open(whatsappFallbackUrl, '_blank');
                        if (!fallbackWindow) {
                            statusText.textContent = 'Please copy the message and open WhatsApp manually.';
                            showManualInstructions();
                        }
                    }, 1000);
                }
            } else {
                statusText.textContent = '✅ WhatsApp should now be opening with your message!';
            }
        }, 2000);
    }
    
    function showManualInstructions() {
        const instructions = document.getElementById('instructions');
        instructions.innerHTML = `
            <strong>🚨 Manual Steps Required:</strong><br>
            1. <strong>Copy the message above</strong> (click "Copy Message")<br>
            2. <strong>Open WhatsApp manually</strong> on your device<br>
            3. <strong>Find the contact:</strong> <?php echo $phone; ?><br>
            4. <strong>Paste and send</strong> the copied message<br><br>
            
            <em>This happens when the browser cannot automatically open WhatsApp.</em>
        `;
        instructions.style.backgroundColor = '#fff3cd';
        instructions.style.borderLeft = '4px solid #ffc107';
    }
    
    function copyMessage() {
        const messageBox = document.getElementById('messageBox');
        const copyButton = document.getElementById('copyButton');
        
        messageBox.select();
        messageBox.setSelectionRange(0, 99999);
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                copyButton.textContent = '✅ Copied!';
                copyButton.classList.add('copy-success');
                
                setTimeout(() => {
                    copyButton.textContent = copyButtonOriginalText;
                    copyButton.classList.remove('copy-success');
                }, 3000);
            } else {
                throw new Error('Copy command failed');
            }
        } catch (err) {
            // Fallback for modern browsers
            if (navigator.clipboard) {
                navigator.clipboard.writeText(messageBox.value).then(() => {
                    copyButton.textContent = '✅ Copied!';
                    copyButton.classList.add('copy-success');
                    
                    setTimeout(() => {
                        copyButton.textContent = copyButtonOriginalText;
                        copyButton.classList.remove('copy-success');
                    }, 3000);
                }).catch(() => {
                    alert('Copy failed. Please select the text manually and copy with Ctrl+C (or Cmd+C on Mac)');
                });
            } else {
                alert('Copy failed. Please select the text manually and copy with Ctrl+C (or Cmd+C on Mac)');
            }
        }
    }
    
    // Initialize page on load
    window.onload = initializePage;
    
    // Handle page visibility change (when user comes back from WhatsApp)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            document.getElementById('statusText').textContent = '👋 Welcome back! Did the message send successfully?';
        }
    });
</script>

</body>
</html>