<?php

namespace App\Controllers;

use App\Models\User;
use App\Rules\UniqueRule;
use \Core\View;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
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
                        'id'   => $userByEmail->id,
                        'firebase_id' => $userByEmail->firebase_id
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
            $creatingData = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT)
            ];

            $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'\..\\..\\chat-dd165-firebase-adminsdk-9yxd3-303d701c23.json');

            $firebase = (new Factory())
                ->withServiceAccount($serviceAccount)
                ->create();

            $uid = uniqid();
            $additionalClaims = [
                'name' => $creatingData['name']
            ];

            $firebase->getAuth()->createCustomToken($uid, $additionalClaims);

            $creatingData['firebase_id'] = $uid;

            $user = User::query()->create($creatingData);

            $_SESSION['user'] = [
                'name' => $user->name,
                'id'   => $user->id,
                'firebase_id' => $user->firebase_id
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
