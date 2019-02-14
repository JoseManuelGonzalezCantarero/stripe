<?php

namespace AppBundle\Subscription;

use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class SubscriptionHelper
{
    /** @var SubscriptionPlan[] */
    private $plans = [];
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        $this->plans[] = new SubscriptionPlan(
            'farmer_brent_monthly',
            'Farmer Brent',
            99
        );

        $this->plans[] = new SubscriptionPlan(
            'new_zealander_monthly',
            'New Zealander',
            199
        );
    }
    /**
     * @param $planId
     * @return SubscriptionPlan|null
     */
    public function findPlan($planId)
    {
        foreach ($this->plans as $plan) {
            if ($plan->getPlanId() == $planId) {
                return $plan;
            }
        }
    }

    public function addSubscriptionToUser(\Stripe\Subscription $stripeSubscription, User $user)
    {
        $subscription = $user->getSubscription();
        if (!$subscription) {
            $subscription = new Subscription();
            $subscription->setUser($user);
        }

        $subscription->activateSubscription(
            $stripeSubscription->plan->id,
            $stripeSubscription->id
        );

        $this->em->persist($subscription);
        $this->em->flush($subscription);
    }
}