<?php
require_once 'config.php';

// Get poster ID from URL
$poster_id = $_GET['id'] ?? '';

if (empty($poster_id)) {
    die('Invalid poster URL');
}

// Fetch poster details
$stmt = $conn->prepare("SELECT * FROM posters WHERE unique_id = ?");
$stmt->bind_param("s", $poster_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Poster not found');
}

$poster = $result->fetch_assoc();
$stmt->close();

// Fetch slots for this poster
$slots = [];
$stmt = $conn->prepare("SELECT * FROM poster_slots WHERE poster_id = ? ORDER BY slot_number ASC");
$stmt->bind_param("i", $poster['id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $slots[] = $row;
}
$stmt->close();

if (count($slots) === 0) {
    die('No slots defined for this poster');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#667eea">
    <title><?= escape($poster['poster_name']) ?> - AlBurhan Frames</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=3">
    <style>
        .poster-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        .poster-image {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 10px;
        }
        .slot-placeholder {
            position: absolute;
            border: 3px dashed #dc2626;
            background: rgba(220, 38, 38, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #dc2626;
            font-size: 24px;
            border-radius: 8px;
        }
        .slot-placeholder:hover {
            background: rgba(220, 38, 38, 0.2);
            border-color: #991b1b;
            transform: scale(1.02);
        }
        .slot-placeholder.filled {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .slot-placeholder.active {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.2);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }
        .slot-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .slot-number {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }
        .slot-controls {
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 25px;
            padding: 5px 10px;
            gap: 5px;
        }
        .slot-placeholder:hover .slot-controls,
        .slot-placeholder.active .slot-controls {
            display: flex;
        }
        .slot-control-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .slot-control-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        .progress-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .slot-placeholder {
                font-size: 18px;
            }
            .slot-control-btn {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }
        }
    </style>
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
                // Show back button if there are multiple posters
                $countQuery = "SELECT COUNT(*) as total FROM posters p WHERE (SELECT COUNT(*) FROM poster_slots WHERE poster_id = p.id) >= 1";
                $countResult = $conn->query($countQuery);
                $countRow = $countResult->fetch_assoc();
                if ($countRow['total'] > 1) {
                    echo '<a href="poster-gallery.php" class="btn btn-outline-light btn-sm">';
                    echo '<i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Change Poster</span>';
                    echo '</a>';
                }
                ?>
                <span class="text-white-50 small d-none d-md-inline">Professional Photo Posters</span>
            </div>
        </div>
    </nav>

    <div class="container py-3 py-md-5">
        <div class="row">
            <!-- Left Side - Editor -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-3 p-md-4">
                        <h4 class="mb-4"><?= escape($poster['poster_name']) ?></h4>
                        
                        <!-- Progress Section -->
                        <div class="progress-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><i class="bi bi-images"></i> <strong id="photosAddedCount">0</strong>/<?= count($slots) ?> Photos Added</span>
                                <span id="progressPercent">0%</span>
                            </div>
                            <div class="progress" style="height: 8px; background: rgba(255,255,255,0.2);">
                                <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <!-- Poster Container with Clickable Placeholders -->
                        <div class="poster-container" id="posterContainer">
                            <img src="<?= escape($poster['poster_path']) ?>" 
                                 alt="<?= escape($poster['poster_name']) ?>" 
                                 class="poster-image" 
                                 id="posterImage">
                            
                            <!-- Slot Placeholders (positioned absolutely using percentages) -->
                            <?php foreach ($slots as $slot): ?>
                            <div class="slot-placeholder" 
                                 id="slot_<?= $slot['slot_number'] ?>"
                                 data-slot="<?= $slot['slot_number'] ?>"
                                 style="left: <?= $slot['x_percent'] ?>%; 
                                        top: <?= $slot['y_percent'] ?>%; 
                                        width: <?= $slot['width_percent'] ?>%; 
                                        height: <?= $slot['height_percent'] ?>%;">
                                
                                <span class="slot-number"><?= $slot['slot_number'] ?></span>
                                
                                <!-- Canvas for this slot -->
                                <canvas class="slot-canvas" id="canvas_<?= $slot['slot_number'] ?>"></canvas>
                                
                                <!-- Empty state text -->
                                <span class="slot-empty-text" id="emptyText_<?= $slot['slot_number'] ?>">
                                    <i class="bi bi-camera"></i>
                                </span>
                                
                                <!-- Hover Controls -->
                                <div class="slot-controls" id="controls_<?= $slot['slot_number'] ?>">
                                    <button class="slot-control-btn" data-action="zoom-in" title="Zoom In">
                                        <i class="bi bi-zoom-in"></i>
                                    </button>
                                    <button class="slot-control-btn" data-action="zoom-out" title="Zoom Out">
                                        <i class="bi bi-zoom-out"></i>
                                    </button>
                                    <button class="slot-control-btn" data-action="rotate-left" title="Rotate Left">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <button class="slot-control-btn" data-action="rotate-right" title="Rotate Right">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button class="slot-control-btn" data-action="change" title="Change Photo">
                                        <i class="bi bi-image"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Hidden file input for this slot -->
                            <input type="file" 
                                   id="fileInput_<?= $slot['slot_number'] ?>" 
                                   accept="image/png,image/jpeg,image/jpg" 
                                   style="display: none;">
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Instructions -->
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle"></i>
                            <strong>Instructions:</strong> Click on any numbered placeholder to upload a photo. 
                            Hover over filled slots to zoom, rotate, or change photos. Drag photos to reposition them.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Controls -->
            <div class="col-lg-4">
                <div class="card shadow-lg border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-card-image"></i> Poster Details
                        </h5>
                        
                        <div class="mb-3">
                            <small class="text-muted d-block">Total Slots</small>
                            <strong class="d-block"><?= count($slots) ?> photo slots</strong>
                        </div>
                        
                        <div class="mb-4">
                            <small class="text-muted d-block">Current Slot</small>
                            <strong class="d-block" id="currentSlotDisplay">None selected</strong>
                        </div>
                        
                        <hr>
                        
                        <!-- Slot Editor (shown when a slot is active) -->
                        <div id="slotEditor" style="display: none;">
                            <h6 class="mb-3">Edit Current Photo</h6>
                            
                            <!-- Zoom Control -->
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between small">
                                    <span><i class="bi bi-zoom-in"></i> Zoom</span>
                                    <span id="zoomValue">100%</span>
                                </label>
                                <input type="range" class="form-range" id="zoomSlider" 
                                       min="20" max="300" value="100" step="1">
                            </div>
                            
                            <!-- Rotation Control -->
                            <div class="mb-3">
                                <label class="form-label small">
                                    <i class="bi bi-arrow-clockwise"></i> Rotation
                                </label>
                                <div class="btn-group w-100 mb-2" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="rotate0">0°</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="rotate90">90°</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="rotate180">180°</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="rotate270">270°</button>
                                </div>
                            </div>
                            
                            <hr>
                        </div>
                        
                        <!-- Download Button -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg" id="downloadBtn" disabled>
                                <i class="bi bi-download"></i> Download Poster
                            </button>
                            <button class="btn btn-outline-danger btn-sm" id="clearAllBtn">
                                <i class="bi bi-trash"></i> Clear All Photos
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Tips Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-lightbulb"></i> Quick Tips</h6>
                        <ul class="small mb-0 ps-3">
                            <li>Click placeholders to add photos</li>
                            <li>Drag photos to reposition</li>
                            <li>Hover for quick controls</li>
                            <li>Fill all slots to download</li>
                        </ul>
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
        <p class="mt-3 text-white">Processing your poster...</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Poster Editor JavaScript
        const posterData = {
            id: <?= $poster['id'] ?>,
            uniqueId: '<?= escape($poster['unique_id']) ?>',
            name: <?= json_encode($poster['poster_name']) ?>,
            path: <?= json_encode($poster['poster_path']) ?>,
            slots: <?= json_encode($slots) ?>
        };
        
        // Photo states for each slot
        const photoStates = {};
        let currentSlot = null;
        let totalSlots = posterData.slots.length;
        
        // Initialize photo states
        posterData.slots.forEach(slot => {
            photoStates[slot.slot_number] = {
                photo: null,
                canvas: null,
                ctx: null,
                scale: 1.0,
                rotation: 0,
                x: 0,
                y: 0,
                isDragging: false,
                dragStartX: 0,
                dragStartY: 0
            };
        });
        
        $(document).ready(function() {
            initPosterEditor();
        });
        
        function initPosterEditor() {
            // Initialize canvases for each slot
            posterData.slots.forEach(slot => {
                const canvas = document.getElementById('canvas_' + slot.slot_number);
                const placeholder = document.getElementById('slot_' + slot.slot_number);
                
                // Set canvas size to match placeholder
                canvas.width = placeholder.offsetWidth;
                canvas.height = placeholder.offsetHeight;
                
                photoStates[slot.slot_number].canvas = canvas;
                photoStates[slot.slot_number].ctx = canvas.getContext('2d');
            });
            
            // Setup click handlers for placeholders
            $('.slot-placeholder').on('click', function(e) {
                if (!$(e.target).hasClass('slot-control-btn') && !$(e.target).parent().hasClass('slot-control-btn')) {
                    const slotNum = $(this).data('slot');
                    openFileDialog(slotNum);
                }
            });
            
            // Setup file inputs
            posterData.slots.forEach(slot => {
                $(`#fileInput_${slot.slot_number}`).on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        handlePhotoUpload(slot.slot_number, file);
                    }
                });
            });
            
            // Setup control buttons
            $('.slot-control-btn').on('click', function(e) {
                e.stopPropagation();
                const slotNum = $(this).closest('.slot-placeholder').data('slot');
                const action = $(this).data('action');
                handleControlAction(slotNum, action);
            });
            
            // Setup canvas drag events
            $('.slot-canvas').on('mousedown touchstart', handleDragStart);
            $(document).on('mousemove touchmove', handleDragMove);
            $(document).on('mouseup touchend', handleDragEnd);
            
            // Setup slider controls
            $('#zoomSlider').on('input', function() {
                if (currentSlot) {
                    const scale = $(this).val() / 100;
                    photoStates[currentSlot].scale = scale;
                    $('#zoomValue').text(Math.round(scale * 100) + '%');
                    redrawSlot(currentSlot);
                }
            });
            
            // Setup rotation buttons
            $('#rotate0, #rotate90, #rotate180, #rotate270').on('click', function() {
                if (currentSlot) {
                    const rotation = parseInt($(this).attr('id').replace('rotate', ''));
                    photoStates[currentSlot].rotation = rotation;
                    $('.btn-group button').removeClass('active');
                    $(this).addClass('active');
                    redrawSlot(currentSlot);
                }
            });
            
            // Download button
            $('#downloadBtn').on('click', downloadPoster);
            
            // Clear all button
            $('#clearAllBtn').on('click', clearAllPhotos);
        }
        
        function openFileDialog(slotNum) {
            $(`#fileInput_${slotNum}`)[0].click();
        }
        
        function handlePhotoUpload(slotNum, file) {
            // Validate file size (15MB)
            if (file.size > 15 * 1024 * 1024) {
                alert('File size must be less than 15MB');
                return;
            }
            
            // Validate file type
            if (!file.type.match('image/(png|jpeg|jpg)')) {
                alert('Only PNG and JPG files are allowed');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Store photo
                    photoStates[slotNum].photo = img;
                    photoStates[slotNum].scale = 1.0;
                    photoStates[slotNum].rotation = 0;
                    photoStates[slotNum].x = 0;
                    photoStates[slotNum].y = 0;
                    
                    // Update UI
                    $(`#slot_${slotNum}`).addClass('filled');
                    $(`#emptyText_${slotNum}`).hide();
                    
                    // Select this slot
                    selectSlot(slotNum);
                    
                    // Redraw
                    redrawSlot(slotNum);
                    
                    // Update progress
                    updateProgress();
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
        
        function selectSlot(slotNum) {
            currentSlot = slotNum;
            
            // Update UI
            $('.slot-placeholder').removeClass('active');
            $(`#slot_${slotNum}`).addClass('active');
            
            // Update controls
            const state = photoStates[slotNum];
            if (state.photo) {
                $('#slotEditor').show();
                $('#currentSlotDisplay').text(`Slot ${slotNum}`);
                
                $('#zoomSlider').val(Math.round(state.scale * 100));
                $('#zoomValue').text(Math.round(state.scale * 100) + '%');
                
                $('.btn-group button').removeClass('active');
                $(`#rotate${state.rotation}`).addClass('active');
            } else {
                $('#slotEditor').hide();
                $('#currentSlotDisplay').text(`Slot ${slotNum} (empty)`);
            }
        }
        
        function handleControlAction(slotNum, action) {
            const state = photoStates[slotNum];
            if (!state.photo) return;
            
            selectSlot(slotNum);
            
            switch(action) {
                case 'zoom-in':
                    state.scale = Math.min(state.scale + 0.1, 3.0);
                    break;
                case 'zoom-out':
                    state.scale = Math.max(state.scale - 0.1, 0.2);
                    break;
                case 'rotate-left':
                    state.rotation = (state.rotation - 90 + 360) % 360;
                    break;
                case 'rotate-right':
                    state.rotation = (state.rotation + 90) % 360;
                    break;
                case 'change':
                    openFileDialog(slotNum);
                    return;
            }
            
            // Update controls
            $('#zoomSlider').val(Math.round(state.scale * 100));
            $('#zoomValue').text(Math.round(state.scale * 100) + '%');
            $('.btn-group button').removeClass('active');
            $(`#rotate${state.rotation}`).addClass('active');
            
            redrawSlot(slotNum);
        }
        
        function redrawSlot(slotNum) {
            const state = photoStates[slotNum];
            if (!state.photo || !state.ctx) return;
            
            const canvas = state.canvas;
            const ctx = state.ctx;
            
            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Save context
            ctx.save();
            
            // Translate to center
            ctx.translate(canvas.width / 2, canvas.height / 2);
            
            // Apply rotation
            ctx.rotate((state.rotation * Math.PI) / 180);
            
            // Apply transformations
            const scaledWidth = state.photo.width * state.scale;
            const scaledHeight = state.photo.height * state.scale;
            
            // Draw photo
            ctx.drawImage(
                state.photo,
                -scaledWidth / 2 + state.x,
                -scaledHeight / 2 + state.y,
                scaledWidth,
                scaledHeight
            );
            
            // Restore context
            ctx.restore();
        }
        
        function handleDragStart(e) {
            const slotNum = $(this).closest('.slot-placeholder').data('slot');
            const state = photoStates[slotNum];
            
            if (!state.photo) return;
            
            e.preventDefault();
            state.isDragging = true;
            selectSlot(slotNum);
            
            const touch = e.type.includes('touch') ? e.originalEvent.touches[0] : e;
            state.dragStartX = touch.clientX - state.x;
            state.dragStartY = touch.clientY - state.y;
        }
        
        function handleDragMove(e) {
            for (let slotNum in photoStates) {
                const state = photoStates[slotNum];
                if (state.isDragging) {
                    e.preventDefault();
                    const touch = e.type.includes('touch') ? e.originalEvent.touches[0] : e;
                    state.x = touch.clientX - state.dragStartX;
                    state.y = touch.clientY - state.dragStartY;
                    redrawSlot(slotNum);
                    break;
                }
            }
        }
        
        function handleDragEnd() {
            for (let slotNum in photoStates) {
                photoStates[slotNum].isDragging = false;
            }
        }
        
        function updateProgress() {
            let filled = 0;
            for (let slotNum in photoStates) {
                if (photoStates[slotNum].photo) filled++;
            }
            
            const percent = Math.round((filled / totalSlots) * 100);
            
            $('#photosAddedCount').text(filled);
            $('#progressPercent').text(percent + '%');
            $('#progressBar').css('width', percent + '%');
            
            // Enable download button if all filled
            if (filled === totalSlots) {
                $('#downloadBtn').prop('disabled', false);
            } else {
                $('#downloadBtn').prop('disabled', true);
            }
        }
        
        function clearAllPhotos() {
            if (!confirm('Are you sure you want to clear all photos?')) return;
            
            for (let slotNum in photoStates) {
                photoStates[slotNum].photo = null;
                photoStates[slotNum].scale = 1.0;
                photoStates[slotNum].rotation = 0;
                photoStates[slotNum].x = 0;
                photoStates[slotNum].y = 0;
                
                $(`#slot_${slotNum}`).removeClass('filled active');
                $(`#emptyText_${slotNum}`).show();
                
                const ctx = photoStates[slotNum].ctx;
                if (ctx) {
                    ctx.clearRect(0, 0, photoStates[slotNum].canvas.width, photoStates[slotNum].canvas.height);
                }
                
                // Clear file input
                $(`#fileInput_${slotNum}`).val('');
            }
            
            currentSlot = null;
            $('#slotEditor').hide();
            $('#currentSlotDisplay').text('None selected');
            updateProgress();
        }
        
        function downloadPoster() {
            // Show loading overlay
            $('#loadingOverlay').show();
            
            // Prepare data for each slot
            const slotData = [];
            
            for (let slotNum in photoStates) {
                const state = photoStates[slotNum];
                if (state.photo && state.canvas) {
                    slotData.push({
                        slot_number: slotNum,
                        image_data: state.canvas.toDataURL('image/png'),
                        scale: state.scale,
                        rotation: state.rotation,
                        x: state.x,
                        y: state.y
                    });
                }
            }
            
            // Send to process.php
            $.ajax({
                url: 'process.php',
                method: 'POST',
                data: {
                    poster_id: posterData.uniqueId,
                    is_poster: 1,
                    slots: JSON.stringify(slotData)
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(blob) {
                    $('#loadingOverlay').hide();
                    
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = posterData.name.replace(/[^a-z0-9]/gi, '_') + '_poster.png';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                },
                error: function() {
                    $('#loadingOverlay').hide();
                    alert('Error processing poster. Please try again.');
                }
            });
        }
    </script>
</body>
</html>
