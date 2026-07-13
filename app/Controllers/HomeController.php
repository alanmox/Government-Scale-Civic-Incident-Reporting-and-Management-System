<?php
declare(strict_types=1);
namespace App\Controllers;

final class HomeController extends BaseController
{
    public function index(): void
    {
        if ($this->session->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        $this->redirect('/login');
    }
}
