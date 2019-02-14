<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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

    /**
     * @Route("/account/subscription/cancel", name="account_subscription_cancel")
     * @Method("POST")
     */
    public function cancelSubscriptionAction()
    {
        $stripeClient = $this->get('stripe_client');
        $stripeClient->cancelSubscription($this->getUser());

        $this->addFlash('success', 'Subscription Canceled :(');

        return $this->redirectToRoute('account');
    }
}
