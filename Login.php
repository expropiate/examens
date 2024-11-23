<?php

/**
 * Class Login
 * Handles the user's login and logout process
 */
class Login
{
    /**
     * @var object The database connection
     */
    private $db_connection = null;

    /**
     * @var array Collection of error messages
     */
    public $errors = array();

    /**
     * @var array Collection of success / neutral messages
     */
    public $messages = array();

    /**
     * Constructor: Automatically starts when an object of this class is created
     */
    public function __construct()
    {
        // Create/read session, absolutely necessary
        session_start();

        // Check the possible login actions:
        if (isset($_GET["logout"])) {
            $this->doLogout();
        } elseif (isset($_POST["login"])) {
            $this->handleLogin();
        }
    }

    /**
     * Handle the login process
     */
    private function handleLogin()
    {
        if ($this->validateLoginForm()) {
            $this->connectToDatabase();
            if ($this->db_connection && !$this->db_connection->connect_errno) {
                $this->authenticateUser();
            } else {
                $this->errors[] = "Problema de conexión de base de datos.";
            }
        }
    }

    /**
     * Validate login form data
     * @return bool
     */
    private function validateLoginForm()
    {
        if (empty($_POST['user_name'])) {
            $this->errors[] = "El campo de usuario está vacío.";
            return false;
        }

        if (empty($_POST['user_password'])) {
            $this->errors[] = "El campo de contraseña está vacío.";
            return false;
        }

        return true;
    }

    /**
     * Establish database connection
     */
    private function connectToDatabase()
    {
        $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if (!$this->db_connection->set_charset("utf8")) {
            $this->errors[] = $this->db_connection->error;
        }
    }

    /**
     * Authenticate user with database
     */
    private function authenticateUser()
    {
        $user_name = $this->sanitizeInput($_POST['user_name']);

        $sql = $this->buildUserQuery($user_name);
        $result = $this->db_connection->query($sql);

        if ($result->num_rows == 1) {
            $result_row = $result->fetch_object();
            $this->verifyPassword($result_row);
        } else {
            $this->errors[] = "Usuario y/o contraseña no coinciden.";
        }
    }

    /**
     * Build query to fetch user information
     * @param string $user_name
     * @return string
     */
    private function buildUserQuery($user_name)
    {
        return "SELECT user_id, user_name, user_email, user_password_hash
                FROM users
                WHERE user_name = '$user_name' OR user_email = '$user_name';";
    }

    /**
     * Verify password and set user session if valid
     * @param object $result_row
     */
    private function verifyPassword($result_row)
    {
        if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
            $this->setUserSession($result_row);
        } else {
            $this->errors[] = "Usuario y/o contraseña no coinciden.";
        }
    }

    /**
     * Sanitize input data
     * @param string $input
     * @return string
     */
    private function sanitizeInput($input)
    {
        return $this->db_connection->real_escape_string($input);
    }

    /**
     * Set user session data
     * @param object $result_row
     */
    private function setUserSession($result_row)
    {
        $_SESSION['user_id'] = $result_row->user_id;
        $_SESSION['user_name'] = $result_row->user_name;
        $_SESSION['user_email'] = $result_row->user_email;
        $_SESSION['user_login_status'] = 1;
    }

    /**
     * Perform the logout
     */
    public function doLogout()
    {
        $_SESSION = array();
        session_destroy();
        $this->messages[] = "Has sido desconectado.";
    }

    /**
     * Return the current state of the user's login
     * @return bool
     */
    public function isUserLoggedIn()
    {
        return isset($_SESSION['user_login_status']) && $_SESSION['user_login_status'] == 1;
    }
}
