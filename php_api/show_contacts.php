<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include 'condb.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $conn->query("SELECT * FROM contacts ORDER BY contact_id DESC");
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $contacts]);
    } elseif ($method === 'POST' || $method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);
        $subject = is_array($data) ? trim($data['subject'] ?? '') : '';
        $detail = is_array($data) ? trim($data['detail'] ?? '') : '';
        $email = is_array($data) ? trim($data['email'] ?? '') : '';

        if ($subject === '' || $detail === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "กรุณากรอกหัวข้อ รายละเอียด และอีเมลให้ถูกต้อง"]);
            exit;
        }

        if ($method === 'POST') {
            $stmt = $conn->prepare("INSERT INTO contacts (subject, detail, email, created_at) VALUES (:subject, :detail, :email, NOW())");
            $stmt->execute([
                ':subject' => $subject,
                ':detail' => $detail,
                ':email' => $email
            ]);
            echo json_encode(["success" => true, "message" => "เพิ่มข้อมูลติดต่อเรียบร้อย"]);
        } else {
            $contact_id = filter_var($data['contact_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$contact_id || $contact_id < 1) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "ไม่พบค่า contact_id"]);
                exit;
            }

            $stmt = $conn->prepare("UPDATE contacts SET subject = :subject, detail = :detail, email = :email WHERE contact_id = :id");
            $stmt->execute([
                ':subject' => $subject,
                ':detail' => $detail,
                ':email' => $email,
                ':id' => $contact_id
            ]);
            echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลติดต่อเรียบร้อย"]);
        }
    } elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        $contact_id = filter_var($data['contact_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$contact_id || $contact_id < 1) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ไม่พบค่า contact_id"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM contacts WHERE contact_id = :id");
        $stmt->bindValue(':id', $contact_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "ลบข้อมูลติดต่อเรียบร้อย"]);
        } else {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลติดต่อ"]);
        }
    } else {
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method ไม่ถูกต้อง"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาดในการจัดการข้อมูลติดต่อ"]);
}
?>