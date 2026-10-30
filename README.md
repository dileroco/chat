# Chat API

Laravel backend for the chat mobile and web clients.

## Requirements

- PHP 8.2+
- Composer
- MySQL (or another configured Laravel database)
- Node.js and npm for the optional web assets

## Setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0
```

Configure the database values in `.env` before running migrations. Never commit `.env`.

The API is available under `/api`. The mobile client uses:

- Android emulator: `http://10.0.2.2:8000/api`
- iOS simulator or desktop browser: `http://127.0.0.1:8000/api`
- Physical device: `http://YOUR_COMPUTER_LAN_IP:8000/api`

## Tests

```bash
php artisan test
```

## Main endpoints

- `POST /api/login`
- `POST /api/signup`
- `POST /api/logout`
- `GET /api/profile`
- `POST /api/profile`
- `GET /api/users`
- `GET /api/contacts`
- `POST /api/messages`
- `POST /api/send-message`

Profile images are stored in `public/react/images/profile-picture` and should be treated as user-uploaded data rather than source code.
