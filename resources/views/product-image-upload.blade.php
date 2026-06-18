<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#161b97">
    <title>{{ $product ? $product->name . ' - Upload Image' : 'Product Image Upload' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f4f5fa;
            min-height: 100vh;
            color: #1a1a2e;
        }

        /* Header */
        .page-header {
            background: linear-gradient(135deg, #161b97 0%, #12167d 100%);
            padding: 20px 16px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(22, 27, 151, 0.3);
        }

        .page-header h1 {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Main Content */
        .main-content {
            padding: 16px;
            max-width: 480px;
            margin: 0 auto;
        }

        /* Product Info Card */
        .product-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 16px;
        }

        .product-info {
            padding: 20px;
        }

        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f2ff;
            color: #161b97;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .product-name {
            font-size: 1.3rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .product-code {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            font-family: 'JetBrains Mono', inter;
            letter-spacing: 0.5px;
        }

        .product-meta {
            display: flex;
            gap: 16px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #f1f5f9;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .meta-label {
            font-size: 0.6rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-value {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
        }

        /* Current Image Preview */
        .current-image-section {
            padding: 0 20px 20px;
        }

        .current-image-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .current-image-wrap {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
        }

        .current-image-wrap img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .current-image-wrap img[src*="product.jpg"] {
            opacity: 0.7;
            filter: grayscale(20%);
        }

        .no-image-text {
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 30px 0;
        }

        .no-image-text i {
            font-size: 2rem;
            display: block;
            margin-bottom: 8px;
            opacity: 0.5;
        }

        /* Upload Section */
        .upload-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 16px;
        }

        .upload-title {
            font-size: 0.75rem;
            font-weight: 800;
            color: #161b97;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .upload-title i {
            font-size: 1.1rem;
        }

        /* Drop Zone */
        .drop-zone {
            border: 2px dashed #d1d5db;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafbff;
            position: relative;
            overflow: hidden;
        }

        .drop-zone:hover,
        .drop-zone.active {
            border-color: #161b97;
            background: #f0f2ff;
        }

        .drop-zone.has-file {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .drop-zone-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #161b97, #12167d);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: #fff;
            font-size: 1.5rem;
        }

        .drop-zone-text {
            font-size: 0.9rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
        }

        .drop-zone-hint {
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        /* Image Preview */
        .image-preview {
            margin-top: 16px;
            display: none;
        }

        .image-preview.show {
            display: block;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .preview-label {
            font-size: 0.65rem;
            font-weight: 800;
            color: #10b981;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .preview-clear {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 0.7rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .preview-img-wrap {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
        }

        .preview-img-wrap img {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
            object-fit: contain;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding: 8px 12px;
            background: #f0fdf4;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #166534;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 16px;
            margin-top: 10px;
            background: linear-gradient(135deg, #161b97 0%, #12167d 100%);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(22, 27, 151, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 27, 151, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .submit-btn i {
            font-size: 1.1rem;
        }

        /* Camera Button (Mobile) */
        .camera-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .camera-btn:hover {
            background: #f0f2ff;
            border-color: #161b97;
            color: #161b97;
        }

        .camera-btn i {
            font-size: 1.2rem;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 18px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: start;
            gap: 12px;
            animation: slideInDown 0.4s ease;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            font-size: 1.3rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert div {
            flex: 1;
        }

        .alert strong {
            display: block;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 2px solid #86efac;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 2px solid #fecaca;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        /* Error Card */
        .error-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .error-icon {
            width: 64px;
            height: 64px;
            background: #fef2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: #ef4444;
            font-size: 1.8rem;
        }

        .error-card h2 {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .error-card p {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Scan another link */
        .scan-another {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            color: #161b97;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
        }

        .scan-another:hover {
            text-decoration: underline;
        }

        /* Loading spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .submit-btn.loading .spinner {
            display: inline-block;
        }

        .submit-btn.loading .btn-text {
            display: none;
        }
    </style>
</head>

<body>


    <!-- Header -->
    <div class="page-header">
        <h1><i class="bi bi-camera"></i> Product Image Upload</h1>
        <p>Jaffna Gold (PVT) LTD</p>
    </div>

    <div class="main-content">
        @if(session('success'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <div>
                <strong>Success!</strong><br>
                {{ session('success') }}
            </div>
        </div>
        <script>
            // Auto-reload to show new image after 2 seconds
            setTimeout(function() {
                window.location.reload();
            }, 2000);
        </script>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>⚠️ Upload Error!</strong><br>
                {{ session('error') }}
                @php
                $error = session('error');
                $isPermissionError = strpos($error, 'directory') !== false ||
                strpos($error, 'writable') !== false ||
                strpos($error, 'Unable to write') !== false;
                @endphp
                @if($isPermissionError)
                <div style="margin-top: 12px; padding-top: 12px; border-top: 2px solid rgba(220, 53, 69, 0.3);">
                    <small style="display: block; margin-bottom: 8px;">
                        <strong>🔧 To fix this, administrator should run ONE of these commands:</strong>
                    </small>
                    <div style="background: rgba(0,0,0,0.15); padding: 8px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 0.75rem; overflow-x: auto; margin-bottom: 8px;">
                        <div style="margin-bottom: 6px;">
                            <strong>Option 1 (Recommended):</strong><br>
                            <code style="color: #10b981;">php artisan images:setup</code>
                        </div>
                        <div style="margin-bottom: 6px; border-top: 1px solid rgba(220, 53, 69, 0.2); padding-top: 6px;">
                            <strong>Option 2:</strong><br>
                            <code style="color: #10b981;">php artisan storage:link</code>
                        </div>
                        <div style="border-top: 1px solid rgba(220, 53, 69, 0.2); padding-top: 6px;">
                            <strong>Option 3 (Manual SSH):</strong><br>
                            <code style="color: #10b981;">chmod -R 777 public/images/</code>
                        </div>
                    </div>
                    <small style="color: #ef4444; display: block;">
                        💡 System will automatically try fallback methods. If error persists after running above commands, clear the browser cache and try again.
                    </small>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($error)
        <!-- Product Not Found -->
        <div class="error-card">
            <div class="error-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <h2>Product Not Found</h2>
            <p>{{ $error }}</p>
            <p style="margin-top: 8px; font-size: 0.75rem; color: #94a3b8;">
                Please check the barcode and try scanning again.
            </p>
        </div>
        @elseif($product)
        <!-- Product Info -->
        <div class="product-card">
            <div class="product-info">
                <div class="product-badge">
                    <i class="bi bi-qr-code"></i> #{{ $product->id }} — {{ $product->code }}
                </div>
                <h2 class="product-name">{{ $product->name }}</h2>
                <div class="product-code">{{ $product->code }}</div>

                <div class="product-meta">
                    @if($product->brand)
                    <div class="meta-item">
                        <span class="meta-label">Brand</span>
                        <span class="meta-value">{{ $product->brand->brand_name ?? '—' }}</span>
                    </div>
                    @endif
                    @if($product->category)
                    <div class="meta-item">
                        <span class="meta-label">Category</span>
                        <span class="meta-value">{{ $product->category->category_name ?? '—' }}</span>
                    </div>
                    @endif
                    <div class="meta-item">
                        <span class="meta-label">Status</span>
                        <span class="meta-value" style="color: {{ $product->status === 'active' ? '#10b981' : '#ef4444' }};">
                            {{ ucfirst($product->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Current Image -->
            <div class="current-image-section">
                @php
                $rawImage = $product->getRawOriginal('image');
                $isDefaultImage = !$rawImage || $rawImage === '' || $rawImage === 'images/product.jpg' || $rawImage === 'images/products/product.jpg';
                if ($isDefaultImage) {
                $displayImageUrl = asset('images/product.jpg');
                } else {
                // Handle storage/images/ paths via serve route
                if (strpos($rawImage, 'storage/images/') === 0) {
                $imgFilename = substr($rawImage, strlen('storage/images/'));
                $displayImageUrl = url('/product-image-serve/' . $imgFilename);
                } else {
                $displayImageUrl = asset($rawImage);
                }
                }
                @endphp
                <div class="current-image-label">
                    Current Image
                    @if($isDefaultImage)
                    <span style="color: #f59e0b; font-weight: 600; margin-left: 8px;">
                        <i class="bi bi-info-circle"></i> Default
                    </span>
                    @else
                    <span style="color: #10b981; font-weight: 600; margin-left: 8px;">
                        <i class="bi bi-check-circle-fill"></i> Custom
                    </span>
                    @endif
                </div>
                <div class="current-image-wrap" style="{{ $isDefaultImage ? 'border-color: #fbbf24; background: #fef3c7;' : 'border-color: #10b981; background: #f0fdf4;' }}">
                    <img src="{{ $displayImageUrl }}?t={{ time() }}"
                        alt="{{ $product->name }}"
                        onerror="this.onerror=null; this.src='{{ asset('images/product.jpg') }}';">
                    @if($isDefaultImage)
                    <div style="margin-top: 10px; color: #92400e; font-size: 0.75rem; font-weight: 600;">
                        <i class="bi bi-arrow-down-circle"></i> Upload a new image below to replace default
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="upload-card">
            <div class="upload-title">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                Upload New Image
            </div>

            <form action="{{ url('/product-image/' . $product->id . '/upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf

                <!-- Drop Zone for gallery pick -->
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('galleryInput').click()">
                    <div class="drop-zone-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <div class="drop-zone-text">Tap to choose image</div>
                    <div class="drop-zone-hint">JPG, PNG, GIF, WebP • Max 10MB</div>
                    <input type="file" name="image" id="galleryInput" accept="image/*" onchange="previewImage(this)">
                </div>

                <!-- Camera Button (mobile) -->
                <label class="camera-btn" for="cameraInput">
                    <i class="bi bi-camera-fill"></i>
                    Take Photo with Camera
                </label>
                <input type="file" name="image" id="cameraInput" accept="image/*" capture="environment" style="display:none;" onchange="previewImage(this)">

                <!-- Preview -->
                <div class="image-preview" id="imagePreview">
                    <div class="preview-header">
                        <span class="preview-label"><i class="bi bi-check-circle-fill"></i> Selected Image</span>
                        <button type="button" class="preview-clear" onclick="clearPreview()">
                            <i class="bi bi-x-circle"></i> Remove
                        </button>
                    </div>
                    <div class="preview-img-wrap">
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                    <div class="file-info" id="fileInfo">
                        <i class="bi bi-file-earmark-image"></i>
                        <span id="fileName">image.jpg</span>
                        <span>•</span>
                        <span id="fileSize">0 KB</span>
                    </div>
                </div>

                @error('image')
                <div class="alert alert-error" style="margin-top: 12px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $message }}
                </div>
                @enderror

                <!-- Submit -->
                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    <div class="spinner"></div>
                    <span class="btn-text">
                        <i class="bi bi-cloud-check-fill"></i>
                        Upload Image
                    </span>
                </button>
            </form>
        </div>

        <!-- Help Section -->
        <div style="background: #fff; border-radius: 16px; padding: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06); margin-top: 16px;">
            <div style="font-size: 0.7rem; font-weight: 800; color: #161b97; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px;">
                <i class="bi bi-info-circle-fill"></i> Quick Guide
            </div>
            <div style="font-size: 0.8rem; color: #475569; line-height: 1.6;">
                <div style="margin-bottom: 8px; display: flex; align-items: start; gap: 8px;">
                    <i class="bi bi-1-circle-fill" style="color: #161b97; margin-top: 2px;"></i>
                    <span><strong>Select or capture</strong> a product image</span>
                </div>
                <div style="margin-bottom: 8px; display: flex; align-items: start; gap: 8px;">
                    <i class="bi bi-2-circle-fill" style="color: #161b97; margin-top: 2px;"></i>
                    <span>Image will be <strong>automatically compressed</strong> for optimal storage</span>
                </div>
                <div style="display: flex; align-items: start; gap: 8px;">
                    <i class="bi bi-3-circle-fill" style="color: #161b97; margin-top: 2px;"></i>
                    <span>Click <strong>Upload Image</strong> to save to the server</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        // Store the compressed file for form submission
        let compressedFile = null;

        function previewImage(input) {
            const file = input.files[0];
            if (!file) return;

            // Show preview immediately with original
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').classList.add('show');
                document.getElementById('dropZone').classList.add('has-file');

                // Show original file info
                document.getElementById('fileName').textContent = file.name;
                const sizeKB = (file.size / 1024).toFixed(1);
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                document.getElementById('fileSize').textContent = file.size > 1024 * 1024 ?
                    sizeMB + ' MB (compressing...)' :
                    sizeKB + ' KB';

                // Compress the image
                compressImage(file, function(compFile) {
                    compressedFile = compFile;
                    // Update file size display with compressed size
                    const compSizeKB = (compFile.size / 1024).toFixed(1);
                    const compSizeMB = (compFile.size / (1024 * 1024)).toFixed(2);
                    let sizeText = compFile.size > 1024 * 1024 ?
                        compSizeMB + ' MB' :
                        compSizeKB + ' KB';

                    if (compFile.size < file.size) {
                        const saved = (((file.size - compFile.size) / file.size) * 100).toFixed(0);
                        sizeText += ' (compressed ' + saved + '%)';
                    }
                    document.getElementById('fileSize').textContent = sizeText;
                    document.getElementById('submitBtn').disabled = false;
                });
            };
            reader.readAsDataURL(file);
        }

        /**
         * Compress image using Canvas API
         * Resizes to max 1200px and compresses to JPEG ~0.8 quality
         */
        function compressImage(file, callback) {
            const maxWidth = 1200;
            const maxHeight = 1200;
            const quality = 0.8;

            console.log('🖼️ Starting image compression', {
                original_name: file.name,
                original_size: file.size + ' bytes',
                original_size_mb: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
                type: file.type
            });

            // If file is already small (< 2MB), use as-is
            if (file.size < 2 * 1024 * 1024) {
                console.log('✅ File < 2MB, using original');
                callback(file);
                return;
            }

            const img = new Image();
            const reader = new FileReader();

            reader.onload = function(e) {
                console.log('📖 FileReader loaded image data');
                img.onload = function() {
                    let width = img.width;
                    let height = img.height;

                    console.log('🎨 Image dimensions', {
                        width,
                        height
                    });

                    // Calculate new dimensions
                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                        console.log('📐 Resized to', {
                            width,
                            height,
                            ratio
                        });
                    }

                    // Draw to canvas
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    console.log('🎬 Canvas created and image drawn');

                    // Convert to blob
                    canvas.toBlob(function(blob) {
                        if (blob) {
                            const compressedFile = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            console.log('✅ Compression complete', {
                                compressed_size: compressedFile.size + ' bytes',
                                compressed_size_mb: (compressedFile.size / (1024 * 1024)).toFixed(2) + ' MB',
                                original_size: file.size + ' bytes',
                                compression_ratio: ((file.size - compressedFile.size) / file.size * 100).toFixed(1) + '%'
                            });
                            callback(compressedFile);
                        } else {
                            console.warn('⚠️ Blob creation failed, using original');
                            // Fallback to original
                            callback(file);
                        }
                    }, 'image/jpeg', quality);
                };

                img.onerror = function(error) {
                    console.error('❌ Image load error:', error);
                    // If image can't be loaded, use original
                    callback(file);
                };

                img.src = e.target.result;
            };

            reader.onerror = function(error) {
                console.error('❌ FileReader error:', error);
                callback(file);
            };

            reader.readAsDataURL(file);
        }

        function clearPreview() {
            document.getElementById('galleryInput').value = '';
            document.getElementById('cameraInput').value = '';
            document.getElementById('imagePreview').classList.remove('show');
            document.getElementById('dropZone').classList.remove('has-file');
            document.getElementById('submitBtn').disabled = true;
            compressedFile = null;
        }

        // Intercept form submission to use compressed file
        document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.disabled = true;

            const form = this;
            const formData = new FormData(form);

            // Replace the image with compressed version
            if (compressedFile) {
                formData.delete('image');
                formData.append('image', compressedFile, compressedFile.name);
            }

            // Show loading state
            console.log('🚀 Starting image upload...', {
                form_action: form.action,
                has_compressed_file: !!compressedFile,
                form_data_size: formData.entries ? Array.from(formData.entries()).length : 'unknown'
            });

            // Submit via fetch
            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    console.log('📨 Response received', {
                        status: response.status,
                        ok: response.ok,
                        redirected: response.redirected,
                        content_type: response.headers.get('content-type')
                    });

                    // Handle JSON response from controller
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(data => {
                            console.log('📋 JSON Response:', data);
                            if (data.success) {
                                console.log('✅ Upload successful');
                                // Reload page after short delay to show success message
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                console.error('❌ Upload failed:', data.message);
                                btn.classList.remove('loading');
                                btn.disabled = false;
                                alert(data.message || 'Upload failed. Please try again.');
                            }
                            return data;
                        });
                    } else if (response.ok) {
                        console.log('✅ HTML Response - reloading page');
                        // Reload page to show success/error message
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        return response.text().then(text => {
                            console.error('❌ Server error:', response.status, text);
                            throw new Error('Server responded with status ' + response.status);
                        });
                    }
                })
                .catch(error => {
                    console.error('🚨 Upload error:', error);
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    alert('Upload failed: ' + (error.message || 'Unknown error. Please try again.'));
                });
        });
    </script>
</body>

</html>