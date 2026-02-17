<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('index.php');
}

// Fetch all posters with slot counts
$query = "SELECT p.*, a.username as uploaded_by_name,
          (SELECT COUNT(*) FROM poster_slots ps WHERE ps.poster_id = p.id) as slot_count
          FROM posters p 
          LEFT JOIN admin_users a ON p.uploaded_by = a.id 
          ORDER BY p.created_at DESC";
$posters = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Poster Management - AlBurhan Frames</title>
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
                <h2 class="mb-1">Poster Management</h2>
                <p class="text-muted mb-0">Upload and manage your posters with photo slots</p>
            </div>
            <div class="col-md-4 col-12 text-md-end">
                <a href="dashboard.php" class="btn btn-outline-secondary me-2 mb-2 mb-md-0">
                    <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Back to Frames</span>
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload"></i> <span class="d-none d-sm-inline">Upload Poster</span>
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
                                <h6 class="text-muted mb-1">Total Posters</h6>
                                <h2 class="mb-0"><?= $posters->num_rows ?></h2>
                            </div>
                            <div class="stat-icon bg-primary">
                                <i class="bi bi-file-earmark-image"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posters Grid -->
        <div class="row">
            <?php if ($posters->num_rows > 0): ?>
                <?php while ($poster = $posters->fetch_assoc()): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card frame-card h-100">
                            <div class="frame-thumbnail">
                                <img src="../<?= escape($poster['thumbnail_path']) ?>" 
                                     class="card-img-top" 
                                     alt="<?= escape($poster['poster_name']) ?>">
                            </div>
                            <div class="card-body">
                                <h6 class="card-title text-truncate" title="<?= escape($poster['poster_name']) ?>">
                                    <?= escape($poster['poster_name']) ?>
                                </h6>
                                <p class="card-text small text-muted mb-1">
                                    <i class="bi bi-clock"></i> <?= date('M d, Y', strtotime($poster['created_at'])) ?>
                                </p>
                                <p class="card-text small text-muted mb-2">
                                    <i class="bi bi-grid-3x2"></i> 
                                    <?= $poster['slot_count'] > 0 ? $poster['slot_count'] . ' slots defined' : 'No slots defined' ?>
                                </p>
                                
                                <div class="d-grid gap-2">
                                    <a href="poster-define-slots.php?id=<?= $poster['id'] ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-grid-3x2"></i> Define Slots <?= $poster['slot_count'] > 0 ? '(' . $poster['slot_count'] . ')' : '' ?>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-poster-btn" 
                                            data-id="<?= $poster['id'] ?>" 
                                            data-name="<?= escape($poster['poster_name']) ?>">
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
                        <h4 class="mt-3">No posters uploaded yet</h4>
                        <p class="text-muted">Click the "Upload Poster" button to get started</p>
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
                    <h5 class="modal-title">Upload New Poster</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="posterName" class="form-label">Poster Name</label>
                            <input type="text" class="form-control" id="posterName" name="poster_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="posterFile" class="form-label">Poster Image (PNG Only, Max 15MB)</label>
                            <input type="file" class="form-control" id="posterFile" name="poster_file" 
                                   accept="image/png" required>
                            <div class="form-text">PNG format with transparent background required</div>
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
                        <i class="bi bi-cloud-upload"></i> Upload Poster
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
        $('#posterFile').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.match('image/png')) {
                    alert('Only PNG files are allowed for posters');
                    $(this).val('');
                    return;
                }
                
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

        // Upload poster
        $('#uploadBtn').on('click', function() {
            const formData = new FormData($('#uploadForm')[0]);
            
            // Validate
            if (!$('#posterName').val() || !$('#posterFile').val()) {
                alert('Please fill in all fields');
                return;
            }
            
            $('#uploadBtn').prop('disabled', true);
            $('#uploadProgress').show();
            $('#uploadMessage').html('');
            
            $.ajax({
                url: 'upload-poster.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
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
                    console.log('Upload response:', response);
                    if (response && response.success) {
                        $('#uploadMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                        // Redirect to define slots page if provided
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        const errorMsg = (response && response.message) ? response.message : 'Upload failed';
                        $('#uploadMessage').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                        $('#uploadBtn').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', xhr.responseText);
                    const errorMsg = xhr.responseText || 'Upload failed. Please try again.';
                    $('#uploadMessage').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                    $('#uploadBtn').prop('disabled', false);
                }
            });
        });

        // Delete poster
        $('.delete-poster-btn').on('click', function() {
            const posterId = $(this).data('id');
            const posterName = $(this).data('name');
            
            if (confirm('Are you sure you want to delete "' + posterName + '"? This will also delete all slot definitions.')) {
                $.post('delete-poster.php', { poster_id: posterId }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
        });
    });
    </script>
</body>
</html>
