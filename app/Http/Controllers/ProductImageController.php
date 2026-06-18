<?php

namespace App\Http\Controllers;

use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProductImageController extends Controller
{
    /**
     * Show the product image upload page for a given product.
     * Supports both barcode and product ID lookup.
     * URL: /product-image/{identifier}
     */
    public function show($identifier)
    {
        // Try finding by ID first (for QR code scans), then by barcode
        $product = ProductDetail::find($identifier);

        if (!$product) {
            $product = ProductDetail::where('barcode', $identifier)->first();
        }

        if (!$product) {
            return view('product-image-upload', [
                'product' => null,
                'identifier' => $identifier,
                'error' => 'No product found with identifier: ' . $identifier,
            ]);
        }

        // Load relationships for display
        $product->load(['brand', 'category', 'supplier']);

        return view('product-image-upload', [
            'product' => $product,
            'identifier' => $identifier,
            'error' => null,
        ]);
    }

    /**
     * Test endpoint to diagnose upload issues
     */
    public function testUpload()
    {
        $uploadPath = public_path('images');
        $exists = is_dir($uploadPath);
        $writable = is_writable($uploadPath);
        $permissions = fileperms($uploadPath);

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'upload_path' => $uploadPath,
            'directory_exists' => $exists,
            'directory_writable' => $writable,
            'directory_permissions' => decoct($permissions & 0777),
            'php_user' => function_exists('posix_getpwuid') ?
                posix_getpwuid(posix_geteuid())['name'] : 'unknown',
            'can_create_file' => $this->canCreateTestFile($uploadPath),
            'disk_free_space' => disk_free_space($uploadPath) . ' bytes',
            'temp_dir' => sys_get_temp_dir(),
            'temp_dir_writable' => is_writable(sys_get_temp_dir()),
        ]);
    }

    /**
     * Test if we can create a file in the upload directory
     */
    private function canCreateTestFile($path)
    {
        try {
            $testFile = $path . '/test-' . time() . '.txt';
            if (@file_put_contents($testFile, 'test')) {
                @unlink($testFile);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle the image upload for a product.
     */
    public function upload(Request $request, $identifier)
    {
        try {
            // Validate the uploaded image
            $validated = $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Max 10MB
            ]);

            // Try finding by ID first, then by barcode
            $product = ProductDetail::find($identifier);
            if (!$product) {
                $product = ProductDetail::where('barcode', $identifier)->first();
            }

            if (!$product) {
                $message = 'Product not found.';
                Log::warning('Product upload attempt failed - product not found', [
                    'identifier' => $identifier
                ]);

                // Return appropriate response based on request type
                if ($request->wantsJson()) {
                    return response()->json(['error' => $message], 404);
                }
                return back()->with('error', $message);
            }

            // Generate a unique filename using product code + timestamp
            $extension = $request->file('image')->getClientOriginalExtension();
            $productCode = $product->getRawOriginal('code') ?: 'product';
            $filename = Str::slug($productCode) . '-' . time() . '.' . $extension;

            Log::info('Starting image upload', [
                'product_id' => $product->id,
                'filename' => $filename,
            ]);

            // Create the directory if it doesn't exist and ensure it's writable
            $uploadPath = public_path('images');
            $useStorageFallback = false;

            // Step 1: Create directory if doesn't exist
            if (!is_dir($uploadPath)) {
                try {
                    @mkdir($uploadPath, 0777, true);
                    Log::info('Created upload directory', ['path' => $uploadPath]);
                } catch (\Exception $e) {
                    Log::warning('Failed to create public images directory', ['error' => $e->getMessage()]);
                }
            }

            // Step 2: Try different permission levels to make directory writable
            $permissionAttempts = [0777, 0775, 0755];
            $isWritable = false;

            foreach ($permissionAttempts as $perm) {
                if (@chmod($uploadPath, $perm)) {
                    if (is_writable($uploadPath)) {
                        $isWritable = true;
                        Log::info('Directory is now writable', ['permissions' => decoct($perm)]);
                        break;
                    }
                }
            }

            // Step 3: If still not writable, use Storage facade as fallback
            if (!$isWritable) {
                Log::warning('Could not make public images directory writable. Switching to storage fallback.', [
                    'path' => $uploadPath,
                    'current_perms' => decoct(fileperms($uploadPath) & 0777)
                ]);
                $useStorageFallback = true;

                // Create storage/app/public/images directory
                $storageDir = storage_path('app/public/images');
                if (!is_dir($storageDir)) {
                    try {
                        @mkdir($storageDir, 0777, true);
                    } catch (\Exception $e) {
                        Log::error('Failed to create storage images directory', ['error' => $e->getMessage()]);
                    }
                }
                $uploadPath = $storageDir;
            }

            // Delete old image if it exists (not the default or empty)
            $oldImage = $product->getRawOriginal('image');
            $defaultImages = ['images/product.jpg', 'images/products/product.jpg', '', null];
            if ($oldImage && !in_array($oldImage, $defaultImages)) {
                $oldPath = public_path($oldImage);
                if (file_exists($oldPath)) {
                    if (@unlink($oldPath)) {
                        Log::info('Old product image deleted', [
                            'product_id' => $product->id,
                            'old_image' => $oldImage
                        ]);
                    } else {
                        Log::warning('Failed to delete old image', [
                            'old_image' => $oldImage,
                            'path' => $oldPath
                        ]);
                    }
                }
            }

            // Store the image in public/images with fallback methods
            $file = $request->file('image');
            $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;

            $fileMoved = false;
            $moveError = '';

            // Method 1: Try standard move() method
            try {
                if ($file->move($uploadPath, $filename)) {
                    $fileMoved = true;
                    Log::info('Image file moved successfully (method 1)', [
                        'filename' => $filename,
                        'destination' => $uploadPath
                    ]);
                }
            } catch (\Exception $e) {
                $moveError = $e->getMessage();
                Log::warning('Method 1 (move) failed', ['error' => $moveError]);
            }

            // Method 2: If move failed, try copy() method
            if (!$fileMoved) {
                try {
                    $tempPath = $file->getRealPath();
                    if (@copy($tempPath, $fullPath)) {
                        $fileMoved = true;
                        Log::info('Image file copied successfully (method 2)', [
                            'filename' => $filename,
                            'destination' => $uploadPath
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Method 2 (copy) failed', ['error' => $e->getMessage()]);
                }
            }

            // Method 3: If copy failed, try file_put_contents()
            if (!$fileMoved) {
                try {
                    $content = file_get_contents($file->getRealPath());
                    if ($content !== false && file_put_contents($fullPath, $content) > 0) {
                        $fileMoved = true;
                        Log::info('Image file saved successfully (method 3)', [
                            'filename' => $filename,
                            'destination' => $uploadPath
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Method 3 (file_put_contents) failed', ['error' => $e->getMessage()]);
                }
            }

            // If all methods failed, throw error
            if (!$fileMoved) {
                $errorMsg = 'Failed to save uploaded file. ';
                $errorMsg .= 'Directory: ' . $uploadPath . ', ';
                $errorMsg .= 'Writable: ' . (is_writable($uploadPath) ? 'yes' : 'no') . ', ';
                $errorMsg .= 'Last error: ' . ($moveError ?: 'unknown');
                throw new \Exception($errorMsg);
            }

            // Ensure file has correct permissions
            @chmod($fullPath, 0666);

            // Save the relative path to the database
            // If using public path: 'images/filename'
            // If using storage fallback: 'storage/images/filename' (via symlink)
            if ($useStorageFallback) {
                $imagePath = 'storage/images/' . $filename;
                Log::info('Using storage fallback path', ['path' => $imagePath]);
            } else {
                $imagePath = 'images/' . $filename;
            }

            $updated = DB::table('product_details')
                ->where('id', $product->id)
                ->update(['image' => $imagePath]);

            if (!$updated && $updated !== 0) {
                Log::warning('Database update may have failed', [
                    'product_id' => $product->id,
                    'imagePath' => $imagePath
                ]);
            }

            Log::info('Product image uploaded successfully', [
                'product_id' => $product->id,
                'identifier' => $identifier,
                'image_path' => $imagePath,
                'filename' => $filename,
                'method' => $useStorageFallback ? 'storage_fallback' : 'public_direct',
            ]);

            $successMessage = 'Image uploaded successfully!';

            // Return appropriate response based on request type
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'image_path' => $imagePath,
                    'redirect' => $request->getRequestUri()
                ]);
            }

            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            $baseError = $e->getMessage();

            // Build helpful error message based on the issue
            if (
                strpos($baseError, 'directory not writable') !== false ||
                strpos($baseError, 'Unable to write') !== false ||
                strpos($baseError, 'Failed to save') !== false
            ) {
                $errorMessage = 'Upload directory issue detected. ';
                $errorMessage .= 'System will try to use storage fallback. ';
                $errorMessage .= 'If this persists, ask your administrator to run: ';
                $errorMessage .= 'php artisan storage:link';
            } else {
                $errorMessage = 'Failed to upload image: ' . $baseError;
            }

            Log::error('Product image upload failed', [
                'identifier' => $identifier,
                'error' => $baseError,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return appropriate response based on request type
            if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => $baseError,
                    'troubleshooting' => 'Run: php artisan storage:link'
                ], 500);
            }

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Serve a product image directly from storage (no symlink required).
     * URL: /product-image-serve/{filename}
     */
    public function serveImage($filename)
    {
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);

        // Check all possible storage locations
        $storagePath        = storage_path('app/public/images/' . $filename); // storage/app/public/images/
        $storageDirectPath  = storage_path('images/' . $filename);            // storage/images/
        $publicPath         = public_path('images/' . $filename);              // public/images/

        if (file_exists($storagePath)) {
            $path = $storagePath;
        } elseif (file_exists($storageDirectPath)) {
            $path = $storageDirectPath;
        } elseif (file_exists($publicPath)) {
            $path = $publicPath;
        } else {
            // Return default image
            $defaultPath = public_path('images/product.jpg');
            if (file_exists($defaultPath)) {
                return response()->file($defaultPath, ['Content-Type' => 'image/jpeg']);
            }
            abort(404);
        }

        $mimeType = mime_content_type($path) ?: 'image/jpeg';

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
