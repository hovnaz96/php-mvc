<?php

namespace App\Controllers;

use App\Models\User;
use \Core\View;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Chat extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        $users = User::query()
            ->select('firebase_id', 'name')
            ->where('id', '<>', $_SESSION['user']['id'])
            ->get();

        View::renderTemplate('Chat/index.html', ['users' => $users]);
    }
}
