// Al Burhan Frames - Frame Editor JavaScript (Multi-Photo Support)

let canvas, ctx;
let userPhoto = null; // For single photo mode
let frameImage = null;
let currentFrame = null;

// Single photo state (backward compatibility)
let photoState = {
    scale: 1.0,
    rotation: 0,
    fineRotation: 0,
    x: 0,
    y: 0,
    isDragging: false,
    dragStartX: 0,
    dragStartY: 0,
    isPinching: false,
    initialPinchDistance: 0,
    initialScale: 1.0
};

// Multi-photo mode state
let photoStates = {}; // { 1: {photo: Image, scale: 1, rotation: 0, x: 0, y: 0}, 2: {...}, ... }
let currentSlot = null; // Currently selected slot for editing
let frameSlots = []; // Array of slot definitions from database

// Initialize the frame editor
function initFrameEditor(frameData) {
    currentFrame = frameData;
    canvas = document.getElementById('photoCanvas');
    ctx = canvas.getContext('2d');
    
    // Load frame image
    loadFrameImage(frameData.path);
    
    // Check if multi-photo mode
    if (frameData.isMultiPhoto) {
        frameSlots = frameData.slots || [];
        initMultiPhotoMode();
    } else {
        initSinglePhotoMode();
    }
    
    // Setup event listeners
    setupEventListeners();
}

// Load frame image
function loadFrameImage(framePath) {
    frameImage = new Image();
    frameImage.crossOrigin = 'anonymous';
    frameImage.onload = function() {
        // Set canvas size to match frame
        canvas.width = frameImage.width;
        canvas.height = frameImage.height;
        
        // Show canvas immediately with frame visible
        $('#photoCanvas').addClass('active');
        $('#canvasPlaceholder').hide();
        
        redrawCanvas();
    };
    frameImage.onerror = function() {
        alert('Error: Failed to load frame image. Please make sure the frame file exists on the server.\n\nPath: ' + framePath);
    };
    frameImage.src = framePath;
}

// Initialize multi-photo mode
function initMultiPhotoMode() {
    // Initialize photo states for each slot
    frameSlots.forEach(slot => {
        photoStates[slot.slot_number] = {
            photo: null,
            scale: 1.0,
            rotation: 0,
            fineRotation: 0,
            x: 0,
            y: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            isPinching: false,
            initialPinchDistance: 0,
            initialScale: 1.0
        };
    });
    
    // Setup upload handlers for each slot
    frameSlots.forEach(slot => {
        const slotNum = slot.slot_number;
        const uploadBox = $(`.slot-upload-box[data-slot="${slotNum}"]`);
        const fileInput = $(`#photoInput_slot${slotNum}`);
        
        uploadBox.on('click', function(e) {
            if (!$(e.target).hasClass('change-slot-photo') && !$(e.target).parent().hasClass('change-slot-photo')) {
                fileInput.click();
            }
        });
        
        $('.change-slot-photo', uploadBox).on('click', function(e) {
            e.stopPropagation();
            fileInput.click();
        });
        
        fileInput.on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                handleSlotPhotoUpload(slotNum, file);
            }
        });
    });
}

// Initialize single photo mode
function initSinglePhotoMode() {
    // Nothing special needed, uses existing logic
}

