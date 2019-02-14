<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends BaseController
{
    /**
     * @Route("/account", name="account")
     */
    public function accountAction()
    {
        return $this->render('profile/account.html.twig', []);
    }
}
