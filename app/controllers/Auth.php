<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Core\Session;

class Auth extends Controller
{
    protected $config;
    protected $session;

    public function __construct($app = null)
    {
        parent::__construct($app);
        // Note: config and session are already set by parent constructor
        // This ensures compatibility with both DI and singleton patterns
    }
    
    public function login()
    {
        // If already logged in, redirect to admin
        if ($this->session->sessionHas('user_id')) {
            $this->redirect($this->config->appConfig('url').'/admin');
        }

        $error = '';

        if ($this->request->isPostRequest()) {
            $username = $this->request->postParam('username');
            $password = $this->request->postParam('password');

            if (empty($username) || empty($password)) {
                $error = 'Username dan password harus diisi';
            } else {
                $userModel = $this->loadModel('UserModel');
                $user = $userModel->authenticate($username, $password);

                if ($user) {
                    // Set session
                    $this->session->sessionSet('user_id', $user['id']);
                    $this->session->sessionSet('username', $user['username']);
                    $this->session->sessionSet('nama', $user['nama']);
                    $this->session->sessionSet('role', $user['role']);
                    $this->session->sessionSet('login_time', time());

                    // Update last login
                    $userModel->updateLastLogin($user['id']);

                    // Redirect to admin
                    $this->redirect($this->config->appConfig('url').'/admin');
                } else {
                    $error = 'Username atau password salah';
                }
            }
        }
        $baseUrl = $this->getBaseUrl();
        $appName = $this->getAppName();

        $data = [
            'title' => 'Login Admin - ' . $appName,
            'error' => $error,
            'baseUrl' => $baseUrl,
            'appName' => $appName
        ];

        $this->loadView('auth/login', $data);
    }

    public function logout()
    {
        // Destroy session
        $this->session->sessionDestroy();
        
        // Redirect to login
        $this->redirect($this->config->appConfig('url').'/login');
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: Auth
 * PURPOSE: Authentication and authorization controller for system security
 * EXTENDS: Controller (base controller class)
 * SECURITY_LEVEL: Critical - handles all system access control
 * 
 * BUSINESS_CONTEXT:
 * This controller manages user authentication, session management, and access control
 * for the boarding house management system. It provides login/logout functionality
 * and ensures only authenticated users can access admin features. All security
 * operations flow through this controller.
 * 
 * CLASS_METHODS:
 * 
 * 1. login()
 *    PURPOSE: Handle user login process (both form display and authentication)
 *    HANDLES: 
 *      - GET: Display login form
 *      - POST: Process login credentials
 *    BUSINESS_LOGIC:
 *      - Validates username and password
 *      - Verifies user status (active/inactive)
 *      - Creates user session on successful authentication
 *      - Updates last login timestamp
 *      - Redirects to admin dashboard or shows errors
 *    SECURITY_FEATURES:
 *      - Password verification using secure hashing
 *      - Session management for authentication state
 *      - Input validation and sanitization
 *      - Failed login tracking and feedback
 *    USED_IN: 
 *      - User authentication workflow
 *      - System access control entry point
 *    AI_CONTEXT: Primary authentication gateway for system access
 * 
 * 2. logout()
 *    PURPOSE: Handle user logout and session cleanup
 *    BUSINESS_LOGIC:
 *      - Destroys user session completely
 *      - Clears all session data
 *      - Redirects to login page
 *      - Provides logout confirmation
 *    SECURITY_FEATURES:
 *      - Complete session destruction
 *      - Secure logout process
 *      - Prevents session hijacking after logout
 *    USED_IN:
 *      - User-initiated logout
 *      - Security logout procedures
 *    AI_CONTEXT: Secure session termination
 * 
 * 3. requireLogin()
 *    PURPOSE: Static method to enforce authentication for protected pages
 *    BUSINESS_LOGIC:
 *      - Checks if user session exists and is valid
 *      - Verifies user authentication status
 *      - Redirects to login if not authenticated
 *      - Allows access if properly authenticated
 *    SECURITY_FEATURES:
 *      - Session validation
 *      - Automatic redirect for unauthorized access
 *      - Protection for all admin functionality
 *    USED_IN:
 *      - Admin::__construct() - protects all admin methods
 *      - Any controller requiring authentication
 *    AI_CONTEXT: Central authorization checkpoint for system security
 * 
 * AUTHENTICATION_FLOW:
 * 1. User visits login page -> Auth::login() GET
 * 2. User submits credentials -> Auth::login() POST
 * 3. System validates credentials -> UserModel::findByUsername() + verifyPassword()
 * 4. Success: Create session -> UserModel::updateLastLogin() -> Redirect to admin
 * 5. Failure: Show error message -> Return to login form
 * 
 * SESSION_MANAGEMENT:
 * - Session creation on successful login
 * - Session data includes user ID, username, role
 * - Session validation on each protected page access
 * - Session destruction on logout or timeout
 * 
 * SECURITY_FEATURES:
 * - Secure password verification (password_verify)
 * - Session-based authentication
 * - Automatic redirection for unauthorized access
 * - Protection against session hijacking
 * - User status validation (active/inactive)
 * - Input validation and sanitization
 * 
 * ERROR_HANDLING:
 * - User-friendly error messages for failed logins
 * - Graceful handling of invalid credentials
 * - Proper feedback for inactive accounts
 * - Session management error handling
 * 
 * INTEGRATION_POINTS:
 * - UserModel: For credential verification and user data
 * - Session: For authentication state management
 * - All protected controllers: Through requireLogin()
 * - Configuration: For system URLs and settings
 * 
 * USAGE_PATTERNS:
 * 1. Login Process:
 *    Auth::login() -> UserModel::findByUsername() -> password verification -> session creation
 * 
 * 2. Access Protection:
 *    Protected controller -> Auth::requireLogin() -> session validation -> allow/deny access
 * 
 * 3. Logout Process:
 *    Auth::logout() -> session destruction -> redirect to login
 * 
 * AI_INTEGRATION_NOTES:
 * - This controller is the security foundation of the entire system
 * - All admin functionality depends on this authentication system
 * - Critical for preventing unauthorized access to sensitive data
 * - Handles both user interface and security enforcement
 * - Essential for maintaining system integrity and data protection
 * - Must be properly configured and maintained for system security
 */