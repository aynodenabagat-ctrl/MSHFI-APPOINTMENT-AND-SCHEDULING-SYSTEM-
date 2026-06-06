<?php
function getImageCount($pdo, $appointmentId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointment_images WHERE appointment_id = ?");
    $stmt->execute([$appointmentId]);
    return $stmt->fetchColumn();
}

function getAppointmentImages($pdo, $appointmentId) {
    $stmt = $pdo->prepare("SELECT * FROM appointment_images WHERE appointment_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$appointmentId]);
    return $stmt->fetchAll();
}

function getPatientImages($pdo, $patientId, $doctorId = null) {
    if ($doctorId) {
        $stmt = $pdo->prepare("
            SELECT ai.*, a.appointment_date 
            FROM appointment_images ai
            JOIN appointments a ON ai.appointment_id = a.id
            WHERE ai.patient_id = ? AND a.doctor_id = ?
            ORDER BY ai.uploaded_at DESC
        ");
        $stmt->execute([$patientId, $doctorId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT ai.*, a.appointment_date 
            FROM appointment_images ai
            JOIN appointments a ON ai.appointment_id = a.id
            WHERE ai.patient_id = ?
            ORDER BY ai.uploaded_at DESC
        ");
        $stmt->execute([$patientId]);
    }
    return $stmt->fetchAll();
}

function uploadAppointmentImage($pdo, $appointmentId, $patientId, $file, $caption = '') {
    require_once __DIR__ . '/../config/app.php';

    $count = getImageCount($pdo, $appointmentId);
    if ($count >= MAX_IMAGES_PER_APPOINTMENT) {
        return 'Maximum of ' . MAX_IMAGES_PER_APPOINTMENT . ' images per appointment reached.';
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload error. Please try again.';
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return 'File is too large. Max size is 5MB.';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return 'Only JPG, PNG, GIF, and WebP files are allowed.';
    }

    $fileName = uniqid('img_') . '.' . $ext;
    $destPath = UPLOAD_PATH . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return 'Failed to save file.';
    }

    $stmt = $pdo->prepare("INSERT INTO appointment_images (appointment_id, patient_id, file_name, original_name, caption) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$appointmentId, $patientId, $fileName, $file['name'], $caption]);

    return null;
}

function deleteAppointmentImage($pdo, $imageId, $patientId) {
    $stmt = $pdo->prepare("SELECT * FROM appointment_images WHERE id = ? AND patient_id = ?");
    $stmt->execute([$imageId, $patientId]);
    $img = $stmt->fetch();

    if ($img) {
        $path = UPLOAD_PATH . $img['file_name'];
        if (file_exists($path)) unlink($path);

        $stmt = $pdo->prepare("DELETE FROM appointment_images WHERE id = ?");
        return $stmt->execute([$imageId]);
    }
    return false;
}
