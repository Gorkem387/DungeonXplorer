<?php

class AuthController
{
    public function showLogin()
    {
        require 'views/auth/login.php';
    }
    
    public function showRegister()
    {
        require 'views/auth/register.php';
    }
}