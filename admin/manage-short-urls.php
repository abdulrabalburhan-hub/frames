<?php
// Short URL Management Guide
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    redirect('index.php');
}

// Fetch all frames with their short URLs
$frames = $conn->query("SELECT id, unique_id, frame_name, short_url, created_at FROM frames ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Short URLs Guide - AlBurhan Frames</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="theme-color" content="#f9fafb">
</head>
<body class="admin-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="../assets/images/logo.png" alt="AlBurhan" class="brand-logo me-2" style="max-height: 70px;">
                <span class="d-none d-sm-inline"><strong>AlBurhan</strong> <span class="badge bg-primary ms-1">Admin</span></span>
                <span class="d-inline d-sm-none"><strong>AlBurhan</strong></span>
            </a>
            <div class="d-flex align-items-center">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Back</span>
                </a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-link-45deg"></i> Short URL Management</h4>
                    </div>
                    <div class="card-body">
                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="bi bi-info-circle"></i> How to Create Short URLs with albn.org</h5>
                            <ol class="mb-0">
                                <li>Copy the <strong>Full URL</strong> of a frame from the table below (click the <i class="bi bi-clipboard"></i> button)</li>
                                <li>Go to <strong>albn.org</strong> (or your URL shortener service)</li>
                                <li>Create a short link (e.g., <code>https://albn.org/seeratframe72</code>) pointing to the copied Full URL</li>
                                <li>Copy the short URL you created</li>
                                <li>Paste it in the "Short URL" field on the dashboard for reference</li>
                            </ol>
                            <hr>
                            <p class="mb-0"><strong>Example:</strong></p>
                            <ul class="mb-0">
                                <li>Full URL: <code>https://alburhan.online/frame.php?id=frame_699206c382ea65.66089756</code></li>
                                <li>Create at albn.org → Short URL: <code>https://albn.org/seeratframe72</code></li>
                                <li>When users click the short URL, they go directly to your frame!</li>
                            </ul>
                        </div>

                        <!-- Frames Table -->
                        <h5 class="mt-4 mb-3">Frames &amp; URLs</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%;">Frame Name</th>
                                        <th style="width: 45%;">Full URL (for creating short links)</th>
                                        <th style="width: 25%;">Created Short URL</th>
                                        <th style="width: 10%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($frames->num_rows > 0): ?>
                                        <?php while ($frame = $frames->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?= escape($frame['frame_name']) ?></strong>
                                                <br><small class="text-muted"><?= date('M d, Y', strtotime($frame['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" 
                                                           class="form-control form-control-sm" 
                                                           value="<?= SITE_URL ?>/frame.php?id=<?= escape($frame['unique_id']) ?>" 
                                                           readonly>
                                                    <button class="btn btn-success copy-url-btn" 
                                                            data-url="<?= SITE_URL ?>/frame.php?id=<?= escape($frame['unique_id']) ?>"
                                                            title="Copy this URL to create short link at albn.org">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted">Use this URL at albn.org to create your short link</small>
                                            </td>
                                            <td>
                                                <?php if (!empty($frame['short_url'])): ?>
                                                    <a href="<?= strpos($frame['short_url'], 'http') === 0 ? '' : 'https://' ?><?= escape($frame['short_url']) ?>" 
                                                       target="_blank" 
                                                       class="text-primary d-block mb-1">
                                                        <?= escape($frame['short_url']) ?>
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                    <small class="text-success"><i class="bi bi-check-circle"></i> Saved</small>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="bi bi-dash-circle"></i> Not created yet</span>
                                                    <br><small class="text-muted">Set this in the dashboard after creating the short link</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="../frame.php?id=<?= escape($frame['unique_id']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Preview frame">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                                No frames uploaded yet. <a href="dashboard.php">Upload frames from the dashboard</a>.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-4">
                            <h5>Additional Options</h5>
                            <div class="accordion" id="accordionOptions">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGallery">
                                            Option 1: Gallery Page (Multiple Frames)
                                        </button>
                                    </h2>
                                    <div id="collapseGallery" class="accordion-collapse collapse" data-bs-parent="#accordionOptions">
                                        <div class="accordion-body">
                                            <p>If you want users to see all frames and choose one:</p>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="<?= SITE_URL ?>/gallery.php" readonly>
                                                <button class="btn btn-success copy-url-btn" data-url="<?= SITE_URL ?>/gallery.php">
                                                    <i class="bi bi-clipboard"></i> Copy
                                                </button>
                                            </div>
                                            <small class="text-muted">Create a short link for this URL at albn.org</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAuto">
                                            Option 2: Smart Routing (Auto-detect)
                                        </button>
                                    </h2>
                                    <div id="collapseAuto" class="accordion-collapse collapse" data-bs-parent="#accordionOptions">
                                        <div class="accordion-body">
                                            <p>Automatically shows gallery if multiple frames, or direct frame if only one:</p>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="<?= SITE_URL ?>/" readonly>
                                                <button class="btn btn-success copy-url-btn" data-url="<?= SITE_URL ?>/">
                                                    <i class="bi bi-clipboard"></i> Copy
                                                </button>
                                            </div>
                                            <small class="text-muted">Create a short link for this URL at albn.org</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Copy URL to clipboard
        $('.copy-url-btn').on('click', function() {
            const url = $(this).data('url');
            navigator.clipboard.writeText(url).then(() => {
                const btn = $(this);
                const originalHtml = btn.html();
                btn.html('<i class="bi bi-check2"></i> Copied!');
                btn.removeClass('btn-success').addClass('btn-primary');
                setTimeout(() => {
                    btn.html(originalHtml);
                    btn.removeClass('btn-primary').addClass('btn-success');
                }, 2000);
            }).catch(() => {
                alert('Failed to copy. Please select and copy manually.');
            });
        });
    </script>
</body>
</html>
