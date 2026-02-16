<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Get frame ID
$frame_id = $_GET['id'] ?? 0;

if ($frame_id <= 0) {
    header('Location: dashboard.php');
    exit();
}

// Fetch frame details
$stmt = $conn->prepare("SELECT * FROM frames WHERE id = ?");
$stmt->bind_param("i", $frame_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit();
}

$frame = $result->fetch_assoc();
$stmt->close();

// Fetch existing slots
$stmt = $conn->prepare("SELECT * FROM frame_slots WHERE frame_id = ? ORDER BY slot_number ASC");
$stmt->bind_param("i", $frame_id);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Define Photo Slots - <?= escape($frame['frame_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        #slotCanvas {
            border: 2px solid #333;
            cursor: crosshair;
            max-width: 100%;
            background-color: #f8f9fa;
        }
        .slot-item {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        .slot-item.active {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }
        .slot-item input {
            width: 80px;
            margin-right: 5px;
        }
        .slot-controls {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="admin-page">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-grid-3x3-gap"></i> Al Burhan Frames Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row mb-3">
            <div class="col">
                <h3><i class="bi bi-grid-3x2"></i> Define Photo Slots: <?= escape($frame['frame_name']) ?></h3>
                <p class="text-muted">Click and drag on the frame to create photo slots</p>
            </div>
        </div>

        <div class="row">
            <!-- Canvas Area -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" id="addSlotBtn">
                                <i class="bi bi-plus-circle"></i> Add Slot (Draw on Frame)
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
                        <h5 class="card-title">Photo Slots</h5>
                        <div class="slot-controls" id="slotList">
                            <!-- Dynamically generated -->
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button type="button" class="btn btn-success btn-lg" id="saveSlotsBtn">
                                <i class="bi bi-check-circle"></i> Save Slots
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
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
        const frameId = <?= $frame['id'] ?>;
        const framePath = '<?= $frame['frame_path'] ?>';
        
        let canvas, ctx;
        let frameImage = null;
        let slots = <?= json_encode($slots) ?>;
        let isDrawing = false;
        let startX, startY;
        let currentSlot = slots.length + 1;
        let drawingMode = false;

        $(document).ready(function() {
            canvas = document.getElementById('slotCanvas');
            ctx = canvas.getContext('2d');
            
            loadFrame();
            renderSlotList();
        });

        function loadFrame() {
            frameImage = new Image();
            frameImage.onload = function() {
                canvas.width = frameImage.width;
                canvas.height = frameImage.height;
                redrawCanvas();
            };
            frameImage.src = '../' + framePath;
        }

        function redrawCanvas() {
            if (!frameImage) return;
            
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(frameImage, 0, 0);
            
            // Draw all slots
            slots.forEach((slot, index) => {
                drawSlot(slot, index);
            });
        }

        function drawSlot(slot, index) {
            const x = slot.x_position - slot.width / 2;
            const y = slot.y_position - slot.height / 2;
            
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

        // Drawing mode toggle
        $('#addSlotBtn').on('click', function() {
            drawingMode = !drawingMode;
            if (drawingMode) {
                $(this).addClass('active').text('Drawing Mode (Click & Drag)');
                canvas.style.cursor = 'crosshair';
            } else {
                $(this).removeClass('active').html('<i class="bi bi-plus-circle"></i> Add Slot (Draw on Frame)');
                canvas.style.cursor = 'default';
            }
        });

        // Canvas drawing
        canvas.addEventListener('mousedown', function(e) {
            if (!drawingMode) return;
            
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            startX = e.clientX - rect.left;
            startY = e.clientY - rect.top;
        });

        canvas.addEventListener('mousemove', function(e) {
            if (!isDrawing || !drawingMode) return;
            
            const rect = canvas.getBoundingClientRect();
            const endX = e.clientX - rect.left;
            const endY = e.clientY - rect.top;
            
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
            const endX = e.clientX - rect.left;
            const endY = e.clientY - rect.top;
            
            const width = Math.abs(endX - startX);
            const height = Math.abs(endY - startY);
            
            if (width > 20 && height > 20) {
                const centerX = Math.min(startX, endX) + width / 2;
                const centerY = Math.min(startY, endY) + height / 2;
                
                addSlot(currentSlot++, Math.round(centerX), Math.round(centerY), Math.round(width), Math.round(height));
            }
            
            isDrawing = false;
        });

        function addSlot(slotNum, x, y, width, height) {
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
            
            $('#slotList').html(html || '<p class="text-muted">No slots defined yet. Draw on the frame to add slots.</p>');
            
            // Attach delete handlers
            $('.delete-slot').on('click', function() {
                const index = $(this).data('index');
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
            slots[index][field] = parseInt(value);
            redrawCanvas();
        }

        $('#clearLastBtn').on('click', function() {
            if (slots.length > 0) {
                slots.pop();
                currentSlot--;
                renderSlotList();
                redrawCanvas();
            }
        });

        $('#clearAllBtn').on('click', function() {
            if (confirm('Are you sure you want to clear all slots?')) {
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
            
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            
            $.ajax({
                url: 'save-slots.php',
                type: 'POST',
                data: {
                    frame_id: frameId,
                    slots: JSON.stringify(slots)
                },
                success: function(response) {
                    if (response.success) {
                        alert('Slots saved successfully!');
                        window.location.href = 'dashboard.php';
                    } else {
                        alert('Error: ' + response.message);
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle"></i> Save Slots');
                    }
                },
                error: function() {
                    alert('Error saving slots. Please try again.');
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle"></i> Save Slots');
                }
            });
        });
    </script>
</body>
</html>
