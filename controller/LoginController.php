<?php
require_once __DIR__ . '/Controller.php';

class LoginController extends Controller
{
    public function login($email, $password)
    {
        $this->setStatement("SELECT user_id, password FROM student_account WHERE email = :email");
        $this->statement->execute(['email' => $email]);
        $user = $this->statement->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // For now, assuming plain text password
            if ($user['password'] === $password) {
                return ['success' => true, 'user_id' => $user['user_id']];
            } else {
                return ['error' => 'Incorrect password'];
            }
        } else {
            return ['error' => 'Email not found'];
        }
    }
}
