<?php

require_once __DIR__ . '/../Utils/Logger.php';
require_once __DIR__ . '/../Utils/EmailService.php';

class SubscriptionService {
    private $db;
    private $emailService;
    
    public function __construct($db) {
        $this->db = $db;
        $this->emailService = new EmailService();
    }
    
    /**
     * Get all subscription plans
     */
    public function getSubscriptionPlans() {
        $stmt = $this->db->prepare("
            SELECT * FROM subscription_plans 
            WHERE is_active = 1 
            ORDER BY price ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user's current subscription
     */
    public function getUserSubscription($userId) {
        $stmt = $this->db->prepare("
            SELECT us.*, sp.name as plan_name, sp.price, sp.features, sp.project_limit, sp.commission_rate
            FROM user_subscriptions us
            JOIN subscription_plans sp ON us.plan_id = sp.id
            WHERE us.user_id = :user_id AND us.status IN ('active', 'trialing')
            ORDER BY us.created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if user can create projects based on subscription
     */
    public function canCreateProject($userId) {
        $subscription = $this->getUserSubscription($userId);
        
        if (!$subscription) {
            // Free tier - check project count
            $projectCount = $this->getUserProjectCount($userId);
            return $projectCount < 1; // Free tier allows 1 project
        }
        
        if ($subscription['project_limit'] === -1) {
            return true; // Unlimited
        }
        
        $projectCount = $this->getUserProjectCount($userId);
        return $projectCount < $subscription['project_limit'];
    }
    
    /**
     * Get user's project count
     */
    private function getUserProjectCount($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM projects 
            WHERE user_id = :user_id AND status != 'deleted'
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get commission rate for user
     */
    public function getCommissionRate($userId) {
        $subscription = $this->getUserSubscription($userId);
        
        if (!$subscription) {
            return 0.05; // 5% for free tier
        }
        
        return $subscription['commission_rate'];
    }
    
    /**
     * Upgrade subscription
     */
    public function upgradeSubscription($userId, $newPlanId, $paymentMethodId) {
        try {
            $this->db->beginTransaction();
            
            $currentSubscription = $this->getUserSubscription($userId);
            $newPlan = $this->getSubscriptionPlan($newPlanId);
            
            if (!$newPlan) {
                throw new Exception('Invalid subscription plan');
            }
            
            if ($currentSubscription) {
                // Upgrade existing subscription
                $result = $this->modifyStripeSubscription(
                    $currentSubscription['stripe_subscription_id'],
                    $newPlan['stripe_price_id']
                );
            } else {
                // Create new subscription
                $paymentService = new PaymentService($this->db);
                $result = $paymentService->createSubscription($userId, $newPlanId, $paymentMethodId);
            }
            
            if ($result['success']) {
                // Update user subscription in database
                $this->updateUserSubscription($userId, $newPlanId, $result);
                
                // Send confirmation email
                $user = $this->getUserById($userId);
                $this->emailService->sendSubscriptionConfirmation(
                    $user['email'], 
                    $user['name'], 
                    $newPlan['name']
                );
                
                Logger::info('Subscription upgraded', [
                    'user_id' => $userId,
                    'new_plan' => $newPlan['name']
                ]);
            }
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Subscription upgrade failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Upgrade failed']];
        }
    }
    
    /**
     * Downgrade subscription
     */
    public function downgradeSubscription($userId, $newPlanId) {
        try {
            $this->db->beginTransaction();
            
            $currentSubscription = $this->getUserSubscription($userId);
            $newPlan = $this->getSubscriptionPlan($newPlanId);
            
            if (!$currentSubscription || !$newPlan) {
                throw new Exception('Invalid subscription or plan');
            }
            
            // Check if downgrade is allowed (project limits)
            if (!$this->canDowngrade($userId, $newPlan)) {
                return [
                    'success' => false, 
                    'errors' => ['Cannot downgrade: You have too many active projects for this plan']
                ];
            }
            
            // Schedule downgrade at period end
            $result = $this->scheduleSubscriptionChange(
                $currentSubscription['stripe_subscription_id'],
                $newPlan['stripe_price_id']
            );
            
            if ($result['success']) {
                // Update database to reflect scheduled change
                $this->scheduleSubscriptionDowngrade($userId, $newPlanId);
                
                Logger::info('Subscription downgrade scheduled', [
                    'user_id' => $userId,
                    'new_plan' => $newPlan['name']
                ]);
            }
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Subscription downgrade failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Downgrade failed']];
        }
    }
    
    /**
     * Cancel subscription
     */
    public function cancelSubscription($userId, $immediately = false) {
        try {
            $subscription = $this->getUserSubscription($userId);
            if (!$subscription) {
                return ['success' => false, 'errors' => ['No active subscription found']];
            }
            
            if ($immediately) {
                // Cancel immediately
                \Stripe\Subscription::update($subscription['stripe_subscription_id'], [
                    'cancel_at_period_end' => false
                ]);
                \Stripe\Subscription::retrieve($subscription['stripe_subscription_id'])->cancel();
                
                $this->updateSubscriptionStatus($userId, 'cancelled');
            } else {
                // Cancel at period end
                \Stripe\Subscription::update($subscription['stripe_subscription_id'], [
                    'cancel_at_period_end' => true
                ]);
                
                $this->updateSubscriptionStatus($userId, 'cancel_scheduled');
            }
            
            // Send cancellation email
            $user = $this->getUserById($userId);
            $this->emailService->sendSubscriptionCancellation(
                $user['email'], 
                $user['name'],
                $immediately
            );
            
            Logger::info('Subscription cancelled', [
                'user_id' => $userId,
                'immediately' => $immediately
            ]);
            
            return ['success' => true, 'message' => 'Subscription cancelled successfully'];
            
        } catch (Exception $e) {
            Logger::error('Subscription cancellation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Cancellation failed']];
        }
    }
    
    /**
     * Reactivate cancelled subscription
     */
    public function reactivateSubscription($userId) {
        try {
            $subscription = $this->getUserSubscription($userId);
            if (!$subscription || $subscription['status'] !== 'cancel_scheduled') {
                return ['success' => false, 'errors' => ['No cancelled subscription found']];
            }
            
            // Remove cancellation
            \Stripe\Subscription::update($subscription['stripe_subscription_id'], [
                'cancel_at_period_end' => false
            ]);
            
            $this->updateSubscriptionStatus($userId, 'active');
            
            Logger::info('Subscription reactivated', ['user_id' => $userId]);
            
            return ['success' => true, 'message' => 'Subscription reactivated successfully'];
            
        } catch (Exception $e) {
            Logger::error('Subscription reactivation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Reactivation failed']];
        }
    }
    
    /**
     * Get subscription usage statistics
     */
    public function getUsageStats($userId) {
        $subscription = $this->getUserSubscription($userId);
        $projectCount = $this->getUserProjectCount($userId);
        
        // Get donation stats
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_donations,
                COALESCE(SUM(amount), 0) as total_raised,
                COUNT(DISTINCT project_id) as projects_with_donations
            FROM donations d
            JOIN projects p ON d.project_id = p.id
            WHERE p.user_id = :user_id AND d.payment_status = 'completed'
        ");
        $stmt->execute(['user_id' => $userId]);
        $donationStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'subscription' => $subscription,
            'project_count' => $projectCount,
            'project_limit' => $subscription ? $subscription['project_limit'] : 1,
            'total_donations' => $donationStats['total_donations'],
            'total_raised' => $donationStats['total_raised'],
            'projects_with_donations' => $donationStats['projects_with_donations'],
            'commission_rate' => $this->getCommissionRate($userId)
        ];
    }
    
    /**
     * Process subscription renewal
     */
    public function processRenewal($subscriptionId) {
        try {
            $this->db->beginTransaction();
            
            // Get subscription details
            $stmt = $this->db->prepare("
                SELECT us.*, u.email, u.name, sp.name as plan_name
                FROM user_subscriptions us
                JOIN users u ON us.user_id = u.id
                JOIN subscription_plans sp ON us.plan_id = sp.id
                WHERE us.stripe_subscription_id = :subscription_id
            ");
            $stmt->execute(['subscription_id' => $subscriptionId]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subscription) {
                throw new Exception('Subscription not found');
            }
            
            // Update renewal date
            $stmt = $this->db->prepare("
                UPDATE user_subscriptions 
                SET current_period_start = NOW(),
                    current_period_end = DATE_ADD(NOW(), INTERVAL 1 MONTH),
                    updated_at = NOW()
                WHERE stripe_subscription_id = :subscription_id
            ");
            $stmt->execute(['subscription_id' => $subscriptionId]);
            
            // Send renewal confirmation
            $this->emailService->sendSubscriptionRenewal(
                $subscription['email'],
                $subscription['name'],
                $subscription['plan_name']
            );
            
            Logger::info('Subscription renewed', [
                'user_id' => $subscription['user_id'],
                'subscription_id' => $subscriptionId
            ]);
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Subscription renewal failed', ['error' => $e->getMessage()]);
            return ['success' => false];
        }
    }
    
    /**
     * Handle failed payment
     */
    public function handleFailedPayment($subscriptionId, $invoiceId) {
        try {
            // Get subscription details
            $stmt = $this->db->prepare("
                SELECT us.*, u.email, u.name
                FROM user_subscriptions us
                JOIN users u ON us.user_id = u.id
                WHERE us.stripe_subscription_id = :subscription_id
            ");
            $stmt->execute(['subscription_id' => $subscriptionId]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subscription) {
                return ['success' => false];
            }
            
            // Update subscription status
            $this->updateSubscriptionStatus($subscription['user_id'], 'past_due');
            
            // Send payment failure notification
            $this->emailService->sendPaymentFailureNotification(
                $subscription['email'],
                $subscription['name'],
                $invoiceId
            );
            
            Logger::warning('Subscription payment failed', [
                'user_id' => $subscription['user_id'],
                'subscription_id' => $subscriptionId,
                'invoice_id' => $invoiceId
            ]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            Logger::error('Failed payment handling error', ['error' => $e->getMessage()]);
            return ['success' => false];
        }
    }
    
    // Helper methods
    private function getSubscriptionPlan($planId) {
        $stmt = $this->db->prepare('SELECT * FROM subscription_plans WHERE id = :id');
        $stmt->execute(['id' => $planId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getUserById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function canDowngrade($userId, $newPlan) {
        if ($newPlan['project_limit'] === -1) {
            return true; // Unlimited projects
        }
        
        $projectCount = $this->getUserProjectCount($userId);
        return $projectCount <= $newPlan['project_limit'];
    }
    
    private function updateSubscriptionStatus($userId, $status) {
        $stmt = $this->db->prepare("
            UPDATE user_subscriptions 
            SET status = :status, updated_at = NOW() 
            WHERE user_id = :user_id AND status IN ('active', 'trialing', 'past_due', 'cancel_scheduled')
        ");
        $stmt->execute([
            'status' => $status,
            'user_id' => $userId
        ]);
    }
    
    private function updateUserSubscription($userId, $planId, $subscriptionData) {
        $stmt = $this->db->prepare("
            UPDATE user_subscriptions 
            SET plan_id = :plan_id, 
                status = 'active',
                updated_at = NOW()
            WHERE user_id = :user_id AND status IN ('active', 'trialing')
        ");
        $stmt->execute([
            'plan_id' => $planId,
            'user_id' => $userId
        ]);
    }
    
    private function scheduleSubscriptionDowngrade($userId, $newPlanId) {
        $stmt = $this->db->prepare("
            UPDATE user_subscriptions 
            SET scheduled_plan_id = :new_plan_id,
                status = 'downgrade_scheduled',
                updated_at = NOW()
            WHERE user_id = :user_id AND status = 'active'
        ");
        $stmt->execute([
            'new_plan_id' => $newPlanId,
            'user_id' => $userId
        ]);
    }
    
    private function modifyStripeSubscription($subscriptionId, $newPriceId) {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            
            \Stripe\Subscription::update($subscriptionId, [
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $newPriceId,
                    ],
                ],
                'proration_behavior' => 'always_invoice',
            ]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            Logger::error('Stripe subscription modification failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Subscription modification failed']];
        }
    }
    
    private function scheduleSubscriptionChange($subscriptionId, $newPriceId) {
        try {
            \Stripe\Subscription::update($subscriptionId, [
                'items' => [
                    [
                        'price' => $newPriceId,
                    ],
                ],
                'proration_behavior' => 'none',
                'billing_cycle_anchor' => 'unchanged',
            ]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            Logger::error('Stripe subscription scheduling failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Subscription scheduling failed']];
        }
    }
}
