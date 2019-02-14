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

        $subscription = $this->getUser()->getSubscription();
        $subscription->deactivateSubscription();
        $em = $this->getDoctrine()->getManager();
        $em->persist($subscription);
        $em->flush();

        $this->addFlash('success', 'Subscription Canceled :(');

        return $this->redirectToRoute('account');
    }

    /**
     * @Route("/account/subscription/reactivate", name="account_subscription_reactivate")
     */
    public function reactivateSubscriptionAction()
    {
        $stripeClient = $this->get('stripe_client');
        $stripeSubscription = $stripeClient->reactivateSubscription($this->getUser());

        $this->get('subscription_helper')->addSubscriptionToUser($stripeSubscription, $this->getUser());

        $this->addFlash('success', 'Welcome back!');

        return $this->redirectToRoute('account');
    }
}
