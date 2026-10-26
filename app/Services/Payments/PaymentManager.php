<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Manager;

class PaymentManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('payment.default', 'manual');
    }

    public function driver($driver = null): PaymentGatewayInterface
    {
        $driver = $driver ?: $this->getDefaultDriver();
        return parent::driver($driver);
    }

    public function createManualDriver(): PaymentGatewayInterface
    {
        return new ManualPaymentGateway(config('payment.gateways.manual', []));
    }

    public function createBkashDriver(): PaymentGatewayInterface
    {
        return new BkashPaymentGateway(config('payment.gateways.bkash', []));
    }

    public function createStripeDriver(): PaymentGatewayInterface
    {
        return new StripePaymentGateway(config('payment.gateways.stripe', []));
    }

    public function createBinanceDriver(): PaymentGatewayInterface
    {
        return new BinancePaymentGateway(config('payment.gateways.binance', []));
    }
}
