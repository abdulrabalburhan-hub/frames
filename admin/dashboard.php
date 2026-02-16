<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('index.php');
}

// Fetch all frames
$query = "SELECT f.*, a.username as uploaded_by_name 
          FROM frames f 
          LEFT JOIN admin_users a ON f.uploaded_by = a.id 
          ORDER BY f.created_at DESC";
$frames = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Dashboard - AlBurhan Frames</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="theme-color" content="#f9fafb">
</head>
<body class="admin-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="../assets/images/logo.png" alt="AlBurhan" class="brand-logo me-2" style="max-height: 70px;">
                <span class="d-none d-sm-inline"><strong>AlBurhan</strong> <span class="badge bg-primary ms-1">Admin</span></span>
                <span class="d-inline d-sm-none"><strong>AlBurhan</strong></span>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-2 d-none d-md-inline">
                    <i class="bi bi-person-circle"></i> <?= escape($_SESSION['admin_username']) ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8 col-12 mb-3 mb-md-0">
                <h2 class="mb-1">Frame Management</h2>
                <p class="text-muted mb-0">Upload and manage your picture frames</p>
            </div>
            <div class="col-md-4 col-12 text-md-end">
                <a href="manage-short-urls.php" class="btn btn-outline-primary me-2 mb-2 mb-md-0">
                    <i class="bi bi-link-45deg"></i> <span class="d-none d-sm-inline">Short URLs</span>
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload"></i> <span class="d-none d-sm-inline">Upload Frame</span>
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Frames</h6>
                                <h2 class="mb-0"><?= $frames->num_rows ?></h2>
                            </div>
                            <div class="stat-icon bg-primary">
                                <i class="bi bi-images"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Frames Grid -->
        <div class="row">
            <?php if ($frames->num_rows > 0): ?>
                <?php while ($frame = $frames->fetch_assoc()): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card frame-card h-100">
                            <div class="frame-thumbnail">
                                <img src="../<?= escape($frame['thumbnail_path']) ?>" 
                                     class="card-img-top" 
                                     alt="<?= escape($frame['frame_name']) ?>">
                            </div>
                            <div class="card-body">
                                <h6 class="card-title text-truncate" title="<?= escape($frame['frame_name']) ?>">
                                    <?= escape($frame['frame_name']) ?>
                                </h6>
                                <p class="card-text small text-muted mb-1">
                                    <i class="bi bi-clock"></i> <?= date('M d, Y', strtotime($frame['created_at'])) ?>
                                </p>
                                
                                <!-- Full URL for creating short links -->
                                <div class="mb-2">
                                    <label class="small text-muted mb-1">Full URL (for albn.org):</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm share-link" 
                                               value="<?= SITE_URL ?>/frame.php?id=<?= escape($frame['unique_id']) ?>" 
                                               readonly>
                                        <button class="btn btn-success copy-link-btn" 
                                                data-link="<?= SITE_URL ?>/frame.php?id=<?= escape($frame['unique_id']) ?>"
                                                title="Copy this URL to create short link at albn.org">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Short URL Input -->
                                <div class="mb-2">
                                    <label class="small text-muted mb-1">Short URL (optional):</label>
                                    <input type="text" class="form-control form-control-sm short-url-input" 
                                           data-frame-id="<?= $frame['id'] ?>"
                                           value="<?= escape($frame['short_url'] ?? '') ?>" 
                                           placeholder="e.g., albn.org/seeratframe72">
                                    <small class="text-muted d-block mt-1">Save the short URL you created at albn.org</small>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="define-slots.php?id=<?= $frame['id'] ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-grid-3x2"></i> Define Slots <?= $frame['is_multi_photo'] ? '(' . $frame['slot_count'] . ')' : '' ?>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-frame-btn" 
                                            data-id="<?= $frame['id'] ?>" 
                                            data-name="<?= escape($frame['frame_name']) ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="mt-3">No frames uploaded yet</h4>
                        <p class="text-muted">Click the "Upload New Frame" button to get started</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload New Frame</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="frameName" class="form-label">Frame Name</label>
                            <input type="text" class="form-control" id="frameName" name="frame_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="frameFile" class="form-label">Frame Image (PNG/JPG, Max 15MB)</label>
                            <input type="file" class="form-control" id="frameFile" name="frame_file" 
                                   accept="image/png,image/jpeg,image/jpg" required>
                            <div class="form-text">Recommended: PNG with transparent background</div>
                        </div>
                        <div id="uploadPreview" class="mb-3" style="display:none;">
                            <img id="previewImage" src="" class="img-fluid rounded" alt="Preview">
                        </div>
                        <div id="uploadProgress" class="mb-3" style="display:none;">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                        <div id="uploadMessage"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="uploadBtn">
                        <i class="bi bi-cloud-upload"></i> Upload Frame
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Preview image on file select
        $('#frameFile').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (15MB)
                if (file.size > 15 * 1024 * 1024) {
                    alert('File size must be less than 15MB');
                    $(this).val('');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImage').attr('src', e.target.result);
                    $('#uploadPreview').show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Upload frame
        $('#uploadBtn').on('click', function() {
            const formData = new FormData($('#uploadForm')[0]);
            
            // Validate
            if (!$('#frameName').val() || !$('#frameFile').val()) {
                alert('Please fill in all fields');
                return;
            }
            
            $('#uploadBtn').prop('disabled', true);
            $('#uploadProgress').show();
            $('#uploadMessage').html('');
            
            $.ajax({
                url: 'upload-frame.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            $('.progress-bar').css('width', percent + '%');
                        }
                    });
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        $('#uploadMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#uploadMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                        $('#uploadBtn').prop('disabled', false);
                    }
                },
                error: function() {
                    $('#uploadMessage').html('<div class="alert alert-danger">Upload failed. Please try again.</div>');
                    $('#uploadBtn').prop('disabled', false);
                }
            });
        });

        // Copy link
        $('.copy-link-btn').on('click', function() {
            const link = $(this).data('link');
            navigator.clipboard.writeText(link).then(() => {
                const btn = $(this);
                const originalHtml = btn.html();
                btn.html('<i class="bi bi-check2"></i> Copied!');
                setTimeout(() => btn.html(originalHtml), 2000);
            });
        });

        // Delete frame
        $('.delete-frame-btn').on('click', function() {
            const frameId = $(this).data('id');
            const frameName = $(this).data('name');
            
            if (confirm('Are you sure you want to delete "' + frameName + '"?')) {
                $.post('delete-frame.php', { frame_id: frameId }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
        });

        // Save short URL with debouncing
        let shortUrlTimeout;
        $('.short-url-input').on('input', function() {
            const input = $(this);
            const frameId = input.data('frame-id');
            const shortUrl = input.val().trim();
            
            // Clear previous timeout
            clearTimeout(shortUrlTimeout);
            
            // Add loading indicator
            input.css('border-color', '#ffc107');
            
            // Debounce for 1 second
            shortUrlTimeout = setTimeout(function() {
                $.post('save-short-url.php', {
                    frame_id: frameId,
                    short_url: shortUrl
                }, function(response) {
                    if (response.success) {
                        input.css('border-color', '#198754');
                        setTimeout(() => input.css('border-color', ''), 2000);
                    } else {
                        input.css('border-color', '#dc3545');
                        console.error('Failed to save short URL:', response.message);
                    }
                }, 'json').fail(function() {
                    input.css('border-color', '#dc3545');
                    console.error('Failed to save short URL');
                });
            }, 1000);
        });
    });
    </script>
</body>
</html>
