@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-cog me-2"></i>System Configuration
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fab fa-telegram-plane fa-3x text-primary mb-3"></i>
                                <h5>Telegram Integration</h5>
                                <p class="text-muted">Configure Telegram bot for payment notifications and alerts</p>
                                <a href="{{ route('telegram.settings') }}" class="btn btn-primary">
                                    <i class="fas fa-cog me-2"></i>Configure Telegram
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-store fa-3x text-info mb-3"></i>
                                <h5>Store Settings</h5>
                                <p class="text-muted">Configure store information, tax rates, and business details</p>
                                <a href="{{ route('admin.store-settings') }}" class="btn btn-info">
                                    <i class="fas fa-cog me-2"></i>Configure Store
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-success mb-3"></i>
                                <h5>User Management</h5>
                                <p class="text-muted">Manage user accounts, roles, and permissions</p>
                                <button class="btn btn-success" disabled>
                                    <i class="fas fa-clock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-3x text-warning mb-3"></i>
                                <h5>Backup & Restore</h5>
                                <p class="text-muted">Backup your data and restore from previous backups</p>
                                <button class="btn btn-warning" disabled>
                                    <i class="fas fa-clock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection