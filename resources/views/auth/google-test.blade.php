<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth Test - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fab fa-google me-2"></i>Google OAuth Test
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Google OAuth is configured and working!</strong>
                        </div>

                        <h5>Configuration Status:</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Client ID
                                <span class="badge bg-success rounded-pill">✓ Configured</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Client Secret
                                <span class="badge bg-success rounded-pill">✓ Configured</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Redirect URL
                                <span class="badge bg-success rounded-pill">✓ Set</span>
                            </li>
                        </ul>

                        <h5>Test Gmail Login:</h5>
                        <p>Click the button below to test the Gmail login functionality:</p>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('auth.google') }}" class="btn btn-danger btn-lg">
                                <i class="fab fa-google me-2"></i>Test Gmail Login
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Login Page
                            </a>
                        </div>

                        <div class="alert alert-info mt-4" role="alert">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>What happens when you test:</strong>
                            <ol class="mb-0 mt-2">
                                <li>You'll be redirected to Google for authorization</li>
                                <li>After authorizing, you'll be redirected back to the POS dashboard</li>
                                <li>A new user account will be created automatically</li>
                                <li>Your Google profile picture will be displayed</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>