<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Get poster ID
$poster_id = $_GET['id'] ?? 0;

if ($poster_id <= 0) {
    header('Location: posters-dashboard.php');
    exit();
}

// Fetch poster details
$stmt = $conn->prepare("SELECT * FROM posters WHERE id = ?");
$stmt->bind_param("i", $poster_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: posters-dashboard.php');
    exit();
}

$poster = $result->fetch_assoc();
$stmt->close();

// Fetch existing slots
$stmt = $conn->prepare("SELECT * FROM poster_slots WHERE poster_id = ? ORDER BY slot_number ASC");
$stmt->bind_param("i", $poster_id);
$stmt->execute();
$result = $stmt->get_result();
$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Define Photo Slots - <?= escape($poster['poster_name']) ?> - AlBurhan</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="theme-color" content="#f9fafb">
    <style>
        #slotCanvas {
            border: 2px solid #333;
            cursor: crosshair;
            max-width: 100%;
            background-color: #f8f9fa;
            display: block;
            margin: 0 auto;
        }
        .slot-item {
            background-color: white;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        @media (min-width: 768px) {
            .slot-item {
                padding: 15px;
            }
        }
        .slot-item.active {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .slot-item input {
            width: 70px;
            margin-right: 5px;
        }
        @media (min-width: 768px) {
            .slot-item input {
                width: 80px;
            }
        }
        .slot-controls {
            max-height: 500px;
            overflow-y: auto;
        }
        @media (min-width: 768px) {
            .slot-controls {
                max-height: 600px;
            }
        }
    </style>
</head>
<body class="admin-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="posters-dashboard.php">
                <img src="../assets/images/logo.png" alt="AlBurhan" class="brand-logo me-2" style="max-height: 64px;">
                <span class="d-none d-md-inline"><i class="bi bi-file-earmark-image"></i> AlBurhan Posters</span>
                <span class="d-inline d-md-none">AlBurhan</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="posters-dashboard.php">
                            <i class="bi bi-speedometer2"></i> <span class="d-none d-sm-inline">Poster Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row mb-3">
            <div class="col">
                <h3><i class="bi bi-grid-3x2"></i> Define Photo Slots: <?= escape($poster['poster_name']) ?></h3>
                <p class="text-muted">Draw 1-10 photo slots on the poster. Use Auto-Detect or draw manually.</p>
            </div>
        </div>

        <div class="row">
            <!-- Canvas Area -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="mb-3">
                            <button type="button" class="btn btn-success me-2" id="autoDetectBtn">
                                <i class="bi bi-magic"></i> Auto-Detect Slots
                            </button>
                            <button type="button" class="btn btn-primary" id="addSlotBtn">
                                <i class="bi bi-plus-circle"></i> Add Slot (Draw on Poster)
                            </button>
                            <button type="button" class="btn btn-secondary" id="clearLastBtn">
                                <i class="bi bi-arrow-counterclockwise"></i> Clear Last
                            </button>
                            <button type="button" class="btn btn-danger" id="clearAllBtn">
                                <i class="bi bi-trash"></i> Clear All
                            </button>
                        </div>
                        <div style="overflow: auto;">
                            <canvas id="slotCanvas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slots List -->
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Photo Slots (Max: 10)</h5>
                        <div class="slot-controls" id="slotList">
                            <!-- Dynamically generated -->
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-success btn-lg" id="saveSlotsBtn">
                                <i class="bi bi-check-circle"></i> Save Slots
                            </button>
                            <a href="posters-dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const posterId = <?= $poster['id'] ?>;
        const posterPath = <?= json_encode($poster['poster_path']) ?>; // CORRECT: json_encode, not quotes
        
        let canvas, ctx;
        let posterImage = null;
        let slots = <?= json_encode($slots) ?>;
        let isDrawing = false;
        let startX, startY;
        let currentSlot = slots.length + 1;
        let drawingMode = false;
        let scaleX = 1;
        let scaleY = 1;

        // Console logging for debugging
        console.log('Poster ID:', posterId);
        console.log('Poster Path:', posterPath);
        console.log('Initial slots:', slots);

        $(document).ready(function() {
            canvas = document.getElementById('slotCanvas');
            ctx = canvas.getContext('2d');
            
            console.log('Canvas element:', canvas);
            console.log('Canvas context:', ctx);
            
            loadPoster();
            renderSlotList();
        });

        function loadPoster() {
            console.log('Loading poster from:', '../' + posterPath);
            posterImage = new Image();
            posterImage.onload = function() {
                console.log('Poster loaded. Natural size:', posterImage.naturalWidth, 'x', posterImage.naturalHeight);
                canvas.width = posterImage.naturalWidth;
                canvas.height = posterImage.naturalHeight;
                
                // Calculate scale factors for coordinate conversion
                const rect = canvas.getBoundingClientRect();
                scaleX = canvas.width / rect.width;
                scaleY = canvas.height / rect.height;
                console.log('Scale factors - X:', scaleX, 'Y:', scaleY);
                
                redrawCanvas();
            };
            posterImage.onerror = function() {
                console.error('Failed to load poster image');
                alert('Failed to load poster image. Check console for details.');
            };
            posterImage.src = '../' + posterPath;
        }

        function redrawCanvas() {
            if (!posterImage) {
                console.log('No poster image to draw');
                return;
            }
            
            console.log('Redrawing canvas with', slots.length, 'slots');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(posterImage, 0, 0);
            
            // Draw all slots
            slots.forEach((slot, index) => {
                drawSlot(slot, index);
            });
        }

        function drawSlot(slot, index) {
            const x = slot.x_position - slot.width / 2;
            const y = slot.y_position - slot.height / 2;
            
            console.log('Drawing slot', slot.slot_number, 'at', x, y, 'size', slot.width, 'x', slot.height);
            
            // Draw rectangle
            ctx.strokeStyle = '#0d6efd';
            ctx.lineWidth = 3;
            ctx.strokeRect(x, y, slot.width, slot.height);
            
            // Draw slot number
            ctx.fillStyle = '#0d6efd';
            ctx.font = 'bold 24px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(slot.slot_number, slot.x_position, slot.y_position);
        }

        // Auto-detect button
        $('#autoDetectBtn').on('click', function() {
            console.log('Auto-detect button clicked');
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Detecting...');
            
            $.ajax({
                url: 'auto-detect-slots.php',
                type: 'POST',
                data: { poster_id: posterId },
                dataType: 'json',
                success: function(response) {
                    console.log('Auto-detect response:', response);
                    if (response.success && response.slots) {
                        slots = response.slots;
                        currentSlot = slots.length + 1;
                        renderSlotList();
                        redrawCanvas();
                        alert('Detected ' + slots.length + ' photo slots!');
                    } else {
                        alert('Error: ' + (response.message || 'Auto-detection failed'));
                    }
                    btn.prop('disabled', false).html('<i class="bi bi-magic"></i> Auto-Detect Slots');
                },
                error: function() {
                    console.error('Auto-detect AJAX error');
                    alert('Error during auto-detection. Please try manual mode.');
                    btn.prop('disabled', false).html('<i class="bi bi-magic"></i> Auto-Detect Slots');
                }
            });
        });

        // Drawing mode toggle
        $('#addSlotBtn').on('click', function() {
            drawingMode = !drawingMode;
            console.log('Drawing mode:', drawingMode);
            if (drawingMode) {
                $(this).addClass('active').text('Drawing Mode (Click & Drag)');
                canvas.style.cursor = 'crosshair';
            } else {
                $(this).removeClass('active').html('<i class="bi bi-plus-circle"></i> Add Slot (Draw on Poster)');
                canvas.style.cursor = 'default';
            }
        });

        // Canvas drawing with proper coordinate scaling
        canvas.addEventListener('mousedown', function(e) {
            if (!drawingMode) return;
            if (slots.length >= 10) {
                alert('Maximum 10 slots allowed');
                return;
            }
            
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            startX = (e.clientX - rect.left) * scaleX;
            startY = (e.clientY - rect.top) * scaleY;
            console.log('Mouse down at:', startX, startY);
        });

        canvas.addEventListener('mousemove', function(e) {
            if (!isDrawing || !drawingMode) return;
            
            const rect = canvas.getBoundingClientRect();
            const endX = (e.clientX - rect.left) * scaleX;
            const endY = (e.clientY - rect.top) * scaleY;
            
            redrawCanvas();
            
            // Draw preview
            ctx.strokeStyle = '#ffc107';
            ctx.lineWidth = 2;
            ctx.setLineDash([5, 5]);
            ctx.strokeRect(
                Math.min(startX, endX),
                Math.min(startY, endY),
                Math.abs(endX - startX),
                Math.abs(endY - startY)
            );
            ctx.setLineDash([]);
        });

        canvas.addEventListener('mouseup', function(e) {
            if (!isDrawing || !drawingMode) return;
            
            const rect = canvas.getBoundingClientRect();
            const endX = (e.clientX - rect.left) * scaleX;
            const endY = (e.clientY - rect.top) * scaleY;
            
            const width = Math.abs(endX - startX);
            const height = Math.abs(endY - startY);
            
            console.log('Mouse up. Width:', width, 'Height:', height);
            
            if (width > 20 && height > 20) {
                const centerX = Math.min(startX, endX) + width / 2;
                const centerY = Math.min(startY, endY) + height / 2;
                
                addSlot(currentSlot++, Math.round(centerX), Math.round(centerY), Math.round(width), Math.round(height));
            }
            
            isDrawing = false;
        });

        function addSlot(slotNum, x, y, width, height) {
            if (slots.length >= 10) {
                alert('Maximum 10 slots allowed');
                return;
            }
            
            console.log('Adding slot:', slotNum, 'at', x, y, 'size', width, 'x', height);
            
            const slot = {
                slot_number: slotNum,
                x_position: x,
                y_position: y,
                width: width,
                height: height,
                rotation: 0
            };
            
            slots.push(slot);
            renderSlotList();
            redrawCanvas();
        }

        function renderSlotList() {
            const html = slots.map((slot, index) => `
                <div class="slot-item" data-index="${index}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Slot ${slot.slot_number}</strong>
                        <button class="btn btn-sm btn-danger delete-slot" data-index="${index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">X Position</label>
                            <input type="number" class="form-control form-control-sm" value="${slot.x_position}" 
                                   onchange="updateSlot(${index}, 'x_position', this.value)">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Y Position</label>
                            <input type="number" class="form-control form-control-sm" value="${slot.y_position}" 
                                   onchange="updateSlot(${index}, 'y_position', this.value)">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Width</label>
                            <input type="number" class="form-control form-control-sm" value="${slot.width}" 
                                   onchange="updateSlot(${index}, 'width', this.value)">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Height</label>
                            <input type="number" class="form-control form-control-sm" value="${slot.height}" 
                                   onchange="updateSlot(${index}, 'height', this.value)">
                        </div>
                    </div>
                </div>
            `).join('');
            
            $('#slotList').html(html || '<p class="text-muted">No slots defined yet. Use Auto-Detect or draw on the poster to add slots (max 10).</p>');
            
            // Attach delete handlers
            $('.delete-slot').on('click', function() {
                const index = $(this).data('index');
                console.log('Deleting slot at index:', index);
                slots.splice(index, 1);
                // Renumber slots
                slots.forEach((slot, i) => {
                    slot.slot_number = i + 1;
                });
                currentSlot = slots.length + 1;
                renderSlotList();
                redrawCanvas();
            });
        }

        function updateSlot(index, field, value) {
            console.log('Updating slot', index, field, '=', value);
            slots[index][field] = parseInt(value);
            redrawCanvas();
        }

        $('#clearLastBtn').on('click', function() {
            if (slots.length > 0) {
                console.log('Clearing last slot');
                slots.pop();
                currentSlot--;
                renderSlotList();
                redrawCanvas();
            }
        });

        $('#clearAllBtn').on('click', function() {
            if (confirm('Are you sure you want to clear all slots?')) {
                console.log('Clearing all slots');
                slots = [];
                currentSlot = 1;
                renderSlotList();
                redrawCanvas();
            }
        });

        $('#saveSlotsBtn').on('click', function() {
            if (slots.length === 0) {
                alert('Please define at least one photo slot');
                return;
            }
            
            if (slots.length > 10) {
                alert('Maximum 10 slots allowed');
                return;
            }
            
            console.log('Saving slots:', slots);
            
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            
            $.ajax({
                url: 'save-poster-slots.php',
                type: 'POST',
                data: {
                    poster_id: posterId,
                    slots: JSON.stringify(slots)
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Save response:', response);
                    if (response.success) {
                        alert('Slots saved successfully!');
                        window.location.href = 'posters-dashboard.php';
                    } else {
                        alert('Error: ' + response.message);
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle"></i> Save Slots');
                    }
                },
                error: function() {
                    console.error('Save slots AJAX error');
                    alert('Error saving slots. Please try again.');
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle"></i> Save Slots');
                }
            });
        });
    </script>
</body>
</html>
