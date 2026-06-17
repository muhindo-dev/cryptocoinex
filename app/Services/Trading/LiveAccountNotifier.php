<?php

namespace App\Services\Trading;

use App\Mail\LiveAccountNotice;
use App\Models\Trading\DepositRequest;
use App\Models\Trading\WithdrawalRequest;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Centralises every Live Account message — both the in-app notification and the
 * branded email — so the deposit/withdrawal flows stay consistent and a mail
 * failure can never break a money operation (sends are wrapped + queued).
 */
class LiveAccountNotifier
{
    public function __construct(private readonly NotificationService $notifications) {}

    private function currency(): string
    {
        return (string) \App\Models\Trading\TradingSetting::get('live_account_currency', 'USD');
    }

    /** Email addresses of all admins who should review Live Account activity. */
    private function adminEmails(): array
    {
        return User::query()
            ->where(fn ($q) => $q->where('role', 'admin')->orWhere('is_admin', true))
            ->whereNotNull('email')
            ->pluck('email')
            ->unique()
            ->all();
    }

    private function safeMail(array $to, LiveAccountNotice $mail): void
    {
        if (empty($to)) {
            return;
        }
        try {
            Mail::to($to)->queue($mail);
        } catch (\Throwable $e) {
            Log::warning('Live Account email failed to queue: '.$e->getMessage());
        }
    }

    // ── Deposits ─────────────────────────────────────────────────────────────

    public function depositRequested(DepositRequest $req): void
    {
        $amount = Money::format($req->amount, $this->currency());

        // Notify all admins by email.
        $this->safeMail($this->adminEmails(), new LiveAccountNotice(
            subjectLine: "New deposit request — {$amount} from {$req->user->name}",
            heading: 'A new deposit request needs review',
            intro: "{$req->user->name} has submitted a crypto deposit with proof of payment and is waiting for you to verify and approve it.",
            rows: array_filter([
                'Student' => e($req->user->name).' &middot; '.e($req->user->email),
                'Amount' => $amount,
                'Txn reference' => $req->reference ? e($req->reference) : null,
                'Proof' => $req->proof_path ? 'Screenshot attached' : 'Not provided',
                'Submitted' => $req->created_at->format('d M Y, H:i'),
            ]),
            ctaUrl: route('admin.trading.live.deposits'),
            ctaLabel: 'Review deposit requests',
            accent: '#16a34a',
            noteTitle: 'Before you approve',
            noteBody: 'Open the request to view the payment screenshot and confirm the crypto actually arrived in the wallet before approving. Approving credits real funds to the student\'s Live Account.',
        ));
    }

    public function depositApproved(DepositRequest $req): void
    {
        $amount = Money::format($req->amount, $this->currency());

        $this->notifications->notify(
            $req->user_id, 'live_deposit', 'Deposit approved',
            "Your deposit of {$amount} has been confirmed and added to your Live Account.",
            route('trade.live'), 'fa-circle-check',
        );

        $this->safeMail([$req->user->email], new LiveAccountNotice(
            subjectLine: "Deposit approved — {$amount} added to your Live Account",
            heading: 'Your deposit is confirmed ✅',
            intro: "Good news, {$req->user->name}! We verified your payment and credited your Live Account. It's now working for you and will start earning daily managed-trading returns once it matures (after 24 hours).",
            rows: [
                'Amount credited' => $amount,
                'Reference' => e($req->reference),
                'Approved' => $req->reviewed_at?->format('d M Y, H:i'),
            ],
            ctaUrl: route('trade.live'),
            ctaLabel: 'Open my Live Account',
            accent: '#16a34a',
        ));
    }