// Handle slot photo upload
function handleSlotPhotoUpload(slotNumber, file) {
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
            // Store photo in photoStates
            photoStates[slotNumber].photo = img;
            
            // Reset transformations for this slot
            photoStates[slotNumber].scale = 1.0;
            photoStates[slotNumber].rotation = 0;
            photoStates[slotNumber].x = 0;
            photoStates[slotNumber].y = 0;
            
            // Update UI
            const uploadBox = $(`.slot-upload-box[data-slot="${slotNumber}"]`);
            uploadBox.find('.slot-upload-content').hide();
            uploadBox.find('.slot-preview img').attr('src', e.target.result);
            uploadBox.find('.slot-preview').show();
            uploadBox.addClass('has-photo');
            
            // Select this slot for editing
            selectSlot(slotNumber);
            
            // Redraw canvas
            redrawCanvas();
            
            // Show controls
            $('#photoControls').show();
            $('#noPhotoMessage').hide();
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Select a slot for editing
function selectSlot(slotNumber) {
    currentSlot = slotNumber;
    
    // Update UI to show which slot is selected
    $('.slot-upload-box').removeClass('selected');
    $(`.slot-upload-box[data-slot="${slotNumber}"]`).addClass('selected');
    
    // Update controls to reflect selected slot's state
    const state = photoStates[slotNumber];
    if (state && state.photo) {
        $('#zoomSlider').val(Math.round(state.scale * 100));
        $('#zoomValue').text(Math.round(state.scale * 100) + '%');
        
        $('.btn-group button').removeClass('active');
        $(`#rotate${state.rotation}`).addClass('active');
        
        $('#fineRotateSlider').val(state.fineRotation || 0);
        $('#fineRotateValue').text((state.fineRotation || 0) + '°');
    } else {
        // Reset controls if slot has no photo
        $('#zoomSlider').val(100);
        $('#zoomValue').text('100%');
        $('.btn-group button').removeClass('active');
        $('#rotate0').addClass('active');
        $('#fineRotateSlider').val(0);
        $('#fineRotateValue').text('0°');
    }
    
    // Update slot indicator
    $('#currentSlotText').text(`Slot ${slotNumber}`);
}

// Setup all event listeners
function setupEventListeners() {
    if (!currentFrame.isMultiPhoto) {
        // Single photo mode listeners
        $('#uploadArea').on('click', function(e) {
            if (e.target.id !== 'photoInput') {
                document.getElementById('photoInput').click();
            }
        });
        
        $('#photoInput').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                handlePhotoUpload(file);
            }
        });
        
        // Drag and drop for single photo
        const uploadArea = document.getElementById('uploadArea');
        if (uploadArea) {
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                const file = e.dataTransfer.files[0];
                if (file && file.type.match('image.*')) {
                    handlePhotoUpload(file);
                }
            });
        }
    }
    
    // Change photo button
    $('#changePhotoBtn').on('click', function() {
        if (currentFrame.isMultiPhoto && currentSlot) {
            $(`#photoInput_slot${currentSlot}`).click();
        } else {
            $('#photoInput').click();
        }
    });
    
    // Zoom slider
    $('#zoomSlider').on('input', function() {
        const value = $(this).val();
        if (currentFrame.isMultiPhoto && currentSlot) {
            photoStates[currentSlot].scale = value / 100;
        } else {
            photoState.scale = value / 100;
        }
        $('#zoomValue').text(value + '%');
        redrawCanvas();
    });
    
    // Rotation buttons
    $('#rotate0').on('click', function() { setRotation(0); });
    $('#rotate90').on('click', function() { setRotation(90); });
    $('#rotate180').on('click', function() { setRotation(180); });
    $('#rotate270').on('click', function() { setRotation(270); });
    
    // Fine rotation slider
    $('#fineRotateSlider').on('input', function() {
        const value = parseInt($(this).val());
        if (currentFrame.isMultiPhoto && currentSlot) {
            photoStates[currentSlot].fineRotation = value;
        } else {
            photoState.fineRotation = value;
        }
        $('#fineRotateValue').text(value + '°');
        redrawCanvas();
    });
    
    // Reset button
    $('#resetBtn').on('click', function() {
        resetPhotoState();
    });
    
    // Download button
    $('#downloadBtn').on('click', function() {
        downloadPhoto();
    });
    
    // Canvas mouse events for dragging
    canvas.addEventListener('mousedown', startDrag);
    canvas.addEventListener('mousemove', drag);
    canvas.addEventListener('mouseup', endDrag);
    canvas.addEventListener('mouseleave', endDrag);
    
    // Canvas touch events for mobile
    canvas.addEventListener('touchstart', handleTouchStart);
    canvas.addEventListener('touchmove', handleTouchMove);
    canvas.addEventListener('touchend', handleTouchEnd);
}

