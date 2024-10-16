<?php

class User
{
    private $conn;
    private $table_name = "users";

    public $username;
    public $email;
    public $password;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function register()
    {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            return ['error' => 'Database error: failed to prepare statement'];
        }

        // Clean data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);

        // Execute the query
        if ($stmt->execute()) {
            return ['success' => 'User registered successfully.'];
        } else {
            return ['error' => 'Registration failed. Please try again.'];
        }
    }

    public function login($rememberMe = false)
    {
        session_start();
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            return ['error' => 'Database error: failed to prepare statement'];
        }

        // Clean data
        $this->username = htmlspecialchars(strip_tags($this->username));

        // Bind parameter
        $stmt->bindParam(':username', $this->username);

        // Execute the query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (password_verify($this->password, $row['password'])) {
                $_SESSION['username'] = $this->username;
                $_SESSION['userrole'] = $row['userole'];
                if ($rememberMe) {
                    setcookie('username', $this->username, time() + (86400 * 30), "/");
                    setcookie('userrole', $row['userole'], time() + (86400 * 30), "/");
                }
                return ['success' => 'User logged in successfully.', 'userrole' => $row['userole']];
            } else {
                return ['error' => 'Invalid password.'];
            }
        } else {
            return ['error' => 'User not found.'];
        }
    }
}
