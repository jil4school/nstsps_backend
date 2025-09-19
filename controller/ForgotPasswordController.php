<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ForgotPasswordController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Check if user exists
    public function checkUser($email)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM student_account WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Send verification code (store in DB)
    public function sendCode($email, $code)
    {
        // Insert or update code in password_reset table
        $stmt = $this->conn->prepare("
            INSERT INTO password_reset (email, code, created_at)
            VALUES (:email, :code, NOW())
            ON DUPLICATE KEY UPDATE code = :code, created_at = NOW()
        ");
        $stmt->execute(['email' => $email, 'code' => $code]);

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'legaspijewel22@gmail.com'; // Gmail
            $mail->Password   = 'thcihzslblixjblb';        // App password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('student-portal@nst.com', 'NST Password Reset');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'NST Student Portal: Password Reset Request';
            $mail->Body    = "
    <p>Hello,</p>
    <p>We received a request to reset the password for your NST Student Portal account associated with this email address.</p>
    <p><strong>Your password reset code is: <span style='font-size: 1.2em;'>$code</span></strong></p>
    <p>This code is valid for <strong>5 minutes</strong>.</p>
    <p>If you did not request a password reset, you can safely ignore this email and your password will remain unchanged.</p>
    <p>Thank you,<br>
    <strong>NST Student Portal Team</strong></p>
";


            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Verify code from DB
    public function verifyCode($email, $code)
    {
        $stmt = $this->conn->prepare("
            SELECT code, created_at FROM password_reset
            WHERE email = :email LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        $created = strtotime($row['created_at']);
        $now = time();

        return $row['code'] === $code && ($now - $created) <= 300; // valid 5 mins

    }

    // Change password and delete code
    public function changePassword($email, $new_password)
    {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $stmt = $this->conn->prepare("UPDATE student_account SET password = :password WHERE email = :email");
        $stmt->execute(['password' => $hashed, 'email' => $email]);

        // Delete code after success
        $stmtDel = $this->conn->prepare("DELETE FROM password_reset WHERE email = :email");
        $stmtDel->execute(['email' => $email]);

        return $stmt->rowCount() > 0;
    }
}
