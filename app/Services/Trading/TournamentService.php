<?php

namespace App\Services\Trading;

use App\Models\Trading\Tournament;
use App\Models\Trading\TournamentParticipant;
use App\Models\User;
use Illuminate\Support\Collection;

class TournamentService
{
    public function __construct(private readonly NotificationService $notifications) {}

    /**
     * Join a user into a tournament (idempotent).
     */
    public function join(Tournament $tournament, User $user): TournamentParticipant
    {
        return TournamentParticipant::firstOrCreate(
            ['tournament_id' => $tournament->id, 'user_id' => $user->id],
            ['joined_at' => now()]
        );
    }

    /**
     * Live standings for a tournament: each participant's net P&L from settled
     * trades on the allowed asset within the tournament window, ranked.
     *
     * @return Collection<int, array{rank:int,user_id:int,name:string,avatar:?string,pnl:int,balance:int,trades:int}>
     */
    public function standings(Tournament $tournament): Collection
    {
        $tournament->loadMissing('participants.user.tradingWallet');

        return $tournament->participants
            ->map(function (TournamentParticipant $p) use ($tournament) {
                // Finalised tournaments use the frozen figures.
                if ($p->final_pnl !== null) {
                    return [
                        'user_id' => $p->user_id,
                        'name' => $p->user?->name ?? '—',
                        'avatar' => $p->user?->avatar_url,
                        'pnl' => (int) $p->final_pnl,
                        'balance' => (int) $p->final_balance,
                        'trades' => 0,
                    ];
                }

                [$pnl, $count] = $this->livePnl($tournament, $p->user_id);

                return [
                    'user_id' => $p->user_id,
                    'name' => $p->user?->name ?? '—',
                    'avatar' => $p->user?->avatar_url,
                    'pnl' => $pnl,
                    'balance' => (int) $tournament->starting_balance + $pnl,
                    'trades' => $count,
                ];
            })
            ->sortByDesc('balance')
            ->values()
            ->map(function ($row, $i) {
                $row['rank'] = $i + 1;

                return $row;
            });
    }

    /**
     * Net P&L and trade count for a user within the tournament window/asset.
     *
     * @return array{0:int,1:int}
     */
    private function livePnl(Tournament $tournament, int $userId): array
    {
        $q = \App\Models\Trading\Trade::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['won', 'lost', 'tie'])
            ->whereBetween('opened_at', [$tournament->starts_at, $tournament->ends_at]);

        if ($tournament->asset_id) {
            $q->where('asset_id', $tournament->asset_id);
        }

        $trades = $q->get(['stake', 'payout_amount']);
        $pnl = (int) $trades->sum(fn ($t) => ((int) ($t->payout_amount ?? 0)) - (int) $t->stake);

        return [$pnl, $trades->count()];
    }

    /**
     * Freeze final standings, declare the winner, mark ended.
     */
    public function finalize(Tournament $tournament): void
    {
        $standings = $this->standings($tournament);

        foreach ($standings as $row) {
            TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('user_id', $row['user_id'])
                ->update([
                    'final_pnl' => $row['pnl'],
                    'final_balance' => $row['balance'],
                    'final_rank' => $row['rank'],
                ]);
        }

        $winner = $standings->first();
        $tournament->update([
            'status' => 'ended',
            'winner_user_id' => $winner['user_id'] ?? null,
        ]);

        // Notify participants of the result.
        foreach ($standings as $row) {
            $isWinner = $row['rank'] === 1;
            $this->notifications->notify(
                $row['user_id'],
                'tournament_result',
                $isWinner ? "🏆 You won {$tournament->name}!" : "{$tournament->name} ended — you placed #{$row['rank']}",
                "Final balance: {$row['balance']} PRACTICE\$ (P&L {$row['pnl']}).",
                route('trade.tournaments.show', $tournament),
                $isWinner ? 'fa-trophy' : 'fa-flag-checkered',
            );
        }
    }

    /**
     * Sync stored status with the clock; finalize any that have just ended.
     */
    public function refreshStatuses(): int
    {
        $finalized = 0;

        Tournament::where('status', '!=', 'ended')->get()->each(function (Tournament $t) use (&$finalized) {
            $live = $t->liveStatus();
            if ($live === 'ended') {
                $this->finalize($t);
                $finalized++;
            } elseif ($live !== $t->status) {
                $t->update(['status' => $live]);
            }
        });

        return $finalized;
    }
}
