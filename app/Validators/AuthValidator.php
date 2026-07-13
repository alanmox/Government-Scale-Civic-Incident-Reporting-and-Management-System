<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

final class AuthValidator
{
    public function __construct(private UserRepository $userRepo) {}

    public function validateRegistration(array $data): array
    {
        $errors = [];

        if (empty($data['full_name']) || strlen($data['full_name']) < 3) {
            $errors['full_name'][] = __('validation.min_length', ['field' => 'Full Name', 'min' => 3]);
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = __('validation.email');
        } elseif ($this->userRepo->findByEmail($data['email'])) {
            $errors['email'][] = __('validation.unique', ['field' => 'Email']);
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'][] = __('validation.password');
        }

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $errors['password_confirmation'][] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return [
            'full_name' => trim($data['full_name']),
            'email'     => strtolower(trim($data['email'])),
            'phone'     => trim($data['phone'] ?? ''),
            'password'  => $data['password'],
        ];
    }
}