    public function depositDeclined(DepositRequest $req): void
    {
        $amount = Money::format($req->amount, $this->currency());

        $this->notifications->notify(
            $req->user_id, 'live_deposit', 'Deposit request declined',
            "Your deposit request for {$amount} was not approved.".($req->admin_note ? ' Reason: '.$req->admin_note : ''),
            route('trade.live'), 'fa-circle-xmark',
        );

        $this->safeMail([$req->user->email], new LiveAccountNotice(
            subjectLine: 'Your deposit request was declined',
            heading: 'We couldn\'t approve this deposit',
            intro: "Hi {$req->user->name}, we reviewed your deposit request for {$amount} (reference {$req->reference}) but were unable to approve it.",
            rows: array_filter([
                'Amount' => $amount,
                'Reference' => e($req->reference),
                'Reason' => $req->admin_note ? e($req->admin_note) : null,
            ]),
            ctaUrl: route('trade.live.deposit'),
            ctaLabel: 'Try again',
            accent: '#dc2626',
            noteTitle: 'Think this is a mistake?',
            noteBody: 'Double-check the amount, the network, and your payment screenshot, then submit a new deposit request and we\'ll help sort it out.',
        ));
    }

    // ── Withdrawals ──────────────────────────────────────────────────────────

    public function withdrawalRequested(WithdrawalRequest $req): void
    {
        $amount = Money::format($req->amount, $this->currency());

        $this->safeMail($this->adminEmails(), new LiveAccountNotice(
            subjectLine: "New withdrawal request — {$amount} from {$req->user->name}",
            heading: 'A withdrawal request needs processing',
            intro: "{$req->user->name} has requested a withdrawal from their Live Account. Send the USDT to the wallet address below, then approve the request to record the debit.",
            rows: array_filter([
                'Student' => e($req->user->name).' &middot; '.e($req->user->email),
                'Amount' => $amount,
                'Send to' => e($req->destination),
                'Network' => $req->payout_network ? e($req->payout_network) : null,
                'Submitted' => $req->created_at->format('d M Y, H:i'),
            ]),
            ctaUrl: route('admin.trading.live.withdrawals'),
            ctaLabel: 'Process withdrawals',
            accent: '#2563eb',
            noteTitle: 'How it works',
            noteBody: 'Send the USDT to the address first, then approve here. Approving creates the negative transaction on their Live Account. Decline if you cannot fulfil it — no funds are touched.',
        ));
    }

    public function withdrawalApproved(WithdrawalRequest $req): void
    {
        $amount = Money::format($req->amount, $this->currency());

        $this->notifications->notify(
            $req->user_id, 'live_withdrawal', 'Withdrawal paid',
            "Your withdrawal of {$amount} has been sent to {$req->destination}.",
            route('trade.live'), 'fa-money-bill-transfer',
        );

        $this->safeMail([$req->user->email], new LiveAccountNotice(
            subjectLine: "Withdrawal paid — {$amount} sent to your wallet",
            heading: 'Your withdrawal is on its way 💸',
            intro: "Hi {$req->user->name}, we've sent your withdrawal in USDT to your wallet address. The amount has been deducted from your Live Account balance.",
            rows: array_filter([
                'Amount sent' => $amount,
                'Sent to' => e($req->destination),
                'Reference' => $req->payout_reference ? e($req->payout_reference) : null,
                'Processed' => $req->reviewed_at?->format('d M Y, H:i'),
            ]),
            ctaUrl: route('trade.live'),
            ctaLabel: 'View my Live Account',
            accent: '#2563eb',
        ));
    }

    public function withdrawalDeclined(WithdrawalRequest $req): void
    {
        $amount = Money::format($req->amount, $this->currency());

        $this->notifications->notify(
            $req->user_id, 'live_withdrawal', 'Withdrawal request declined',
            "Your withdrawal request for {$amount} was not approved.".($req->admin_note ? ' Reason: '.$req->admin_note : '').' Your balance is unchanged.',
            route('trade.live'), 'fa-circle-xmark',
        );

        $this->safeMail([$req->user->email], new LiveAccountNotice(
            subjectLine: 'Your withdrawal request was declined',
            heading: 'We couldn\'t process this withdrawal',
            intro: "Hi {$req->user->name}, your withdrawal request for {$amount} was not approved. Your Live Account balance has not changed.",
            rows: array_filter([
                'Amount' => $amount,
                'Requested to' => e($req->destination),
                'Reason' => $req->admin_note ? e($req->admin_note) : null,
            ]),
            ctaUrl: route('trade.live'),
            ctaLabel: 'Back to my Live Account',
            accent: '#dc2626',
        ));
    }
}
