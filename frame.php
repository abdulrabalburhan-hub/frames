<?php
require_once 'config.php';

// Get frame ID from URL
$frame_id = $_GET['id'] ?? '';

if (empty($frame_id)) {
    die('Invalid frame URL');
}

// Fetch frame details
$stmt = $conn->prepare("SELECT * FROM frames WHERE unique_id = ?");
$stmt->bind_param("s", $frame_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Frame not found');
}

$frame = $result->fetch_assoc();
$stmt->close();

// Fetch slots if multi-photo frame
$slots = [];
if ($frame['is_multi_photo']) {
    $stmt = $conn->prepare("SELECT * FROM frame_slots WHERE frame_id = ? ORDER BY slot_number ASC");
    $stmt->bind_param("i", $frame['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $slots[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#667eea">
    <title><?= escape($frame['frame_name']) ?> - AlBurhan Frames</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=3">
</head>
<body class="user-page">
    <!-- Navigation -->
    <nav class="navbar navbar-dark" style="background: rgba(0,0,0,0.3);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/images/logo.png" alt="AlBurhan" class="brand-logo me-2" style="max-height: 70px;">
                <strong class="d-none d-sm-inline">AlBurhan <span class="brand-arabic d-none d-md-inline">البرہان</span></strong>
                <strong class="d-inline d-sm-none">AlBurhan</strong>
            </a>
            <div class="d-flex align-items-center gap-3">
                <?php
                // Show back button if there are multiple frames
                $countResult = $conn->query("SELECT COUNT(*) as total FROM frames");
                $countRow = $countResult->fetch_assoc();
                if ($countRow['total'] > 1) {
                    echo '<a href="gallery.php" class="btn btn-outline-light btn-sm">';
                    echo '<i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Change Frame</span>';
                    echo '</a>';
                }
                ?>
                <span class="text-white-50 small d-none d-md-inline">Professional Photo Framing</span>
            </div>
        </div>
    </nav>

    <div class="container py-3 py-md-5">
        <div class="row">
            <!-- Left Side - Editor -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-3 p-md-4">
                        <h4 class="mb-4"><?= escape($frame['frame_name']) ?></h4>
                        
                        <!-- Canvas Container -->
                        <div class="canvas-container mb-4">
                            <canvas id="photoCanvas"></canvas>
                            <div id="canvasPlaceholder" class="canvas-placeholder">
                                <i class="bi bi-image display-1 text-muted"></i>
                                <p class="mt-3 text-muted">Upload your photo to get started</p>
                            </div>
                        </div>
                        
                        <!-- Upload Area for Single Photo -->
                        <?php if (!$frame['is_multi_photo']): ?>
                        <div id="uploadArea" class="upload-area mb-4">
                            <input type="file" id="photoInput" accept="image/png,image/jpeg,image/jpg" style="display: none;">
                            <div class="upload-area-content" id="uploadAreaContent">
                                <i class="bi bi-cloud-upload display-4 text-primary"></i>
                                <h5 class="mt-3">Upload Your Photo</h5>
                                <p class="text-muted">Click to browse or drag and drop</p>
                                <p class="text-muted small">PNG or JPG (Max 15MB)</p>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Multi-Slot Upload Area -->
                        <div id="multiSlotUpload" class="mb-4">
                            <h6 class="mb-3">Upload Photos (<?= count($slots) ?> slots)</h6>
                            <div class="slot-upload-grid">
                                <?php foreach ($slots as $slot): ?>
                                <div class="slot-upload-box" data-slot="<?= $slot['slot_number'] ?>">
                                    <input type="file" id="photoInput_slot<?= $slot['slot_number'] ?>" 
                                           accept="image/png,image/jpeg,image/jpg" 
                                           style="display: none;">
                                    <div class="slot-upload-content">
                                        <i class="bi bi-cloud-upload display-4 text-primary"></i>
                                        <p class="mt-2"><strong>Slot <?= $slot['slot_number'] ?></strong></p>
                                        <p class="text-muted small">Click to upload</p>
                                    </div>
                                    <div class="slot-preview" style="display: none;">
                                        <img src="" alt="Preview">
                                        <button class="btn btn-sm btn-outline-primary mt-2 change-slot-photo">
                                            <i class="bi bi-image"></i> Change
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Controls -->
            <div class="col-lg-4">
                <div class="card shadow-lg border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Adjust Your Photo</h5>
                        
                        <!-- Controls (initially hidden) -->
                        <div id="photoControls" style="display: none;">
                            <?php if ($frame['is_multi_photo']): ?>
                            <!-- Current Slot Indicator -->
                            <div id="currentSlotIndicator" class="alert alert-info">
                                <strong>Editing:</strong> <span id="currentSlotText">No slot selected</span>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Zoom Control -->
                            <div class="mb-4">
                                <label class="form-label d-flex justify-content-between">
                                    <span><i class="bi bi-zoom-in"></i> Zoom</span>
                                    <span id="zoomValue">100%</span>
                                </label>
                                <input type="range" class="form-range" id="zoomSlider" 
                                       min="20" max="300" value="100" step="1">
                            </div>
                            
                            <!-- Rotation Control -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-arrow-clockwise"></i> Rotation
                                </label>
                                <div class="btn-group w-100 mb-2" role="group">
                                    <button type="button" class="btn btn-outline-primary" id="rotate0">
                                        0°
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="rotate90">
                                        90°
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="rotate180">
                                        180°
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="rotate270">
                                        270°
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <label class="form-label d-flex justify-content-between small">
                                        <span>Fine Tune</span>
                                        <span id="fineRotateValue">0°</span>
                                    </label>
                                    <input type="range" class="form-range" id="fineRotateSlider" 
                                           min="-30" max="30" value="0" step="1">
                                </div>
                            </div>
                            
                            <!-- Position Control -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-arrows-move"></i> Position
                                </label>
                                <p class="text-muted small">Drag the photo on canvas to reposition</p>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg" id="downloadBtn">
                                    <i class="bi bi-download"></i> Download Photo
                                </button>
                                <button class="btn btn-outline-secondary" id="resetBtn">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                                <button class="btn btn-outline-primary" id="changePhotoBtn">
                                    <i class="bi bi-image"></i> Change Photo
                                </button>
                            </div>
                        </div>
                        
                        <div id="noPhotoMessage" class="text-center text-muted">
                            <i class="bi bi-info-circle"></i>
                            <p class="mb-0 mt-2">Upload a photo to access controls</p>
                        </div>
                    </div>
                </div>
                
                <!-- Instructions Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-lightbulb"></i> Instructions</h6>
                        <ol class="small mb-0 ps-3">
                            <li>Upload your photo</li>
                            <li>Adjust zoom and rotation</li>
                            <li>Drag to position your photo</li>
                            <li>Download the final result</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-white">Processing your photo...</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/editor.js?v=9"></script>
    <script>
        // Initialize editor with frame data
        const frameData = {
            id: '<?= $frame['id'] ?>',
            uniqueId: '<?= $frame['unique_id'] ?>',
            name: '<?= escape($frame['frame_name']) ?>',
            path: '<?= $frame['frame_path'] ?>',
            isMultiPhoto: <?= $frame['is_multi_photo'] ? 'true' : 'false' ?>,
            slotCount: <?= $frame['slot_count'] ?? 1 ?>,
            slots: <?= json_encode($slots) ?>
        };
        
        // Start editor when DOM is ready
        $(document).ready(function() {
            initFrameEditor(frameData);
        });
    </script>
</body>
</html>
