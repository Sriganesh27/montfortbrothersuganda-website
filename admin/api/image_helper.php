<?php
// web/admin/api/image_helper.php

function secure_compress_and_save($tmp_file_path, $target_file_path, $max_width = 1200) {
    // 1. SECURITY: Verify True MIME Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp_file_path);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowed_mimes)) {
        return false; // Reject fake images or malicious scripts
    }

    // 2. Load the image into memory based on its type
    switch($mime) {
        case 'image/jpeg': $source_image = imagecreatefromjpeg($tmp_file_path); break;
        case 'image/png':  $source_image = imagecreatefrompng($tmp_file_path); break;
        case 'image/webp': $source_image = imagecreatefromwebp($tmp_file_path); break;
        default: return false;
    }

    if (!$source_image) return false;

    // 3. Resize if it's too large (saves massive amounts of bandwidth)
    $width = imagesx($source_image);
    $height = imagesy($source_image);
    
    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = floor($height * ($max_width / $width));
        $virtual_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNGs
        imagealphablending($virtual_image, false);
        imagesavealpha($virtual_image, true);
        
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        $source_image = $virtual_image;
    }

    // 4. Save the final image as a compressed WebP (Quality 80)
    $success = imagewebp($source_image, $target_file_path, 80);
    imagedestroy($source_image);
    
    return $success;
}
?>