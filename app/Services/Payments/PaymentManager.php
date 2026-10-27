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

    public function createBkashPersonalDriver(): PaymentGatewayInterface
    {
        return new BkashPersonalPaymentGateway(config('payment.gateways.bkash_personal', []));
    }

    public function createBkashDriver(): PaymentGatewayInterface
    {
        return new BkashPaymentGateway(config('payment.gateways.bkash', []));
    }

    public function createSslcommerzDriver(): PaymentGatewayInterface
    {
        return new SslcommerzPaymentGateway(config('payment.gateways.sslcommerz', []));
    }

    public function createPiprapayDriver(): PaymentGatewayInterface
    {
        return new PiprapayPaymentGateway(config('payment.gateways.piprapay', []));
    }

    public function createUddoktapayDriver(): PaymentGatewayInterface
    {
        return new UddoktapayPaymentGateway(config('payment.gateways.uddoktapay', []));
    }

    public function createBinanceDriver(): PaymentGatewayInterface
    {
        return new BinanceMerchantPaymentGateway(config('payment.gateways.binance', []));
    }

    public function createBinancePersonalDriver(): PaymentGatewayInterface
    {
        return new BinancePersonalPaymentGateway(config('payment.gateways.binance_personal', []));
    }

    public function createStripeDriver(): PaymentGatewayInterface
    {
        return new StripePaymentGateway(config('payment.gateways.stripe', []));
    }
}
