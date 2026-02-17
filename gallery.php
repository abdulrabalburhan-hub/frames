<?php
// User-Facing Frames Gallery
// Displays all available frames for users to choose from

require_once 'config.php';

// Fetch all available frames
$query = "SELECT * FROM frames ORDER BY created_at DESC";
$frames = $conn->query($query);

if ($frames === false) {
    // Database query failed; return a generic 500 error without exposing details
    http_response_code(500);
    echo 'An unexpected error occurred. Please try again later.';
    exit();
}
if ($frames->num_rows === 0) {
    http_response_code(404);
    include '404.php';
    exit();
}

// If only one frame, redirect to it directly
if ($frames->num_rows === 1) {
    $frame = $frames->fetch_assoc();
    redirect('frame.php?id=' . $frame['unique_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#667eea">
    <title>Choose Your Frame - AlBurhan Frames</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .gallery-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        @media (min-width: 768px) {
            .gallery-page {
                padding: 40px 0;
            }
        }
        .gallery-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            height: 100%;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        @media (hover: none) {
            .gallery-card:hover {
                transform: none;
            }
            .gallery-card:active {
                transform: scale(0.98);
            }
        }
        .gallery-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        @media (min-width: 768px) {
            .gallery-card img {
                height: 250px;
            }
        }
        .gallery-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        @media (min-width: 768px) {
            .gallery-header {
                padding: 30px;
                margin-bottom: 40px;
            }
        }
        .frame-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(37, 99, 235, 0.9);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        @media (min-width: 768px) {
            .frame-badge {
                top: 15px;
                right: 15px;
                padding: 5px 15px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body class="gallery-page">
    <!-- Navigation -->
    <nav class="navbar navbar-dark" style="background: rgba(0,0,0,0.3);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/images/logo.png" alt="AlBurhan" class="brand-logo me-2" style="max-height: 70px;">
                <strong class="d-none d-sm-inline">AlBurhan <span class="brand-arabic d-none d-md-inline">البرہان</span></strong>
                <strong class="d-inline d-sm-none">AlBurhan</strong>
            </a>
            <span class="text-white small d-none d-sm-inline">Professional Photo Framing</span>
        </div>
    </nav>

    <div class="container mt-3 mt-md-4">
        <!-- Header -->
        <div class="gallery-header text-center">
            <h1 class="mb-2 mb-md-3" style="font-size: 1.75rem;">
                <i class="bi bi-images text-primary"></i> Choose Your Frame
            </h1>
            <p class="text-muted mb-0">
                Select a frame below to start adding your photo
            </p>
        </div>

        <!-- Frames Grid -->
        <div class="row g-3 g-md-4">
            <?php while ($frame = $frames->fetch_assoc()): ?>
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <div class="card gallery-card" onclick="window.location.href='frame.php?id=<?= escape($frame['unique_id']) ?>'">
                        <div style="position: relative;">
                            <?php if ($frame['is_multi_photo']): ?>
                                <span class="frame-badge">
                                    <i class="bi bi-collection"></i> <?= $frame['slot_count'] ?>
                                </span>
                            <?php endif; ?>
                            <img src="<?= escape($frame['thumbnail_path']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= escape($frame['frame_name']) ?>"
                                 loading="lazy"
                                 onerror="this.src='https://via.placeholder.com/300x250?text=Frame+Image'">
                        </div>
                        <div class="card-body text-center p-2 p-md-3">
                            <h6 class="card-title mb-1 mb-md-2" style="font-size: 0.95rem;"><?= escape($frame['frame_name']) ?></h6>
                            <p class="text-muted small mb-2 d-none d-md-block">
                                <?php if ($frame['is_multi_photo']): ?>
                                    <i class="bi bi-grid-3x2"></i> Multi-photo
                                <?php else: ?>
                                    <i class="bi bi-image"></i> Single photo
                                <?php endif; ?>
                            </p>
                            <button class="btn btn-primary btn-sm w-100" style="font-size: 0.875rem;">
                                <i class="bi bi-arrow-right-circle"></i> Select
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Info Section -->
        <div class="text-center mt-5">
            <div class="card bg-white shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="bi bi-info-circle text-primary"></i> How It Works</h5>
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <i class="bi bi-hand-index display-4 text-primary mb-2"></i>
                            <p class="small mb-0"><strong>1. Choose Frame</strong><br>Select your favorite frame design</p>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <i class="bi bi-cloud-upload display-4 text-primary mb-2"></i>
                            <p class="small mb-0"><strong>2. Upload Photo</strong><br>Add your photo(s) to the frame</p>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <i class="bi bi-sliders display-4 text-primary mb-2"></i>
                            <p class="small mb-0"><strong>3. Adjust</strong><br>Resize, rotate, and position</p>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <i class="bi bi-download display-4 text-primary mb-2"></i>
                            <p class="small mb-0"><strong>4. Download</strong><br>Get your framed photo</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
