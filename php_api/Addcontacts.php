<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method ไม่ถูกต้อง"]);
    exit;
}

include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    !is_array($data) ||
    trim($data['subject'] ?? '') === '' ||
    trim($data['detail'] ?? '') === '' ||
    !filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL)
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "กรุณากรอกหัวข้อ รายละเอียด และอีเมลให้ถูกต้อง"
    ]);
    exit;
}

try {
        $sql = "INSERT INTO contacts
            (subject, detail, email, created_at)
            VALUES
            (:subject, :detail, :email, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':subject' => $data['subject'],
        ':detail' => $data['detail'],
        ':email' => $data['email']
    ]);

    echo json_encode([
        "success" => true,
        "message" => "เพิ่มข้อมูลเรียบร้อย"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "ไม่สามารถบันทึกข้อมูลติดต่อได้"
    ]);
}
