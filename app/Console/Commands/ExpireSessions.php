<?php

namespace App\Console\Commands;

use App\Events\SessionEnded;
use App\Models\TierTalkSession;
use Illuminate\Console\Command;

class ExpireSessions extends Command
{
    protected $signature = 'sessions:expire';

    protected $description = 'Expire TierTalk sessions that have passed their expiration time';

    public function handle(): int
    {
        $sessions = TierTalkSession::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($sessions as $session) {
            $session->update(['status' => 'ended']);
            SessionEnded::dispatch($session);
            $count++;
        }

        $this->info("Expired {$count} session(s).");

        return Command::SUCCESS;
    }
}
