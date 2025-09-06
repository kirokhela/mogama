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

    .loading {
        color: #ffff00;
        font-weight: bold;
    }

    .error {
        color: #ff4444;
        font-weight: bold;
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

    // Function to extract ID from QR code data
    function extractEmployeeId(qrData) {
        try {
            // Try different patterns to extract ID
            const patterns = [
                /ID:\s*([^,\n]+)/i, // ID: 123
                /Employee\s*ID:\s*([^,\n]+)/i, // Employee ID: 123
                /^(\d+)$/, // Just a number
                /"?id"?\s*:\s*"?([^",\n]+)"?/i // JSON-like: "id": "123"
            ];

            for (let pattern of patterns) {
                const match = qrData.match(pattern);
                if (match && match[1]) {
                    return match[1].trim();
                }
            }

            // If no pattern matches, check if the entire string is just an ID
            const trimmed = qrData.trim();
            if (/^\d+$/.test(trimmed)) {
                return trimmed;
            }

            return null;
        } catch (error) {
            console.error('Error extracting ID:', error);
            return null;
        }
    }

    // Function to fetch employee data from database
    async function fetchEmployeeData(employeeId) {
        try {
            const response = await fetch('get_employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id: employeeId
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching employee data:', error);
            throw error;
        }
    }

    async function startCamera() {
        try {
            const video = document.getElementById('camera');
            const status = document.getElementById('status');

            // Initialize QR Scanner
            qrScanner = new QrScanner(
                video,
                async result => {
                    if (!isScanning) {
                        isScanning = true;
                        status.className = 'loading';
                        status.textContent = 'Processing QR Code...';

                        try {
                            // Extract employee ID from QR data
                            const employeeId = extractEmployeeId(result.data);

                            if (!employeeId) {
                                throw new Error('Could not extract employee ID from QR code');
                            }

                            status.textContent = `Found ID: ${employeeId}. Fetching data...`;

                            // Fetch employee data from database
                            const employeeData = await fetchEmployeeData(employeeId);

                            if (employeeData.success) {
                                // Stop the scanner
                                qrScanner.stop();

                                status.textContent = 'Data loaded! Redirecting...';

                                // Redirect to results page with employee data
                                setTimeout(() => {
                                    const params = new URLSearchParams({
                                        id: employeeData.employee.id,
                                        name: employeeData.employee.name,
                                        payment: employeeData.employee.payment,
                                        team: employeeData.employee.team
                                    });
                                    window.location.href = `result.html?${params.toString()}`;
                                }, 1000);
                            } else {
                                throw new Error(employeeData.message || 'Employee not found in database');
                            }

                        } catch (error) {
                            status.className = 'error';
                            status.textContent = `Error: ${error.message}`;

                            // Reset scanning after 3 seconds
                            setTimeout(() => {
                                isScanning = false;
                                status.className = '';
                                status.textContent = 'Point your camera at a QR code';
                            }, 3000);
                        }
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
            document.getElementById('status').className = 'error';
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