# Payment services

[[toc]]

## Usage

A payment service is a class that processes the payment defined in the subscription column `payment_method`.

## Create a payment service

### Create the service

Create a new laravel class in your project and implement `Bpuig\Subby\Contracts\PaymentMethodService`. Looking at
the `Free`
service would be a good starting point.

```php
<?php

declare(strict_types=1);

namespace Bpuig\Subby\Services\PaymentMethods;

use Bpuig\Subby\Contracts\PaymentMethodService;

class Free implements PaymentMethodService
{
    /**
     * Charge desired amount
     * @return void
     */
    public function charge()
    {
        // Nothing is charged, no exception is raised
    }
}
```

#### Create your methods

In the following code, see an example of what could be a payment method service for a fictional credit card payment
processor.

```php
<?php

declare(strict_types=1);

namespace PaymentMethods;

use Bpuig\Subby\Contracts\PaymentMethodService;
use Bank\BankPackages\YourPaymentProcessor;

class CreditCard implements PaymentMethodService
{
    private $amount;
    private $currency;
    private $creditCard;

    public function amount($amount = 0) {
        $this->amount = $amount;
        
        return $this;
    }
    
    public function currency($currency = 'EUR') {
        $this->currency = $currency;
        
        return $this;
    }

    public function creditCard($creditCard = null) {
        $this->creditCard = $creditCard;
        
        return $this;
    }
     
    /**
     * Charge desired amount with your favorite bank
     * @return void
     */
    public function charge()
    {
        $processor = new YourPaymentProcessor();
        $processor->setParameter('MERCHANT_CURRENCY', $this->currency);
        $processor->setParameter('MERCHANT_AMOUNT', $this->amount);
        $processor->setParameter('MERCHANT_CARD', $this->creditCard);
        $processor->pay();
    }
}
```

### Make the service available

In your config file, add a name and the path of your new payment method:

```php 
'services' => [
        'schedule' => \Bpuig\Subby\Services\ScheduleService::class,
        'renewal' => \Bpuig\Subby\Services\RenewalService::class,
        'payment_methods' => [
            'free' => \Bpuig\Subby\Services\PaymentMethods\Free::class,
            'credit_card' => \PaymentMethods\CreditCard::class,
        ]
]
```