// Handle single photo upload
function handlePhotoUpload(file) {
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
        userPhoto = new Image();
        userPhoto.onload = function() {
            // Reset state for new photo
            resetPhotoState();
            
            // Hide upload area, show canvas and controls
            $('#uploadArea').hide();
            $('#canvasPlaceholder').hide();
            $('#photoCanvas').addClass('active');
            $('#photoControls').show();
            $('#noPhotoMessage').hide();
            
            // Redraw canvas
            redrawCanvas();
        };
        userPhoto.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Set rotation
function setRotation(degrees) {
    if (currentFrame.isMultiPhoto && currentSlot) {
        photoStates[currentSlot].rotation = degrees;
        photoStates[currentSlot].fineRotation = 0;
    } else {
        photoState.rotation = degrees;
        photoState.fineRotation = 0;
    }
    
    $('.btn-group button').removeClass('active');
    $('#rotate' + degrees).addClass('active');
    $('#fineRotateSlider').val(0);
    $('#fineRotateValue').text('0°');
    
    redrawCanvas();
}

// Reset photo state
function resetPhotoState() {
    if (currentFrame.isMultiPhoto && currentSlot) {
        photoStates[currentSlot].scale = 1.0;
        photoStates[currentSlot].rotation = 0;
        photoStates[currentSlot].fineRotation = 0;
        photoStates[currentSlot].x = 0;
        photoStates[currentSlot].y = 0;
        photoStates[currentSlot].isDragging = false;
    } else {
        photoState = {
            scale: 1.0,
            rotation: 0,
            fineRotation: 0,
            x: 0,
            y: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0
        };
    }
    
    $('#zoomSlider').val(100);
    $('#zoomValue').text('100%');
    $('.btn-group button').removeClass('active');
    $('#rotate0').addClass('active');
    $('#fineRotateSlider').val(0);
    $('#fineRotateValue').text('0°');
    
    redrawCanvas();
}

// Redraw canvas
function redrawCanvas() {
    if (!ctx || !frameImage) return;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    if (currentFrame.isMultiPhoto) {
        // Draw each slot's photo
        frameSlots.forEach(slot => {
            const state = photoStates[slot.slot_number];
            if (state && state.photo) {
                drawPhotoInSlot(state.photo, slot, state);
            }
        });
    } else {
        // Single photo drawing
        if (userPhoto) {
            ctx.save();
            
            const centerX = canvas.width / 2 + photoState.x;
            const centerY = canvas.height / 2 + photoState.y;
            
            ctx.translate(centerX, centerY);
            ctx.rotate(((photoState.rotation + (photoState.fineRotation || 0)) * Math.PI) / 180);
            
            const scaledWidth = userPhoto.width * photoState.scale;
            const scaledHeight = userPhoto.height * photoState.scale;
            
            ctx.drawImage(
                userPhoto,
                -scaledWidth / 2,
                -scaledHeight / 2,
                scaledWidth,
                scaledHeight
            );
            
            ctx.restore();
        }
    }
    
    // Draw frame on top
    ctx.drawImage(frameImage, 0, 0);
}

// Draw photo in slot
function drawPhotoInSlot(photo, slotDef, state) {
    ctx.save();
    
    // Define slot clipping region (rectangle)
    const x = slotDef.x_position - slotDef.width / 2;
    const y = slotDef.y_position - slotDef.height / 2;
    
    ctx.beginPath();
    ctx.rect(x, y, slotDef.width, slotDef.height);
    ctx.closePath();
    ctx.clip();
    
    // Translate to slot center + user offset
    ctx.translate(slotDef.x_position + state.x, slotDef.y_position + state.y);
    
    // Apply rotation (slot rotation + user rotation + fine rotation)
    ctx.rotate(((slotDef.rotation + state.rotation + (state.fineRotation || 0)) * Math.PI) / 180);
    
    // Draw photo
    const scaledWidth = photo.width * state.scale;
    const scaledHeight = photo.height * state.scale;
    ctx.drawImage(photo, -scaledWidth / 2, -scaledHeight / 2, scaledWidth, scaledHeight);
    
    ctx.restore();
}

// Dragging functions
function startDrag(e) {
    if (currentFrame.isMultiPhoto) {
        if (!currentSlot || !photoStates[currentSlot] || !photoStates[currentSlot].photo) return;
        
        photoStates[currentSlot].isDragging = true;
        photoStates[currentSlot].dragStartX = e.clientX - photoStates[currentSlot].x;
        photoStates[currentSlot].dragStartY = e.clientY - photoStates[currentSlot].y;
    } else {
        if (!userPhoto) return;
        
        photoState.isDragging = true;
        photoState.dragStartX = e.clientX - photoState.x;
        photoState.dragStartY = e.clientY - photoState.y;
    }
    canvas.style.cursor = 'grabbing';
}

function drag(e) {
    if (currentFrame.isMultiPhoto) {
        if (!currentSlot || !photoStates[currentSlot] || !photoStates[currentSlot].isDragging) return;
        
        e.preventDefault();
        photoStates[currentSlot].x = e.clientX - photoStates[currentSlot].dragStartX;
        photoStates[currentSlot].y = e.clientY - photoStates[currentSlot].dragStartY;
    } else {
        if (!photoState.isDragging) return;
        
        e.preventDefault();
        photoState.x = e.clientX - photoState.dragStartX;
        photoState.y = e.clientY - photoState.dragStartY;
    }
    
    redrawCanvas();
}

function endDrag() {
    if (currentFrame.isMultiPhoto && currentSlot && photoStates[currentSlot]) {
        photoStates[currentSlot].isDragging = false;
    } else if (photoState.isDragging) {
        photoState.isDragging = false;
    }
    canvas.style.cursor = 'move';
}

// Touch events for mobile
function handleTouchStart(e) {
    const state = currentFrame.isMultiPhoto && currentSlot ? photoStates[currentSlot] : photoState;
    const photo = currentFrame.isMultiPhoto && currentSlot ? (photoStates[currentSlot] ? photoStates[currentSlot].photo : null) : userPhoto;
    
    if (!photo) return;
    
    e.preventDefault();
    
    if (e.touches.length === 2) {
        state.isPinching = true;
        state.isDragging = false;
        state.initialScale = state.scale;
        
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        state.initialPinchDistance = Math.hypot(
            touch2.clientX - touch1.clientX,
            touch2.clientY - touch1.clientY
        );
    } else if (e.touches.length === 1) {
        state.isDragging = true;
        state.isPinching = false;
        const touch = e.touches[0];
        state.dragStartX = touch.clientX - state.x;
        state.dragStartY = touch.clientY - state.y;
    }
}

function handleTouchMove(e) {
    const state = currentFrame.isMultiPhoto && currentSlot ? photoStates[currentSlot] : photoState;
    const photo = currentFrame.isMultiPhoto && currentSlot ? (photoStates[currentSlot] ? photoStates[currentSlot].photo : null) : userPhoto;
    
    if (!photo) return;
    e.preventDefault();
    
    if (e.touches.length === 2 && state.isPinching) {
        const touch1 = e.touches[0];
        const touch2 = e.touches[1];
        
        const currentDistance = Math.hypot(
            touch2.clientX - touch1.clientX,
            touch2.clientY - touch1.clientY
        );
        
        const scaleChange = currentDistance / state.initialPinchDistance;
        let newScale = state.initialScale * scaleChange;
        newScale = Math.max(0.2, Math.min(3.0, newScale));
        
        state.scale = newScale;
        
        const zoomPercent = Math.round(newScale * 100);
        $('#zoomSlider').val(zoomPercent);
        $('#zoomValue').text(zoomPercent + '%');
        
        redrawCanvas();
    } else if (e.touches.length === 1 && state.isDragging) {
        const touch = e.touches[0];
        state.x = touch.clientX - state.dragStartX;
        state.y = touch.clientY - state.dragStartY;
        
        redrawCanvas();
    }
}

function handleTouchEnd(e) {
    const state = currentFrame.isMultiPhoto && currentSlot ? photoStates[currentSlot] : photoState;
    
    if (e.touches.length < 2) {
        state.isPinching = false;
    }
    if (e.touches.length === 0) {
        state.isDragging = false;
        state.isPinching = false;
    }
}

// Download photo
function downloadPhoto() {
    if (currentFrame.isMultiPhoto) {
        downloadMultiPhoto();
    } else {
        downloadSinglePhoto();
    }
}

// Download single photo
function downloadSinglePhoto() {
    if (!userPhoto) {
        alert('Please upload a photo first');
        return;
    }
    
    $('#loadingOverlay').show();
    
    const canvasData = canvas.toDataURL('image/png');
    
    $.ajax({
        url: 'process.php',
        type: 'POST',
        data: {
            frame_id: currentFrame.id,
            photo_data: canvasData,
            is_multi_photo: '0'
        },
        success: function(response) {
            $('#loadingOverlay').hide();
            
            if (response.success) {
                downloadBase64Image(response.image_data, response.filename);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#loadingOverlay').hide();
            alert('Error processing photo. Please try again.');
        }
    });
}

// Download multi-photo
function downloadMultiPhoto() {
    // Check if all slots have photos
    let hasEmptySlots = false;
    for (let slot of frameSlots) {
        if (!photoStates[slot.slot_number] || !photoStates[slot.slot_number].photo) {
            hasEmptySlots = true;
            break;
        }
    }
    
    if (hasEmptySlots) {
        if (!confirm('Some slots are empty. Continue anyway?')) {
            return;
        }
    }
    
    // Prepare slot photo data
    let slotPhotos = {};
    for (let slot of frameSlots) {
        const state = photoStates[slot.slot_number];
        if (state && state.photo) {
            // Convert image to base64
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = state.photo.width;
            tempCanvas.height = state.photo.height;
            const tempCtx = tempCanvas.getContext('2d');
            tempCtx.drawImage(state.photo, 0, 0);
            
            slotPhotos[slot.slot_number] = {
                photo_data: tempCanvas.toDataURL('image/png'),
                scale: state.scale,
                rotation: state.rotation,
                fineRotation: state.fineRotation || 0,
                x: state.x,
                y: state.y
            };
        }
    }
    
    $('#loadingOverlay').show();
    
    $.ajax({
        url: 'process.php',
        type: 'POST',
        data: {
            frame_id: currentFrame.id,
            is_multi_photo: '1',
            slot_photos: JSON.stringify(slotPhotos)
        },
        success: function(response) {
            $('#loadingOverlay').hide();
            if (response.success) {
                downloadBase64Image(response.image_data, response.filename);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#loadingOverlay').hide();
            alert('Error processing photos. Please try again.');
        }
    });
}

// Helper function to download base64 image
function downloadBase64Image(base64Data, filename) {
    const byteCharacters = atob(base64Data);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const blob = new Blob([byteArray], {type: 'image/jpeg'});
    
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
}

// Expose functions globally
window.initFrameEditor = initFrameEditor;
