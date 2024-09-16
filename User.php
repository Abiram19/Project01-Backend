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
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            return ['error' => 'Database error: failed to prepare statement'];
        }

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bind_param("sss", $this->username, $this->email, $this->password);

        if ($stmt->execute()) {
            return ['success' => 'User registered successfully.'];
        } else {
            return ['error' => 'Registration failed. Please try again.'];
        }
    }

    public function login($rememberMe = false)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ?";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            return ['error' => 'Database error: failed to prepare statement'];
        }

        $this->username = htmlspecialchars(strip_tags($this->username));

        $stmt->bind_param("s", $this->username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
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
