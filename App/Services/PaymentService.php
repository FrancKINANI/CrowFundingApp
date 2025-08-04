<?php

require_once __DIR__ . '/../Utils/Logger.php';

class PaymentService {
    private $db;
    private $stripeSecretKey;
    private $stripePublishableKey;
    
    public function __construct($db) {
        $this->db = $db;
        $this->stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        $this->stripePublishableKey = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';
        
        // Initialize Stripe
        if ($this->stripeSecretKey) {
            \Stripe\Stripe::setApiKey($this->stripeSecretKey);
        }
    }
    
    /**
     * Process donation payment
     */
    public function processDonation($donationData) {
        try {
            $this->db->beginTransaction();
            
            // Validate donation data
            $validation = $this->validateDonationData($donationData);
            if (!$validation['valid']) {
                return ['success' => false, 'errors' => $validation['errors']];
            }
            
            // Create payment intent with Stripe
            $paymentIntent = $this->createPaymentIntent($donationData);
            if (!$paymentIntent['success']) {
                return $paymentIntent;
            }
            
            // Create donation record
            $donationId = $this->createDonationRecord($donationData, $paymentIntent['payment_intent_id']);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'donation_id' => $donationId,
                'client_secret' => $paymentIntent['client_secret'],
                'payment_intent_id' => $paymentIntent['payment_intent_id']
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Donation processing failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Payment processing failed']];
        }
    }
    
