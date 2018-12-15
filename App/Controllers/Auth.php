<?php

namespace App\Controllers;

use App\Models\User;
use App\Rules\UniqueRule;
use \Core\View;
use Rakit\Validation\Validator;


/**
 * Home controller
 *
 * PHP version 7.0
 */
class Auth extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function loginAction()
    {
        View::renderTemplate('Auth/login.html');
    }

    /**
     * Login user
     * @return void
     */
    public function loginPost()
    {
        $validator = new Validator;

        // make it
        $validation = $validator->make($_POST, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // then validate
        $validation->validate();

        if ($validation->fails()) {
            // handling errors
            $errors = $validation->errors();

            $_SESSION['old_values'] = $_POST;
            $_SESSION['errors'] = $errors->firstOfAll();

            header('Location: /sign-in');
        } else {
            $userByEmail = User::query()->where('email', '=', $_POST['email'])->first();

            if(!empty($userByEmail)) {
                $hash = $userByEmail->password;

                if(password_verify($_POST['password'], $hash)) {
                    $_SESSION['user'] = [
                        'name' => $userByEmail->name,
                        'id'   => $userByEmail->id
                    ];

                    header('Location: /home');
                }
            }

            $_SESSION['old_values'] = $_POST;
            $_SESSION['errors'] = ['email' => 'Email or password incorrect.'];

            header('Location: /sign-in');
        }
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function registerAction()
    {
        View::renderTemplate('Auth/register.html');
    }

    /**
     * Registration user
     *
     * @return void
     */
    public function registerPost()
    {
        $validator = new Validator;
        $validator->addValidator('unique', new UniqueRule());

        // make it
        $validation = $validator->make($_POST, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        // then validate
        $validation->validate();

        if ($validation->fails()) {
            // handling errors
            $errors = $validation->errors();

            $_SESSION['old_values'] = $_POST;
            $_SESSION['errors'] = $errors->firstOfAll();

            header('Location: /sign-up');
        } else {
            $user = User::query()->create([
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT)
            ]);

            $_SESSION['user'] = [
                'name' => $user->name,
                'id'   => $user->id
            ];

            header('Location: /home');
        }
    }


    /**
     * Log out user
     *
     * @return void
     */
    public function logout()
    {
        unset($_SESSION['user']);
        header('Location: /sign-in');
    }
}
