<?php
namespace App\Services;

use App\Interfaces\DashboardServiceInterface;
use App\Interfaces\OrderItemRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\TabRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UserSubscriptionRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use App\Models\UserSubscription;

class DashboardService implements DashboardServiceInterface
{
    protected UserRepositoryInterface $userRepository;
    protected OrderRepositoryInterface $orderRepository;
    protected TabRepositoryInterface $tabRepository;
    protected OrderItemRepositoryInterface $orderItemRepository;
    protected UserSubscriptionRepositoryInterface $userSubscriptionRepository;

    public function __construct(UserRepositoryInterface $userRepository, OrderRepositoryInterface $orderRepository, TabRepositoryInterface $tabRepository, OrderItemRepositoryInterface $orderItemRepository, UserSubscriptionRepositoryInterface $userSubscriptionRepository)
    {
        $this->userRepository = $userRepository;
        $this->orderRepository = $orderRepository;
        $this->tabRepository = $tabRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->userSubscriptionRepository = $userSubscriptionRepository;
    }

    public function getCountUser(): int
    {
        return $this->userRepository->count();
    }

    public function getCountOrder(): int
    {
        return $this->orderRepository->count();
    }

    public function getCountTab(): int
    {
        return $this->tabRepository->count();
    }

    public function getTabRevenue(): int
    {
        $sum = $this->orderItemRepository->whereHas('order', function ($query) {
            $query->where('status', Order::STATUS_COMPLETED);
        })->sum('price');

        return $sum;
        // $q->whereHas('order', function ($subQuery) use ($startDate, $endDate) {
        //     $subQuery->where('status', Order::STATUS_COMPLETED)
        //         ->when($startDate, function ($query, string $startDate) {
        //             $query->where('approval_date', '>=', $startDate);
        //         })
        //         ->when($endDate, function ($query, string $endDate) {
        //             $query->where('approval_date', '<=', $endDate);
        //         });
        // });
    }

    public function getSubscriptionRevenue(): int
    {
        $sum = $this->userSubscriptionRepository->where('status', UserSubscription::STATUS_APPROVED)->sum('price');

        return $sum;
    }
}
