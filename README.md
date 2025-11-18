this is my current readme: "# [ElmMac](https://elmmac.co.za/) - Laravel iKhokha Payment Integration API 💳

# Laravel iKhokha Payment Integration API (elmmac/ikhokha) 💳

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elmmac/ikhokha.svg?style=flat-square)](https://packagist.org/packages/elmmac/ikhokha)
[![Total Downloads](https://img.shields.io/packagist/dt/elmmac/ikhokha.svg?style=flat-square)](https://packagist.org/packages/elmmac/ikhokha)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-9%2F10-orange.svg)](https://laravel.com)
[![GitHub Repo stars](https://img.shields.io/github/stars/ElmMac/ikhokha?style=social)](https://github.com/ElmMac/ikhokha/stargazers)


Official Laravel package to integrate [iKhokha](https://www.ikhokha.com/) Pay Links and Webhooks into your Laravel projects.

---

## 📘 ElmMac iKhokha Laravel Package API

This Laravel package provides a native iKhokha payment integration layer, complete with:

-   Webhook handling
-   Database logging
-   API client functionality
-   Optional UI blade views for frontend integration
-   Paylink creation
-   Easily extendable API client

Perfect for:

✔ SaaS platforms
✔ Marketplaces
✔ Payment-based apps
✔ Donation systems
✔ Subscription-like flows

🔒 Secure, scalable, and modular — ideal for custom Laravel SaaS and marketplaces.

---

## 🛆 Features

✅ Webhook listener & processor\
✅ Auto-persist `IkhokhaPayment` records\
✅ Extendable `IkhokhaClient` for outbound API calls\
✅ Built for Laravel 9/10+\
✅ Clean PSR-4 package structure\
✅ Optional config, views, and migration publishing\
✅ Optional UI views included (Blade templates)

#### Current Package Structure

```

elmmac/ikhokha/
│
├── composer.json
│
├── config/
│   └── ikhokha.php
│
├── database/
│   └── migrations/
│       └── 2025_06_30_130134_create_ikhokha_payments_table.php
│
├── resources/
│   └── views/
│       └── ikhokha/
│           ├── success.blade.php
│           ├── failed.blade.php
│           └── cancel.blade.php
│
├── src/
│   ├── Http/
│   │   └── Controllers/
│   │       └── IkhokhaPaymentController.php
│   │
│   ├── Models/
│   │   └── IkhokhaPayment.php
│   │
│   ├── Services/
│   │   └── IkhokhaClient.php
│   │
│   ├── routes/                     ← **ROUTES ARE HERE**
│   │   ├── api.php
│   │   └── web.php
│   │
│   └── IkhokhaServiceProvider.php
│
└── README.md



```

---

## 🛆 Installation

### Step 1: Install via Composer

```bash
composer require elmmac/ikhokha
```

### Step 2: Register Service Provider (if needed)

> For Laravel < 5.5 or explicit config

```php
'providers' => [
    Elmmac\Ikhokha\IkhokhaServiceProvider::class,
],
```

---

## ⚙️ Configuration

### Optional: Publish the config

```bash
php artisan vendor:publish --tag=ikhokha-config
```

This publishes: `config/ikhokha.php`

### Edit your `.env` file:

```dotenv
IKHOKHA_APP_ID=YOUR_APP_ID
IKHOKHA_SIGN_SECRET=YOUR_SECRET
IKHOKHA_WEBHOOK_URL=https://yourdomain.com/api/ikhokha/callback
```

---

## 📄 Publish Views (Optional UI)

```bash
php artisan vendor:publish --tag=ikhokha-views
```

Views will be published to: `resources/views/ikhokha`

Use them as base templates or customize freely.

---

## 🗃️ Migrations

### Optional: Publish Migrations

```bash
php artisan vendor:publish --tag=ikhokha-migrations
```

Migration will be published to: `database/migrations/`

Then run:

```bash
php artisan migrate
```

Model:

```php
Elmmac\Ikhokha\Models\IkhokhaPayment
```

Table: `ikhokha_payments`

Add relationship in your `User.php`:

```php
public function ikhokha_payments()
{
    return $this->hasMany(IkhokhaPayment::class);
}
```

And in `IkhokhaPayment.php`:

```php
public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id');
}
```

🛆 Example Manual Entry:

```php
IkhokhaPayment::create([
    'user_id' => $user->id ?? null,
    'customer_email' => $user->email ?? $request->input(key: 'customer_email', default: 'misael@elmmac.co.za'),
    'transaction_id' => $externalTransactionID,
    'description' => $request->input('description', 'Payment'),
    'paylink_id' => $data['paylinkID'] ?? null,
    'amount' => $amount,
    'currency' => $currency,
    'status' => 'pending',
    'payment_url' => $data['paylinkUrl'] ?? null,
    'webhook_signature' => $ikSign,
    'metadata' => $data,
]);
```
---

## 🔗 Create Payment Link Payload - REQUEST | Refer to 🔗 [iKhokha API Overview](https://dev.ikhokha.com/overview) for more.

Use this structure to initiate payment (All iKhokha Requests & Responses MUST be in JSON format):

```php Request Object
$payload = [
    "entityID" => $appID,
    "amount" => (int) $amount * 100,
    "currency" => $currency,
    "requesterUrl" => url()->current(),
    "mode" => $mode,
    "externalTransactionID" => $externalTransactionID,
    "urls" => [
        "callbackUrl" => route(name: 'ikhokha.webhook'),
        "successPageUrl" => route(name: 'ikhokha.success'),
        "failurePageUrl" => route(name: 'ikhokha.failed'),
        "cancelUrl" => route(name: 'ikhokha.cancel'),
    ]
];
```
Create Paylink/Payment Link Response Object
```
CREATE PAYMENT LINK - RESPONSE
{
    "responseCode": "00",
    "message": "",
    "paylinkUrl": "https://securepay.ikhokha.red/2zh1zj6y8xpb0g3",
    "paylinkID": "2zh1zj6y8xpb0g3",
    "externalTransactionID": "TRANS789" // $transactionId
}
```


---

## 🔁 Webhook Setup | Refer to 🔗 [iKhokha API Overview](https://dev.ikhokha.com/overview) for more.


📬 Webhook URL:

```
POST /api/ikhokha/webhook
```

Required headers:

```
ik-appid
ik-sign
Content-Type: application/json
```

📨 Sample Payload:

```json
{
    "paylinkID": "2zh1zj6y8xpb0g3", // Gotten from Create Payment Link Response
    "status": "SUCCESS",
    "externalTransactionID": "IKH_REF_CODE_9911",
    "responseCode": "00"
}
```

---

## 🚦 Route Summary

| Method | URL                    | Purpose                     |
| ------ | ---------------------- | --------------------------- |
| POST   | `/ikhokha-initiate`    | Create payment link         |
| GET    | `/ikhokha/success`     | Redirect page after payment |
| GET    | `/ikhokha/failed`      | Failed payment view         |
| GET    | `/ikhokha/cancel`      | Cancel payment view         |
| POST   | `/api/ikhokha/webhook` | Webhook callback            |


---

## 📋 Artisan Helper Tags (Optional)

For dev reminders:

```bash
php artisan vendor:publish --tag=ikhokha-config      # Publish config
php artisan vendor:publish --tag=ikhokha-views       # Publish views
php artisan vendor:publish --tag=ikhokha-migrations  # Publish migrations
```

Use only the ones you need! 💡

---

## 🧠 Roadmap

-   Full unit testing with PHPUnit
-   Laravel Octane compatibility
-   Tokenized card billing
-   Refunds & reversals
-   Multi-merchant support
-   Optional payment UI component scaffolds
***********
iKhokha Subscription Tokenization
Eloquent Traits (Billable)
Octane compatibility

---

## 🤝 Contributing

Pull requests welcome. Open an issue for proposals or fixes.\
Make sure tests pass if you're submitting functional changes.

---

## 🙏 Credits

Developed with 🚀 by **ElmMac Pty Ltd**\
Maintained by @ElmMac - **Misael Cruise Mutege** — [WhatsApp: +27786411181](https://web.whatsapp.com/send?phone=27786411181)  Durban, South Africa\
Digital Dev | Hustler Mode: `ON 💼`

---

## 📄 License

MIT License © [ElmMac](https://elmmac.co.za/)
"  ||| just edit according to the chamges say like the folder structure excluding the package folder, etc depending on what has changed so far.
