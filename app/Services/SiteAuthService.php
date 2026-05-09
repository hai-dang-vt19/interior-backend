<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\CustomerRegistrationVerifyMail;
use App\Models\Customer;
use App\Repositories\SiteAuth\SiteAuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\CarbonInterface;

class SiteAuthService extends BaseService
{
    public function __construct(
        private SiteAuthRepositoryInterface $siteAuthRepository
    ) {}

    public function attemptLogin(string $phone, string $password): ?Customer
    {
        $customer = $this->siteAuthRepository->findCustomerByPhone($phone);
        if (!$customer || !Hash::check($password, $customer->password)) {
            return null;
        }

        return $customer;
    }

    /**
     * Đăng ký khách mới, lưu hash token xác thực và gửi email có nút kích hoạt
     */
    public function registerCustomer(array $payload): Customer
    {
        $plainToken = Str::random(64);

        $customer = $this->siteAuthRepository->createCustomer([
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => Hash::make($payload['password']),
            'loyalty_tier' => 'standard',
            'reward_points' => 0,
            'email_verified_at' => null,
            'email_verification_token_hash' => hash('sha256', $plainToken),
            'email_verification_token_expires_at' => now()->addHours(48),
        ]);

        $verifyUrl = route('site.register.verify', ['token' => $plainToken]);
        Mail::to($customer->email)->send(new CustomerRegistrationVerifyMail($customer, $verifyUrl));

        return $customer;
    }

    /**
     * Xác thực email từ token trên URL (token thô so khớp với hash trong CSDL)
     *
     * @return array{status: string}
     */
    public function verifyRegistrationEmail(string $plainToken): array
    {
        $plainToken = trim($plainToken);
        if (strlen($plainToken) < 32) {
            return ['status' => 'invalid'];
        }

        $hash = hash('sha256', $plainToken);
        $customer = $this->siteAuthRepository->findCustomerByEmailVerificationTokenHash($hash);
        if (!$customer) {
            return ['status' => 'invalid'];
        }

        if ($customer->hasVerifiedEmail()) {
            return ['status' => 'already_verified'];
        }

        $expiresAt = $customer->email_verification_token_expires_at;
        if ($expiresAt instanceof CarbonInterface && $expiresAt->isPast()) {
            return ['status' => 'expired'];
        }

        $this->siteAuthRepository->markCustomerEmailVerified($customer);

        return ['status' => 'success'];
    }
}
