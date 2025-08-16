<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>QR Code Scanner</title>
    <style>
    body {
        margin: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background: black;
        font-family: Arial, sans-serif;
    }

    #scanner-container {
        position: relative;
        width: 100%;
        max-width: 800px;
        height: 800px;
        border: 2px solid #fff;
        border-radius: 10px;
        overflow: hidden;
    }

    video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 700px;
        height: 700px;
        border: 2px solid #00ff00;
        border-radius: 10px;
        pointer-events: none;
    }

    #status {
        color: white;
        margin-top: 20px;
        text-align: center;
        font-size: 18px;
    }

    canvas {
        display: none;
    }
    </style>
</head>

<body>
    <div id="scanner-container">
        <video id="camera" autoplay playsinline></video>
        <div id="overlay"></div>
    </div>
    <div id="status">Point your camera at a QR code</div>
    <canvas id="canvas"></canvas>

    <script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>
    <script>
    let qrScanner;
    let isScanning = false;

    async function startCamera() {
        try {
            const video = document.getElementById('camera');
            const canvas = document.getElementById('canvas');
            const status = document.getElementById('status');

            // Initialize QR Scanner
            qrScanner = new QrScanner(
                video,
                result => {
                    if (!isScanning) {
                        isScanning = true;
                        status.textContent = 'QR Code detected! Redirecting...';

                        // Stop the scanner
                        qrScanner.stop();

                        // Redirect to results page with QR data
                        setTimeout(() => {
                            window.location.href =
                                `result.html?data=${encodeURIComponent(result.data)}`;
                        }, 1000);
                    }
                }, {
                    returnDetailedScanResult: true,
                    highlightScanRegion: false,
                    highlightCodeOutline: false,
                }
            );

            // Start scanning
            await qrScanner.start();
            status.textContent = 'Camera ready - Point at a QR code';

        } catch (err) {
            document.getElementById('status').textContent = "Camera access denied or not available: " + err.message;
            console.error(err);
        }
    }

    // Start when page loads
    window.addEventListener('load', startCamera);

    // Clean up when page unloads
    window.addEventListener('beforeunload', () => {
        if (qrScanner) {
            qrScanner.destroy();
        }
    });
    </script>

</body>

</html>