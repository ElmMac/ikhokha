# [ElmMac](https://elmmac.co.za/) - Laravel iKhokha Payment Integration API 💳

# Laravel iKhokha Payment Integration API (elmmac/ikhokha) 💳

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
elmmac/
└── ikhokha/
    ├── composer.json
    ├── config/
    │   └── ikhokha.php
    ├── database/
    │   └── migrations/
    │       └── 2025_07_03_000000_create_ikhokha_payments_table.php
    ├── resources/
    │   └── views/
    │       └── success.blade.php
    │       └── failed.blade.php
    │       └── cancel.blade.php
    ├── routes/
    │   ├── api.php
    │   └── web.php
    ├── src/
    │   ├── Http/
    │   │   └── Controllers/
    │   │       └── IkhokhaPaymentController.php
    │   ├── Models/
    │   │   └── IkhokhaPayment.php
    │   ├── Services/
    │   │   └── IkhokhaClient.php
    │   └── IkhokhaServiceProvider.php
    ├── README.md
    └── .gitignore (if present, or generated during GitHub push)

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

Views will be published to: `resources/views/vendor/ikhokha`

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
    'user_id' => 1,
    'paylink_id' => 'test001',
    'description' => 'Test payment',
    'customer_email' => 'test@example.com',
    'transaction_id' => 'IKH_TEST_TXID_001',
    'amount' => 5000,
    'status' => 'pending',
]);
```
---

## 🔗 Create Payment Link Payload - REQUEST | Refer to 🔗 [iKhokha API Overview](https://dev.ikhokha.com/overview) for more.

Use this structure to initiate payment (All iKhokha Requests & Responses MUST be in JSON format):

```php Request Object
$payload = [
    "entityID" => config('ikhokha.entry_id'),
    "externalEntityID" => config('ikhokha.external_entity_id'),
    "amount" => 5000,
    "currency" => "ZAR",
    "requesterUrl" => config('ikhokha.urls.requester_url'),
    "mode" => "live",
    "externalTransactionID" => $transactionId, // TRANS789
    "urls" => [
        "callbackUrl" => config('ikhokha.urls.callback_url'),
        "successPageUrl" => config('ikhokha.urls.success_url'),
        "failurePageUrl" => config('ikhokha.urls.failure_url'),
        "cancelUrl" => config('ikhokha.urls.cancel_url')
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
POST /api/ikhokha/callback
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

| Method | URI                   | Description                     |
| ------ | --------------------- | ------------------------------- |
| POST   | /initiate-payment     | Start payment via IkhokhaClient |
| GET    | /ikhokha/success      | Success redirect URL            |
| GET    | /ikhokha/failed       | Failure redirect URL            |
| GET    | /ikhokha/cancel       | Cancel redirect URL             |
| POST   | /api/ikhokha/callback | Webhook endpoint from iKhokha   |

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

---

## 🤝 Contributing

Pull requests welcome. Open an issue for proposals or fixes.\
Make sure tests pass if you're submitting functional changes.

---

## 🙏 Credits

Developed with 🚀 by **ElmMac Devs**\
Maintained by **Misael Cruise Mutege** — [WhatsApp: +27786411181](https://web.whatsapp.com/send?phone=27786411181)  Durban, South Africa\
Digital Dev | Hustler Mode: `ON 💼`

---

## 📄 License

MIT License © [ElmMac](https://elmmac.co.za/)
