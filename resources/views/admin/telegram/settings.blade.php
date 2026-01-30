@extends('layouts.app')

@section('title', 'Telegram Settings')
@section('page-title', 'Telegram Bot Settings')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fab fa-telegram-plane me-2"></i>Telegram Bot Configuration
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Setup Instructions:</h6>
                    <ol class="mb-0">
                        <li>Create a new bot by messaging <strong>@BotFather</strong> on Telegram</li>
                        <li>Use the command <code>/newbot</code> and follow the instructions</li>
                        <li>Copy the bot token and paste it in your <code>.env</code> file</li>
                        <li>Get your chat ID by messaging <strong>@userinfobot</strong> or your group chat ID</li>
                        <li>Update the <code>.env</code> file with your configuration</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Bot Token</label>
                            <input type="text" class="form-control" value="{{ $botToken ? '***' . substr($botToken, -10) : 'Not configured' }}" readonly>
                            <small class="form-text text-muted">Configure in .env file: TELEGRAM_BOT_TOKEN</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Chat ID</label>
                            <input type="text" class="form-control" value="{{ $chatId ?: 'Not configured' }}" readonly>
                            <small class="form-text text-muted">Configure in .env file: TELEGRAM_CHAT_ID</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Current Configuration Status:</h6>
                    <div class="d-flex align-items-center">
                        @if($botToken && $chatId)
                            <span class="badge bg-success me-2">
                                <i class="fas fa-check-circle me-1"></i>Configured
                            </span>
                            <span class="text-success">Telegram bot is ready to use</span>
                        @else
                            <span class="badge bg-warning me-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>Not Configured
                            </span>
                            <span class="text-warning">Please configure bot token and chat ID</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2">
                    @if($botToken)
                        @if(!$chatId || $chatId === 'your_chat_id_here')
                            <button type="button" class="btn btn-warning" id="getChatIdBtn">
                                <i class="fas fa-search me-2"></i>Get Chat ID
                            </button>
                        @endif
                        
                        @if($botToken && $chatId && $chatId !== 'your_chat_id_here')
                            <form action="{{ route('telegram.test') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Test Connection
                                </button>
                            </form>
                            
                            <form action="{{ route('telegram.daily-report') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-chart-bar me-2"></i>Send Daily Report
                                </button>
                            </form>
                        @endif
                    @else
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="fas fa-cog me-2"></i>Configure Bot Token First
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bell me-2"></i>Notification Features
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <i class="fas fa-cash-register text-success me-2"></i>
                            <strong>Payment Reports</strong>
                            <br><small class="text-muted">Automatic notifications for each sale</small>
                        </div>
                        <span class="badge bg-success">Active</span>
                    </div>
                    
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <strong>Low Stock Alerts</strong>
                            <br><small class="text-muted">Alerts when products run low</small>
                        </div>
                        <span class="badge bg-success">Active</span>
                    </div>
                    
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <i class="fas fa-chart-line text-info me-2"></i>
                            <strong>Daily Reports</strong>
                            <br><small class="text-muted">End-of-day sales summary</small>
                        </div>
                        <span class="badge bg-info">Manual</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-code me-2"></i>Sample .env Configuration
                </h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded"><code># Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_CHAT_ID=123456789</code></pre>
                <small class="text-muted">
                    Replace with your actual bot token and chat ID
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Chat ID Modal -->
<div class="modal fade" id="chatIdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Get Chat ID</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="chatIdContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Checking for recent messages...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#getChatIdBtn').click(function() {
        $('#chatIdModal').modal('show');
        getChatId();
    });
    
    function getChatId() {
        $.get('{{ route("telegram.get-chat-id") }}')
            .done(function(response) {
                if (response.success) {
                    let content = '<h6>Available Chats:</h6>';
                    
                    if (response.chats.length === 1) {
                        const chat = response.chats[0];
                        content += `
                            <div class="alert alert-success">
                                <strong>Chat Found!</strong><br>
                                <strong>Chat ID:</strong> ${chat.id}<br>
                                <strong>Type:</strong> ${chat.type}<br>
                                <strong>Name:</strong> ${chat.title}
                            </div>
                            <form action="{{ route('telegram.update-chat-id') }}" method="POST">
                                @csrf
                                <input type="hidden" name="chat_id" value="${chat.id}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Use This Chat ID
                                </button>
                            </form>
                        `;
                    } else {
                        content += '<div class="list-group">';
                        response.chats.forEach(function(chat) {
                            content += `
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Chat ID:</strong> ${chat.id}<br>
                                            <strong>Type:</strong> ${chat.type}<br>
                                            <strong>Name:</strong> ${chat.title}
                                        </div>
                                        <form action="{{ route('telegram.update-chat-id') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="chat_id" value="${chat.id}">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                Use This
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            `;
                        });
                        content += '</div>';
                    }
                    
                    $('#chatIdContent').html(content);
                } else {
                    let content = `
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>No Messages Found</h6>
                            <p>${response.message}</p>
                            <ol>
                                <li>Open Telegram and find your bot</li>
                                <li>Send any message to your bot (e.g., "Hello")</li>
                                <li>Come back and click "Get Chat ID" again</li>
                            </ol>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="getChatId()">
                            <i class="fas fa-refresh me-2"></i>Try Again
                        </button>
                    `;
                    $('#chatIdContent').html(content);
                }
            })
            .fail(function() {
                $('#chatIdContent').html(`
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-times-circle me-2"></i>Error</h6>
                        <p>Failed to get chat ID. Please check your bot token and try again.</p>
                    </div>
                `);
            });
    }
});
</script>
@endpush
@endsection