<?php
require_once '../config/database.php';

class Registration {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function registerStudent($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO student_registrations 
                (full_name, birth_date, gender, parent_name, address, phone, email, previous_school) 
                VALUES 
                (:full_name, :birth_date, :gender, :parent_name, :address, :phone, :email, :previous_school)
            ");

            return $stmt->execute([
                ':full_name' => $data['full_name'],
                ':birth_date' => $data['birth_date'],
                ':gender' => $data['gender'],
                ':parent_name' => $data['parent_name'],
                ':address' => $data['address'],
                ':phone' => $data['phone'],
                ':email' => $data['email'],
                ':previous_school' => $data['previous_school']
            ]);
        } catch(PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return false;
        }
    }

    public function validateRegistrationData($data) {
        $errors = [];

        // Validate full name
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Nama lengkap harus diisi';
        }

        // Validate birth date
        if (empty($data['birth_date'])) {
            $errors['birth_date'] = 'Tanggal lahir harus diisi';
        } elseif (!strtotime($data['birth_date'])) {
            $errors['birth_date'] = 'Format tanggal lahir tidak valid';
        }

        // Validate gender
        if (empty($data['gender']) || !in_array($data['gender'], ['L', 'P'])) {
            $errors['gender'] = 'Jenis kelamin harus dipilih';
        }

        // Validate parent name
        if (empty($data['parent_name'])) {
            $errors['parent_name'] = 'Nama orang tua harus diisi';
        }

        // Validate address
        if (empty($data['address'])) {
            $errors['address'] = 'Alamat harus diisi';
        }

        // Validate phone
        if (empty($data['phone'])) {
            $errors['phone'] = 'Nomor telepon harus diisi';
        } elseif (!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
            $errors['phone'] = 'Format nomor telepon tidak valid';
        }

        // Validate email
        if (empty($data['email'])) {
            $errors['email'] = 'Email harus diisi';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid';
        }

        return $errors;
    }

    public function getRegistrationStatus($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT status FROM student_registrations WHERE email = :email ORDER BY registration_date DESC LIMIT 1");
            $stmt->execute([':email' => $email]);
            $result = $stmt->fetch();
            return $result ? $result['status'] : null;
        } catch(PDOException $e) {
            error_log("Status Check Error: " . $e->getMessage());
            return null;
        }
    }

    public function getAllRegistrations($status = null) {
        try {
            $query = "SELECT * FROM student_registrations";
            if ($status) {
                $query .= " WHERE status = :status";
            }
            $query .= " ORDER BY registration_date DESC";

            $stmt = $this->pdo->prepare($query);
            if ($status) {
                $stmt->bindValue(':status', $status);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Fetch Registrations Error: " . $e->getMessage());
            return [];
        }
    }

    public function updateRegistrationStatus($id, $status) {
        try {
            $stmt = $this->pdo->prepare("UPDATE student_registrations SET status = :status WHERE id = :id");
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
        } catch(PDOException $e) {
            error_log("Status Update Error: " . $e->getMessage());
            return false;
        }
    }
}

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $registration = new Registration($pdo);
    
    $registrationData = [
        'full_name' => $_POST['full_name'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'parent_name' => $_POST['parent_name'] ?? '',
        'address' => $_POST['address'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'previous_school' => $_POST['previous_school'] ?? ''
    ];

    $errors = $registration->validateRegistrationData($registrationData);

    if (empty($errors)) {
        if ($registration->registerStudent($registrationData)) {
            header('Location: ../registration-success.php');
            exit;
        } else {
            $error_message = 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi.';
        }
    }
}
?>
