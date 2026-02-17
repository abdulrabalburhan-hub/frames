<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Logo Test - AlBurhan Frames</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dark-bg {
            background: #1a202c;
            color: white;
        }
        .logo-test {
            margin: 10px 0;
            padding: 10px;
            border: 2px dashed #ccc;
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        .dark-bg h2 {
            color: white;
        }
        code {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 14px;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>📸 AlBurhan Logo Display Test</h1>
    <p>This page tests various logo display scenarios to diagnose visibility issues.</p>

    <!-- Test 1: Basic Logo -->
    <div class="test-section">
        <h2>Test 1: Basic Logo (No Filters)</h2>
        <div class="logo-test">
            <img src="../assets/images/logo.png" alt="AlBurhan Logo" style="max-height: 60px;">
        </div>
        <p><code>&lt;img src="../assets/images/logo.png" style="max-height: 60px;"&gt;</code></p>
    </div>

    <!-- Test 2: Logo with Inverted Colors (for dark backgrounds) -->
    <div class="test-section dark-bg">
        <h2>Test 2: Logo with Invert Filter (Dark Background)</h2>
        <div class="logo-test">
            <img src="../assets/images/logo.png" alt="AlBurhan Logo" style="max-height: 60px; filter: brightness(0) invert(1);">
        </div>
        <p><code>&lt;img src="../assets/images/logo.png" style="filter: brightness(0) invert(1);"&gt;</code></p>
    </div>

    <!-- Test 3: Different Sizes -->
    <div class="test-section">
        <h2>Test 3: Different Sizes</h2>
        <div class="logo-test">
            <p>35px height (navbar): <img src="../assets/images/logo.png" alt="AlBurhan" style="max-height: 35px;"></p>
            <p>40px height (default): <img src="../assets/images/logo.png" alt="AlBurhan" style="max-height: 40px;"></p>
            <p>60px height (login): <img src="../assets/images/logo.png" alt="AlBurhan" style="max-height: 60px;"></p>
            <p>100px height (large): <img src="../assets/images/logo.png" alt="AlBurhan" style="max-height: 100px;"></p>
        </div>
    </div>

    <!-- Test 4: File Path Check -->
    <div class="test-section">
        <h2>Test 4: File Path Verification</h2>
        <p>Expected path: <code>../assets/images/logo.png</code></p>
        <p>Check browser console (F12) for any 404 errors.</p>
        <p>If you see a broken image icon above, the file path is incorrect.</p>
    </div>

    <!-- Test 5: Admin Path -->
    <div class="test-section">
        <h2>Test 5: Admin Panel Path (../ prefix)</h2>
        <div class="logo-test">
            <img src="../assets/images/logo.png" alt="AlBurhan Logo" style="max-height: 60px;">
        </div>
        <p><code>&lt;img src="../assets/images/logo.png"&gt;</code></p>
        <p><em>Note: This will work if accessed from admin/ directory</em></p>
    </div>

    <!-- Test 6: Direct URL Test -->
    <div class="test-section">
        <h2>Test 6: Direct URL Access</h2>
        <p>Try accessing the logo directly:</p>
        <p><a href="../assets/images/logo.png" target="_blank">Click here to open logo in new tab</a></p>
        <p>If this fails, the file doesn't exist or permissions are wrong.</p>
    </div>

    <!-- JavaScript Check -->
    <script>
        // Check if images loaded
        window.addEventListener('load', function() {
            const images = document.querySelectorAll('img[src*="logo.png"]');
            let loadedCount = 0;
            let failedCount = 0;
            
            images.forEach(img => {
                if (img.complete && img.naturalHeight !== 0) {
                    loadedCount++;
                    img.style.border = '2px solid green';
                } else {
                    failedCount++;
                    img.style.border = '2px solid red';
                    img.onerror = function() {
                        this.alt = '❌ FAILED TO LOAD: ' + this.src;
                    };
                }
            });
            
            // Add result
            const resultDiv = document.createElement('div');
            resultDiv.className = 'test-section';
            resultDiv.innerHTML = `
                <h2>Test Results</h2>
                <p class="${loadedCount > 0 ? 'success' : 'error'}">✓ Successfully loaded: ${loadedCount} images</p>
                ${failedCount > 0 ? `<p class="error">✗ Failed to load: ${failedCount} images</p>` : ''}
                <p><strong>Diagnosis:</strong></p>
                <ul>
                    ${loadedCount === 0 ? '<li class="error">Logo file not found or path is incorrect</li>' : ''}
                    ${loadedCount > 0 && failedCount > 0 ? '<li>Some paths work, others don\'t - check relative paths</li>' : ''}
                    ${loadedCount > 0 && failedCount === 0 ? '<li class="success">All logo instances loaded successfully!</li>' : ''}
                </ul>
            `;
            document.body.appendChild(resultDiv);
        });
    </script>
</body>
</html>
