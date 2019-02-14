<?php

namespace AppBundle;

use AppBundle\Entity\User;
use AppBundle\Subscription\SubscriptionPlan;
use Doctrine\ORM\EntityManager;

class StripeClient
{
    private $em;

    public function __construct($secretKey, EntityManager $em)
    {
        $this->em = $em;
        \Stripe\Stripe::setApiKey($secretKey);
    }

    public function createCustomer(User $user, $paymentToken)
    {
        $customer = \Stripe\Customer::create([
            'email' => $user->getEmail(),
            'source' => $paymentToken
        ]);
        $user->setStripeCustomerId($customer->id);

        $this->em->persist($user);
        $this->em->flush();

        return $customer;
    }

    public function updateCustomerCard(User $user, $paymentToken)
    {
        $customer = \Stripe\Customer::retrieve($user->getStripeCustomerId());

        $customer->source = $paymentToken;
        $customer->save();

        return $customer;
    }

    public function createInvoiceItem($amount, User $user, $description)
    {
        return \Stripe\InvoiceItem::create(array(
            "amount" => $amount,
            "currency" => "usd",
            "customer" => $user->getStripeCustomerId(),
            "description" => $description
        ));
    }

    public function createInvoice(User $user, $payImmediately = true)
    {
        $invoice = \Stripe\Invoice::create(array(
            "customer" => $user->getStripeCustomerId()
        ));
        if ($payImmediately) {
            // guarantee it charges *right* now
            $invoice->pay();
        }
        return $invoice;
    }

    public function createSubscription(User $user, SubscriptionPlan $plan)
    {
        $subscription = \Stripe\Subscription::create([
            'customer' => $user->getStripeCustomerId(),
            'plan' => $plan->getPlanId()
        ]);

        return $subscription;
    }

    public function cancelSubscription(User $user)
    {
        $sub = \Stripe\Subscription::retrieve(
            $user->getSubscription()->getStripeSubscriptionId()
        );

        $sub->cancel_at_period_end = true;
        $sub->save();
    }

    public function reactivateSubscription(User $user)
    {
        if (!$user->hasActiveSubscription()) {
            throw new \LogicException('Subscriptions can only be reactivated if the subscription has not actually ended yet');
        }

        $subscription = \Stripe\Subscription::retrieve(
            $user->getSubscription()->getStripeSubscriptionId()
        );
        // this triggers the refresh of the subscription!
        $subscription->plan = $user->getSubscription()->getStripePlanId();
        $subscription->save();
        \Stripe\Subscription::update($user->getSubscription()->getStripeSubscriptionId(), [
            'cancel_at_period_end' => false,
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'plan' => $subscription->plan,
                ],
            ],
        ]);
        return $subscription;
    }
}