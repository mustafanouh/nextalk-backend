# 💬 NexTalk — Backend API

The backend of **NexTalk**, a real-time chat and video calling platform. Built with **Laravel 12**, it provides a REST API, WebSocket broadcasting (Laravel Reverb), and a WebRTC signaling server.

> Frontend repository: `nextalk-frontend` (Next.js)

---

## 💡 Core Idea

A call that looks simple to the user goes through a full pipeline behind the scenes:

```
Authentication → WebSocket → Signaling → SDP → ICE → WebRTC → Media Streams
```

This backend handles the first half of that pipeline: authenticating users, authorizing WebSocket channels, managing call state, and relaying WebRTC signaling messages (SDP offers/answers and ICE candidates) between peers.

---

## ✨ Features

- 🔐 Authentication with **Laravel Sanctum** (register, login, logout, forgot/reset password)
- ✉️ Email verification using **signed URLs**
- 💬 Real-time messaging and message deletion via WebSockets
- 📎 File attachments stored on S3-compatible object storage
- 📞 Voice & video call management (`ringing → active → ended / rejected / missed`)
- 🔁 WebRTC signaling endpoints (offer, answer, ICE candidate)
- 👤 User profiles and avatars (Spatie Media Library)
- 🔎 User search by username
- 🛡️ Policies for conversations, messages, and calls
- 🚦 Rate limiting on auth endpoints

---

## 🛠️ Tech Stack

| Category | Technology |
|----------|-----------|
| Framework | Laravel 12 (PHP 8.2+) |
| Database | MySQL |
| Authentication | Laravel Sanctum |
| WebSocket Server | Laravel Reverb |
| Cache / Queue / Session | Redis |
| File Storage | S3-compatible object storage |
| Media | Spatie Media Library |
| API | RESTful API |
| Testing | PHPUnit |
| Code Style | Laravel Pint |

---

## 🏗️ Architecture

```
app/
├── Events/          # Broadcast events (MessageSent, IncomingCall, WebRTCOffer, ...)
├── Exceptions/      # Domain exceptions (InvalidCallStateException)
├── Http/
│   ├── Controllers/ # Thin controllers
│   ├── Requests/    # Form Request validation
│   └── Resources/   # API Resources (response shaping)
├── Models/          # User, Conversation, Message, Attachment, Call
├── Notifications/   # Verify email, reset password
├── Policies/        # Conversation, Message, Call authorization
└── Services/        # Business logic (Call, Message, Conversation, Attachment)
routes/
├── api.php          # REST endpoints
└── channels.php     # WebSocket channel authorization
```

### Key design ideas

- **Service Layer** — business logic lives in services, keeping controllers thin.
- **Form Requests + API Resources** — validation and response formatting are separated from controllers.
- **Policies** — only conversation participants can read messages or start/join calls.
- **Call state machine** — `CallService` validates transitions (e.g. only a *ringing* call can be accepted, and only the callee can accept/reject). Invalid transitions throw `InvalidCallStateException`. A ringing call cancelled by the caller becomes `missed`.
- **Event broadcasting** — every real-time action has a dedicated event:
  `MessageSent`, `MessageDeleted`, `IncomingCall`, `CallAccepted`, `CallRejected`, `CallEnded`, `WebRTCOffer`, `WebRTCAnswer`, `ICECandidate`.
- **Channel authorization**
  - `user.{userId}` — private channel; users can only listen to their own.
  - `call.{callId}` — presence channel; only participants of the call's conversation may join.
- **Signed verification links** — the email verification route sits outside `auth:sanctum` (the browser tab has no bearer token) and is secured by the `signed` middleware.

---

## 🔌 API Overview

Protected routes require `Authorization: Bearer <token>` (`auth:sanctum`).

| Group | Endpoints |
|-------|-----------|
| Auth (public) | `POST /api/auth/register`, `/login`, `/forgot-password`, `/reset-password` |
| Auth (protected) | `POST /api/auth/logout`, `GET /api/auth/me` |
| Email verification | `GET /api/auth/email/verify/{id}/{hash}` (signed) |
| Users | `GET/PATCH /api/users/me`, `GET /api/users/search`, `GET /api/users/{username}` |
| Conversations | `GET /api/conversations`, `POST /api/conversations`, `GET /api/conversations/{id}` |
| Messages | `GET/POST /api/conversations/{id}/messages`, `DELETE /api/messages/{id}` |
| Calls | `POST /api/conversations/{id}/calls`, `POST /api/calls/{id}/accept`, `/reject`, `/end` |
| Signaling | `POST /api/calls/{id}/signal/offer`, `/signal/answer`, `/signal/ice-candidate` |

A ready-to-use Postman collection is included: `NexTalk.postman_collection.json`.

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+ and Composer
- MySQL
- Redis
- Node.js (for Vite assets / dev script)
- An S3-compatible bucket (AWS S3, Cloudflare R2, MinIO, Backblaze, ...)

### Installation

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure .env (database, Redis, Reverb, S3, mail, frontend URL)

# 4. Run migrations
php artisan migrate
```

### Important environment variables

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000

DB_CONNECTION=mysql
DB_DATABASE=nextalk

SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

FILESYSTEM_DISK=s3
MEDIA_DISK=s3
AWS_BUCKET=
AWS_DEFAULT_REGION=
```

### Run the services

```bash
php artisan serve            # API       -> http://localhost:8000
php artisan reverb:start     # WebSocket -> port 8080
php artisan queue:listen     # Queue worker
```

Or run everything together:

```bash
composer run dev
```

### Tests & code style

```bash
php artisan test
./vendor/bin/pint
```

---

## 📞 TURN / STUN

For calls to work across different networks (NAT), configure a TURN server such as **Coturn** in production. ICE servers are configured on the frontend side.

---

## 🧠 Lessons Learned

- WebSocket **channel authorization** is its own layer of security and a common source of silent failures.
- Real-time features have a hidden world behind the scenes: signaling, SDP, ICE, and connection state.
- Treat a problem as something to **understand**, not just a "bug to fix".

---

## 🏷️ Tags

`#NexTalk` `#Laravel` `#PHP` `#LaravelReverb` `#WebSockets` `#WebRTC` `#RealTime` `#MySQL` `#Redis` `#SoftwareEngineering`