    /**
     * Create Stripe Payment Intent
     */
    private function createPaymentIntent($donationData) {
        try {
            $amount = (int)($donationData['amount'] * 100); // Convert to cents
            
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => $donationData['currency'] ?? 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'project_id' => $donationData['project_id'],
                    'user_id' => $donationData['user_id'],
                    'type' => 'donation'
                ],
                'description' => 'Donation to: ' . $donationData['project_title']
            ]);
            
            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret
            ];
            
        } catch (\Stripe\Exception\CardException $e) {
            return ['success' => false, 'errors' => [$e->getError()->message]];
        } catch (\Stripe\Exception\RateLimitException $e) {
            return ['success' => false, 'errors' => ['Too many requests. Please try again later.']];
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return ['success' => false, 'errors' => ['Invalid payment request.']];
        } catch (\Stripe\Exception\AuthenticationException $e) {
            Logger::error('Stripe authentication failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Payment service unavailable.']];
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            return ['success' => false, 'errors' => ['Network error. Please try again.']];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Logger::error('Stripe API error', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Payment service error.']];
        }
    }
    
    /**
     * Handle Stripe webhook
     */
    public function handleWebhook($payload, $signature) {
        try {
            $endpoint_secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
            
            $event = \Stripe\Webhook::constructEvent(
                $payload, $signature, $endpoint_secret
            );
            
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSuccess($event->data->object);
                    break;
                    
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailure($event->data->object);
                    break;
                    
                case 'invoice.payment_succeeded':
                    $this->handleSubscriptionPayment($event->data->object);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionCancellation($event->data->object);
                    break;
                    
                default:
                    Logger::info('Unhandled webhook event', ['type' => $event->type]);
            }
            
            return ['success' => true];
            
        } catch (\UnexpectedValueException $e) {
            Logger::error('Invalid webhook payload', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Invalid payload'];
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Logger::error('Invalid webhook signature', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Invalid signature'];
        }
    }
    
    /**
     * Handle successful payment
     */
    private function handlePaymentSuccess($paymentIntent) {
        try {
            $this->db->beginTransaction();
            
            // Update donation status
            $stmt = $this->db->prepare("
                UPDATE donations 
                SET payment_status = 'completed', 
                    stripe_payment_intent_id = :payment_intent_id,
                    paid_at = NOW()
                WHERE stripe_payment_intent_id = :payment_intent_id
            ");
            $stmt->execute(['payment_intent_id' => $paymentIntent->id]);
            
            // Get donation details
            $donation = $this->getDonationByPaymentIntent($paymentIntent->id);
            if ($donation) {
                // Update project current amount
                $this->updateProjectAmount($donation['project_id'], $donation['amount']);
                
                // Send confirmation email
                $this->sendDonationConfirmation($donation);
                
                // Log successful payment
                Logger::info('Payment completed', [
                    'donation_id' => $donation['id'],
                    'amount' => $donation['amount'],
                    'project_id' => $donation['project_id']
                ]);
            }
            
            $this->db->commit();
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to handle payment success', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle failed payment
     */
    private function handlePaymentFailure($paymentIntent) {
        try {
            $stmt = $this->db->prepare("
                UPDATE donations 
                SET payment_status = 'failed',
                    failure_reason = :reason
                WHERE stripe_payment_intent_id = :payment_intent_id
            ");
            $stmt->execute([
                'payment_intent_id' => $paymentIntent->id,
                'reason' => $paymentIntent->last_payment_error->message ?? 'Unknown error'
            ]);
            
            Logger::info('Payment failed', [
                'payment_intent_id' => $paymentIntent->id,
                'reason' => $paymentIntent->last_payment_error->message ?? 'Unknown'
            ]);
            
        } catch (Exception $e) {
            Logger::error('Failed to handle payment failure', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Create subscription for user
     */
    public function createSubscription($userId, $planId, $paymentMethodId) {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return ['success' => false, 'errors' => ['User not found']];
            }
            
            // Create or retrieve Stripe customer
            $customer = $this->getOrCreateStripeCustomer($user);
            
            // Attach payment method to customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach(['customer' => $customer->id]);
            
            // Set as default payment method
            \Stripe\Customer::update($customer->id, [
                'invoice_settings' => ['default_payment_method' => $paymentMethodId]
            ]);
            
            // Get plan details
            $plan = $this->getSubscriptionPlan($planId);
            if (!$plan) {
                return ['success' => false, 'errors' => ['Invalid plan']];
            }
            
            // Create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [['price' => $plan['stripe_price_id']]],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'user_id' => $userId,
                    'plan_id' => $planId
                ]
            ]);
            
            // Save subscription to database
            $this->saveSubscription($userId, $subscription, $planId);
            
            return [
                'success' => true,
                'subscription_id' => $subscription->id,
                'client_secret' => $subscription->latest_invoice->payment_intent->client_secret
            ];
            
        } catch (Exception $e) {
            Logger::error('Subscription creation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Subscription creation failed']];
        }
    }
    
    /**
     * Cancel subscription
     */
    public function cancelSubscription($userId) {
        try {
            $subscription = $this->getUserSubscription($userId);
            if (!$subscription) {
                return ['success' => false, 'errors' => ['No active subscription found']];
            }
            
            // Cancel at period end
            \Stripe\Subscription::update($subscription['stripe_subscription_id'], [
                'cancel_at_period_end' => true
            ]);
            
            // Update database
            $stmt = $this->db->prepare("
                UPDATE user_subscriptions 
                SET status = 'cancelled', cancelled_at = NOW() 
                WHERE user_id = :user_id AND status = 'active'
            ");
            $stmt->execute(['user_id' => $userId]);
            
            Logger::info('Subscription cancelled', ['user_id' => $userId]);
            
            return ['success' => true, 'message' => 'Subscription will be cancelled at the end of the billing period'];
            
        } catch (Exception $e) {
            Logger::error('Subscription cancellation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Cancellation failed']];
        }
    }
    
    /**
     * Process refund
     */
    public function processRefund($donationId, $amount = null, $reason = 'requested_by_customer') {
        try {
            $donation = $this->getDonationById($donationId);
            if (!$donation || $donation['payment_status'] !== 'completed') {
                return ['success' => false, 'errors' => ['Invalid donation for refund']];
            }
            
            $refundAmount = $amount ? (int)($amount * 100) : (int)($donation['amount'] * 100);
            
            $refund = \Stripe\Refund::create([
                'payment_intent' => $donation['stripe_payment_intent_id'],
                'amount' => $refundAmount,
                'reason' => $reason,
                'metadata' => [
                    'donation_id' => $donationId,
                    'project_id' => $donation['project_id']
                ]
            ]);
            
            // Update donation status
            $stmt = $this->db->prepare("
                UPDATE donations 
                SET payment_status = 'refunded', 
                    refund_amount = :refund_amount,
                    refunded_at = NOW()
                WHERE id = :donation_id
            ");
            $stmt->execute([
                'refund_amount' => $refundAmount / 100,
                'donation_id' => $donationId
            ]);
            
            // Update project amount
            $this->updateProjectAmount($donation['project_id'], -($refundAmount / 100));
            
            Logger::info('Refund processed', [
                'donation_id' => $donationId,
                'refund_amount' => $refundAmount / 100
            ]);
            
            return ['success' => true, 'refund_id' => $refund->id];
            
        } catch (Exception $e) {
            Logger::error('Refund processing failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Refund processing failed']];
        }
    }
    
    // Helper methods
    private function validateDonationData($data) {
        $errors = [];
        
        if (empty($data['amount']) || $data['amount'] < 1) {
            $errors[] = 'Minimum donation amount is $1';
        }
        
        if (empty($data['project_id'])) {
            $errors[] = 'Project ID is required';
        }
        
        if (empty($data['user_id'])) {
            $errors[] = 'User ID is required';
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    
    private function createDonationRecord($data, $paymentIntentId) {
        $stmt = $this->db->prepare("
            INSERT INTO donations (
                amount, project_id, user_id, stripe_payment_intent_id, 
                payment_status, currency, anonymous, message, created_at
            ) VALUES (
                :amount, :project_id, :user_id, :payment_intent_id,
                'pending', :currency, :anonymous, :message, NOW()
            )
        ");
        
        $stmt->execute([
            'amount' => $data['amount'],
            'project_id' => $data['project_id'],
            'user_id' => $data['user_id'],
            'payment_intent_id' => $paymentIntentId,
            'currency' => $data['currency'] ?? 'usd',
            'anonymous' => $data['anonymous'] ?? false,
            'message' => $data['message'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    private function getDonationByPaymentIntent($paymentIntentId) {
        $stmt = $this->db->prepare("
            SELECT d.*, p.title as project_title, u.name as donor_name, u.email as donor_email
            FROM donations d
            JOIN projects p ON d.project_id = p.id
            JOIN users u ON d.user_id = u.id
            WHERE d.stripe_payment_intent_id = :payment_intent_id
        ");
        $stmt->execute(['payment_intent_id' => $paymentIntentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function updateProjectAmount($projectId, $amount) {
        $stmt = $this->db->prepare("
            UPDATE projects 
            SET current_amount = current_amount + :amount 
            WHERE id = :project_id
        ");
        $stmt->execute([
            'amount' => $amount,
            'project_id' => $projectId
        ]);
    }
    
    private function getOrCreateStripeCustomer($user) {
        // Check if customer already exists
        if ($user['stripe_customer_id']) {
            return \Stripe\Customer::retrieve($user['stripe_customer_id']);
        }
        
        // Create new customer
        $customer = \Stripe\Customer::create([
            'email' => $user['email'],
            'name' => $user['name'],
            'metadata' => ['user_id' => $user['id']]
        ]);
        
        // Save customer ID
        $stmt = $this->db->prepare("UPDATE users SET stripe_customer_id = :customer_id WHERE id = :user_id");
        $stmt->execute([
            'customer_id' => $customer->id,
            'user_id' => $user['id']
        ]);
        
        return $customer;
    }
    
    private function getUserById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getSubscriptionPlan($planId) {
        $stmt = $this->db->prepare('SELECT * FROM subscription_plans WHERE id = :id AND is_active = 1');
        $stmt->execute(['id' => $planId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function saveSubscription($userId, $subscription, $planId) {
        $stmt = $this->db->prepare("
            INSERT INTO user_subscriptions (
                user_id, plan_id, stripe_subscription_id, status, 
                current_period_start, current_period_end, created_at
            ) VALUES (
                :user_id, :plan_id, :stripe_subscription_id, :status,
                :current_period_start, :current_period_end, NOW()
            )
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'plan_id' => $planId,
            'stripe_subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
            'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end)
        ]);
    }
}
