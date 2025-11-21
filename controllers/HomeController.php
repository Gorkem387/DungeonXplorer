<?php

class HomeController
{
    public function index()
    {
        require 'views/home.php';
    }

    public function testDb()
    {
        echo "Connexion OK !";
    }
}
