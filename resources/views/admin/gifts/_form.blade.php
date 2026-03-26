@extends('admin.layouts.app')

@section('title', 'Manage Gifts')

@section('content')

@if(isset($gift))
    @php
        $route = route('admin.gifts.update', $gift);
        $method = 'post';
        $title = 'Edit Gift';
    @endphp
@else
    @php
        $route = route('admin.gifts.store');
        $method = 'POST';
        $title = 'Add New Gift';
    @endphp
@endif

<form action="{{ $route }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method($method)
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ $title }}</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $gift->name ?? '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" 
                               class="form-control @error('price') is-invalid @enderror" 
                               id="price" name="price" value="{{ old('price', $gift->price ?? '') }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3">{{ old('description', $gift->description ?? '') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="image" class="form-label">
                    {{ isset($gift) && $gift->hasMedia('gifts') ? 'Change Image' : 'Upload Image' }}
                </label>
                <input class="form-control @error('image') is-invalid @enderror" 
                       type="file" id="image" name="image" accept="image/*">
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                @if(isset($gift) && $gift->hasMedia('gifts'))
                    <div class="mt-2">
                        <img src="{{ $gift->getFirstMediaUrl('gifts') }}" 
                             alt="{{ $gift->name }}" 
                             class="img-thumbnail mt-2" 
                             style="max-width: 150px;">
                    </div>
                @endif
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class='bx bx-save'></i> Save
                </button>
                <a href="{{ route('admin.gifts.index') }}" class="btn btn-outline-secondary">
                    <i class='bx bx-arrow-back'></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection
@push('scripts')
<script>
    // Preview image before upload
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.querySelector('.image-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.className = 'mt-2';
                    document.querySelector('.mb-3').appendChild(preview);
                }
                preview.innerHTML = `
                    <img src="${e.target.result}" 
                         alt="Preview" 
                         class="img-thumbnail" 
                         style="max-width: 150px;">
                `;
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
