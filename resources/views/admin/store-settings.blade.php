@extends('layouts.app')

@section('title', 'Store Settings')
@section('page-title', 'Store Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-store me-2"></i>Store Configuration
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.store-settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Store Branding Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-palette me-2"></i>Store Branding
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_name" class="form-label">Store Name *</label>
                                <input type="text" class="form-control @error('store_name') is-invalid @enderror" 
                                       id="store_name" name="store_name" 
                                       value="{{ old('store_name', $settings['store_name'] ?? 'Mini Mart POS') }}" required>
                                @error('store_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_tagline" class="form-label">Store Tagline</label>
                                <input type="text" class="form-control @error('store_tagline') is-invalid @enderror" 
                                       id="store_tagline" name="store_tagline" 
                                       value="{{ old('store_tagline', $settings['store_tagline'] ?? '') }}"
                                       placeholder="Your Friendly Neighborhood Store">
                                @error('store_tagline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Logo Upload -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_logo" class="form-label">Store Logo</label>
                                <input type="file" class="form-control @error('store_logo') is-invalid @enderror" 
                                       id="store_logo" name="store_logo" accept="image/*">
                                <small class="form-text text-muted">Recommended size: 200x200px. Max: 2MB</small>
                                @error('store_logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                @if(isset($settings['store_logo']) && $settings['store_logo'])
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $settings['store_logo']) }}" 
                                             alt="Current Logo" class="img-thumbnail" style="max-height: 100px;">
                                        <button type="button" class="btn btn-sm btn-danger ms-2" 
                                                onclick="deleteImage('logo')">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Banner Upload -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_banner" class="form-label">Store Banner</label>
                                <input type="file" class="form-control @error('store_banner') is-invalid @enderror" 
                                       id="store_banner" name="store_banner" accept="image/*">
                                <small class="form-text text-muted">Recommended size: 1200x300px. Max: 2MB</small>
                                @error('store_banner')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                @if(isset($settings['store_banner']) && $settings['store_banner'])
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $settings['store_banner']) }}" 
                                             alt="Current Banner" class="img-thumbnail" style="max-height: 100px;">
                                        <button type="button" class="btn btn-sm btn-danger ms-2" 
                                                onclick="deleteImage('banner')">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Store Information Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>Store Information
                            </h6>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="store_address" class="form-label">Store Address</label>
                                <textarea class="form-control @error('store_address') is-invalid @enderror" 
                                          id="store_address" name="store_address" rows="3"
                                          placeholder="123 Main Street, City, State, ZIP">{{ old('store_address', $settings['store_address'] ?? '') }}</textarea>
                                @error('store_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('store_phone') is-invalid @enderror" 
                                       id="store_phone" name="store_phone" 
                                       value="{{ old('store_phone', $settings['store_phone'] ?? '') }}"
                                       placeholder="+1 (555) 123-4567">
                                @error('store_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('store_email') is-invalid @enderror" 
                                       id="store_email" name="store_email" 
                                       value="{{ old('store_email', $settings['store_email'] ?? '') }}"
                                       placeholder="store@example.com">
                                @error('store_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Business Settings Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-calculator me-2"></i>Business Settings
                            </h6>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                                <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" 
                                       id="tax_rate" name="tax_rate" step="0.01" min="0" max="100"
                                       value="{{ old('tax_rate', $settings['tax_rate'] ?? '0.00') }}"
                                       placeholder="0.00">
                                @error('tax_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                <input type="text" class="form-control @error('currency_symbol') is-invalid @enderror" 
                                       id="currency_symbol" name="currency_symbol" maxlength="5"
                                       value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '$') }}"
                                       placeholder="$">
                                @error('currency_symbol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="receipt_footer" class="form-label">Receipt Footer</label>
                                <input type="text" class="form-control @error('receipt_footer') is-invalid @enderror" 
                                       id="receipt_footer" name="receipt_footer"
                                       value="{{ old('receipt_footer', $settings['receipt_footer'] ?? '') }}"
                                       placeholder="Thank you for your business!">
                                @error('receipt_footer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.settings') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Settings
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteImage(type) {
    if (confirm('Are you sure you want to delete this image?')) {
        fetch('{{ route("admin.store-settings.delete-image") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ type: type })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete image');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}
</script>
@endpush
@endsection