<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Database credentials
$host = 'localhost:3307';
$db = 'imageannotationsdb';
$user = 'root';
$pass = '';

try {
    // Create a database connection
    $conn = new mysqli($host, $user, $pass, $db);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Validate and get form data
            $description = $_POST['description'] ?? '';
            $priority = $_POST['priority'] ?? 'low';
            $assignedTo = $_POST['assignedTo'] ?? '';
            $annotations = json_decode($_POST['annotations'], true);

            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
                throw new Exception("No image uploaded or upload error.");
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $imageExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $imageExt;
            $imagePath = $uploadDir . $imageName;

            // Move uploaded image
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                throw new Exception("Failed to upload image.");
            }

            // Insert image data
            $stmt = $conn->prepare("INSERT INTO images (image_path, description, priority, assigned_to) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param('ssss', $imagePath, $description, $priority, $assignedTo);
            if (!$stmt->execute()) {
                throw new Exception("Failed to save image data: " . $stmt->error);
            }

            $imageId = $stmt->insert_id;
            $stmt->close();

            // Insert annotations
            if (!empty($annotations)) {
                $stmt = $conn->prepare("INSERT INTO annotations (image_id, x, y, description) VALUES (?, ?, ?, ?)");
                
                foreach ($annotations as $annotation) {
                    $x = $annotation['x'];
                    $y = $annotation['y'];
                    $desc = $annotation['description'];
                    $stmt->bind_param('iiis', $imageId, $x, $y, $desc);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to save annotation: " . $stmt->error);
                    }
                }
                $stmt->close();
            }

            // Commit transaction
            $conn->commit();
            
            echo json_encode([
                "status" => "success",
                "message" => "Image and annotations saved successfully.",
                "image_id" => $imageId
            ]);

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
    } else {
        throw new Exception("Invalid request method.");
    }

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>