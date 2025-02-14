<?php

namespace App\Services;

use App\Interfaces\SubscriptionRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UserSubscriptionRepositoryInterface;
use App\Interfaces\UserSubscriptionServiceInterface;
use App\Jobs\CreateNotification;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserSubscriptionService implements UserSubscriptionServiceInterface
{
    protected UserSubscriptionRepositoryInterface $repository;

    public function __construct(UserSubscriptionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function register(Request $request): UserSubscription
    {
        $introducerId = null;
        if ($referralCode = $request->get('referral_code')) {
            /** @var UserRepositoryInterface $userRepository */
            $userRepository = app(UserRepositoryInterface::class);
            $introducer = $userRepository->where('referral_code', $referralCode)->first();
            $introducerId = $introducer->getKey();
        }
        $userId = $request->user()->getKey();
        $subscriptionId = $request->get('subscription_id');
        $lastestSub = $this->repository->where('user_id', $userId)->where('status', UserSubscription::STATUS_APPROVED)->max('end_date');
        $startDate = Carbon::today();
        if ($lastestSub && Carbon::parse($lastestSub) > Carbon::today()) {
            $startDate = Carbon::parse($lastestSub)->addDay();
        }

        /** @var SubscriptionRepositoryInterface $subscriptionRepository */
        $subscriptionRepository = app(SubscriptionRepositoryInterface::class);
        $subscription = $subscriptionRepository->find($subscriptionId);

        $userSubscription = $this->repository->create([
            'subscription_id' => $subscriptionId,
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->addDays($subscription->duration_in_days),
            'meta' => [
                'price' => $subscription->price
            ],
            'note' => $request->get('note'),
            'price' => $subscription->price,
            'introducer_id' => $introducerId,
        ]);
        if ($request->file('bill')) {
            $userSubscription->addMediaFromRequest('bill')->toMediaCollection(UserSubscription::MEDIA_SUBSCRIPTION_BILL);
        }

        CreateNotification::registerSubscription($userSubscription);

        return $userSubscription;
    }

    public function index(Request $request): LengthAwarePaginator
    {
        $query = $this->repository->with([
            'user:id,name',
            'approver:id,name',
            'rejector:id,name',
            'subscription:id,name',
            'media' => function ($query) {
                $query->whereIn('collection_name', [UserSubscription::MEDIA_SUBSCRIPTION_BILL]);
            }
        ]);

        if ($status = $request->get('status')) {
            $query = $query->whereIn('status', $status);
        }
        if ($introducerId = $request->get('user_id')) {
            $query = $query->where('introducer_id', $introducerId);
        }

        if ($search = $request->get('search')) {
            $query = $query->whereHas('user', function (Builder $q) use ($search) {
                $q->fullTextSearch($search);
            });
        }

        $items = $query->orderBy('created_at', 'DESC')->paginate(10);

        return $items;
    }

    public function approve(int $id, int $approverId): void
    {
        $userSubscription = $this->repository->update([
            'status' => UserSubscription::STATUS_APPROVED,
            'approver_id' => $approverId,
            'approval_date' => Carbon::today(),
        ], $id);

        CreateNotification::approveSubscription($userSubscription);
    }

    public function reject(int $id, int $rejectorId): void
    {
        $userSubscription = $this->repository->update([
            'status' => UserSubscription::STATUS_REJECTED,
            'rejector_id' => $rejectorId,
        ], $id);

        CreateNotification::rejectSubscription($userSubscription);

    }

    public function getMyUserSubscription(int $userId): LengthAwarePaginator
    {
        $subs = $this->repository->with('subscription')->where('user_id', $userId)->orderBy('created_at', 'DESC')->paginate(10);

        return $subs;
    }

    public function checkSubscriptionValid(int $userId): bool
    {
        $now = Carbon::today()->toDateString();
        $valid = $this->repository->where('status', UserSubscription::STATUS_APPROVED)
            ->where('user_id', $userId)
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->exists();

        return $valid;
    }
}
