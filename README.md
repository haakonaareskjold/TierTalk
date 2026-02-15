# TierTalk 🗣️

A real-time voting and feedback application built with Laravel, Livewire, and Laravel Reverb for WebSocket support.

## Features

- **Create Voting Sessions**: Hosts can create sessions with multiple questions, set participant limits, and configure expiration times
- **Share Links**: Generate unique URLs that participants can use to join sessions
- **Real-time Voting**: Participants can cast votes (1-5 scale) on questions
- **Live Updates**: Hosts see vote results in real-time via WebSockets
- **Session Management**: Hosts can add new questions, reset votes, and end sessions on the fly
- **No Account Required**: Participants join with just a username - no signup needed

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Livewire 4
- **Database**: MySQL
- **Real-time**: Laravel Reverb (WebSockets)
- **Styling**: Tailwind CSS

## Installation

### Prerequisites

- PHP 8.2+
- Composer
- MySQL
- Node.js (for Reverb)

### Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd TierTalk
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tiertalk
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the development servers**
   
   In separate terminals:
   ```bash
   # Terminal 1: Laravel development server
   php artisan serve
   
   # Terminal 2: Laravel Reverb WebSocket server
   php artisan reverb:start
   
   # Terminal 3: Queue worker (for broadcasting events)
   php artisan queue:work
   ```

### Using Laravel Sail (Docker)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan reverb:start
./vendor/bin/sail artisan queue:work
```

## Usage

### Creating a Session (Host)

1. Go to the homepage and click "Start a TierTalk Session"
2. Fill in:
   - Session title (optional)
   - One or more questions
   - Maximum number of participants
   - Session duration
3. Click "Create Session"
4. You'll be redirected to the Host Dashboard with a shareable link

### Joining a Session (Participant)

1. Open the shared link
2. Enter your username
3. Click "Join Session"
4. Vote on questions (1-5 scale)

### Host Controls

- **Add Questions**: Add new questions during the session
- **Reset Votes**: Clear all votes for a specific question
- **Toggle Questions**: Activate/deactivate questions
- **Delete Questions**: Remove questions from the session
- **End Session**: Close the session for all participants

## Project Structure

```
app/
├── Console/Commands/
│   └── ExpireSessions.php      # Scheduled command for session cleanup
├── Events/
│   ├── ParticipantJoined.php   # Broadcast when participant joins
│   ├── QuestionAdded.php       # Broadcast when host adds question
│   ├── QuestionReset.php       # Broadcast when votes are reset
│   ├── SessionEnded.php        # Broadcast when session ends
│   └── VoteCast.php            # Broadcast when vote is cast
├── Livewire/
│   ├── CreateSession.php       # Session creation form
│   ├── HostDashboard.php       # Host management interface
│   ├── JoinSession.php         # Participant join form
│   └── VotingInterface.php     # Participant voting interface
└── Models/
    ├── Participant.php         # Temporary session users
    ├── Question.php            # Session questions
    ├── TierTalkSession.php     # Main session model
    └── Vote.php                # Vote records
```

## Scheduled Tasks

Add the following to your crontab for automatic session expiration:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## API Routes

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/` | Home page |
| GET | `/create` | Create session form |
| GET | `/s/{slug}` | Join session page |
| GET | `/s/{slug}/vote` | Voting interface |
| GET | `/s/{slug}/host?token={token}` | Host dashboard |

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
