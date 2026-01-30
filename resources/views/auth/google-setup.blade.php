<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth Setup - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .step-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .step-card:hover {
            border-color: #dc3545;
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
        }
        .step-number {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .btn-copy {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="setup-card">
                    <div class="setup-header">
                        <i class="fab fa-google fa-3x mb-3"></i>
                        <h2 class="mb-0">Google OAuth Setup</h2>
                        <p class="mb-0 opacity-75">Enable Free Gmail Login for Your POS System</p>
                    </div>
                    
                    <div class="p-4">
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Follow these steps to enable Gmail login for your POS system.</strong>
                            This will allow anyone with a Gmail account to access your system for free!
                        </div>

                        <!-- Step 1 -->
                        <div class="step-card">
                            <div class="d-flex align-items-start">
                                <div class="step-number">1</div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">Create Google Cloud Project</h5>
                                    <p class="mb-3">Visit Google Cloud Console and create a new project for your POS system.</p>
                                    <a href="https://console.cloud.google.com/" target="_blank" class="btn btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-2"></i>Open Google Cloud Console
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="step-card">
                            <div class="d-flex align-items-start">
                                <div class="step-number">2</div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">Enable Required APIs</h5>
                                    <p class="mb-2">Enable these APIs in your Google Cloud project:</p>
                                    <ul class="mb-3">
                                        <li><strong>Google+ API</strong> (for user authentication)</li>
                                        <li><strong>Google People API</strong> (for profile information)</li>
                                    </ul>
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Go to "APIs & Services" > "Library" and search for these APIs
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="step-card">
                            <div class="d-flex align-items-start">
                                <div class="step-number">3</div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">Configure OAuth Consent Screen</h5>
                                    <p class="mb-2">Set up the OAuth consent screen with these details:</p>
                                    <ul class="mb-3">
                                        <li><strong>App name:</strong> POS System</li>
                                        <li><strong>User type:</strong> External</li>
                                        <li><strong>Scopes:</strong> email, profile, openid</li>
                                    </ul>
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Go to "APIs & Services" > "OAuth consent screen"
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="step-card">
                            <div class="d-flex align-items-start">
                                <div class="step-number">4</div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">Create OAuth Credentials</h5>
                                    <p class="mb-2">Create OAuth 2.0 Client ID with this redirect URI:</p>
                                    <div class="position-relative">
                                        <div class="code-block">
                                            <code id="redirectUri">{{ url('/auth/google/callback') }}</code>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary btn-copy" onclick="copyToClipboard('redirectUri')">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Go to "APIs & Services" > "Credentials" > "Create Credentials" > "OAuth 2.0 Client IDs"
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5 -->
                        <div class="step-card">
                            <div class="d-flex align-items-start">
                                <div class="step-number">5</div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">Add Credentials to Your .env File</h5>
                                    <p class="mb-2">Copy your Client ID and Client Secret, then add them to your .env file:</p>
                                    <div class="position-relative">
                                        <div class="code-block">
<code id="envConfig">GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URL={{ url('/auth/google/callback') }}</code>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary btn-copy" onclick="copyToClipboard('envConfig')">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6 -->
                        <div class="step-card">
                            <div class="d-flex align-items-start">
                                <div class="step-number">6</div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2">Clear Configuration Cache</h5>
                                    <p class="mb-2">Run this command in your terminal:</p>
                                    <div class="position-relative">
                                        <div class="code-block">
                                            <code id="clearCommand">php artisan config:clear</code>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary btn-copy" onclick="copyToClipboard('clearCommand')">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('login') }}" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-2"></i>Test Gmail Login
                            </a>
                            <p class="text-muted mt-2">
                                <small>After completing the setup, test the Gmail login functionality</small>
                            </p>
                        </div>

                        <div class="alert alert-success mt-4" role="alert">
                            <i class="fas fa-rocket me-2"></i>
                            <strong>Once configured, users can:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Login with any Gmail account instantly</li>
                                <li>No registration or password required</li>
                                <li>Automatic account creation</li>
                                <li>Profile picture sync from Google</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            navigator.clipboard.writeText(text).then(function() {
                // Show success feedback
                const button = element.parentElement.querySelector('.btn-copy');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                button.classList.remove('btn-outline-secondary');
                button.classList.add('btn-success');
                
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }, 2000);
            });
        }
    </script>
</body>
</html>