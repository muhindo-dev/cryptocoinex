<?php

namespace App\Services\Trading;

use App\Mail\LiveAccountNotice;
use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Identity verification (KYC) workflow. A submission moves
 *   pending → approved | declined | resubmit
 * and the user's fast-path {@see User::$kyc_status} mirrors the latest outcome.
 */
class KycService
{
    public function __construct(private readonly NotificationService $notifications) {}

    /** File an identity-verification attempt. Only allowed when not already pending/approved. */
    public function submit(User $user, array $data, string $documentPath): KycSubmission
    {
        if (! $user->canSubmitKyc()) {
            throw new RuntimeException('You already have a verification in progress.');
        }

        return DB::transaction(function () use ($user, $data, $documentPath) {
            $submission = KycSubmission::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'document_type' => $data['document_type'],
                'document_number' => $data['document_number'],
                'document_path' => $documentPath,
                'message' => $data['message'] ?? null,
                'status' => 'pending',
            ]);

            $user->forceFill(['kyc_status' => 'pending'])->save();

            $this->emailAdmins($user, $submission);

            return $submission;
        });
    }

    public function approve(KycSubmission $submission, User $admin): KycSubmission
    {
        return $this->review($submission, $admin, 'approved', null);
    }

    public function decline(KycSubmission $submission, User $admin, ?string $note): KycSubmission
    {
        return $this->review($submission, $admin, 'declined', $note);
    }

    public function requestRedo(KycSubmission $submission, User $admin, ?string $note): KycSubmission
    {
        return $this->review($submission, $admin, 'resubmit', $note);
    }

    private function review(KycSubmission $submission, User $admin, string $status, ?string $note): KycSubmission
    {
        return DB::transaction(function () use ($submission, $admin, $status, $note) {
            $submission = KycSubmission::lockForUpdate()->findOrFail($submission->id);

            if (! $submission->isPending()) {
                throw new RuntimeException('This verification has already been reviewed.');
            }

            $submission->update([
                'status' => $status,
                'admin_note' => $note,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $user = $submission->user;
            $user->forceFill([
                'kyc_status' => $status,
                'kyc_verified_at' => $status === 'approved' ? now() : null,
            ])->save();

            $this->notifyUser($user, $submission, $status);

            return $submission;
        });
    }

    // ── Notifications ────────────────────────────────────────────────────────

    private function notifyUser(User $user, KycSubmission $submission, string $status): void
    {
        [$title, $body, $icon] = match ($status) {
            'approved' => ['Identity verified ✅', 'Your identity verification was approved. You can now go Live and use real-money features.', 'fa-circle-check'],
            'declined' => ['Verification declined', 'Your identity verification was not approved.'.($submission->admin_note ? ' Reason: '.$submission->admin_note : ''), 'fa-circle-xmark'],
            'resubmit' => ['Please re-verify', 'We need you to redo your identity verification.'.($submission->admin_note ? ' '.$submission->admin_note : ''), 'fa-rotate'],
            default => ['Verification update', 'Your verification status changed.', 'fa-id-card'],
        };

        $this->notifications->notify($user->id, 'kyc', $title, $body, route('trade.kyc'), $icon);

        $this->safeMail([$user->email], new LiveAccountNotice(
            subjectLine: $title,
            heading: $title,
            intro: $body,
            rows: array_filter([
                'Status' => ucfirst($status),
                'Reviewed' => $submission->reviewed_at?->format('d M Y, H:i'),
                'Note' => $submission->admin_note ? e($submission->admin_note) : null,
            ]),
            ctaUrl: route('trade.kyc'),
            ctaLabel: $status === 'approved' ? 'Start trading live' : 'Open verification',
            accent: $status === 'approved' ? '#16a34a' : '#dc2626',
        ));
    }

    private function emailAdmins(User $user, KycSubmission $submission): void
    {
        $admins = User::query()
            ->where(fn ($q) => $q->where('role', 'admin')->orWhere('is_admin', true))
            ->whereNotNull('email')->pluck('email')->unique()->all();

        $this->safeMail($admins, new LiveAccountNotice(
            subjectLine: 'New identity verification — '.$user->name,
            heading: 'A verification needs review',
            intro: "{$user->name} submitted identity documents for review.",
            rows: [
                'Applicant' => e($user->name).' &middot; '.e($user->email),
                'Document' => e($submission->document_label),
                'Submitted' => $submission->created_at->format('d M Y, H:i'),
            ],
            ctaUrl: route('admin.trading.kyc.index'),
            ctaLabel: 'Review verifications',
            accent: '#2563eb',
        ));
    }

    private function safeMail(array $to, LiveAccountNotice $mail): void
    {
        $to = array_filter($to);
        if (empty($to)) {
            return;
        }
        try {
            Mail::to($to)->queue($mail);
        } catch (\Throwable $e) {
            Log::warning('KYC email failed to queue: '.$e->getMessage());
        }
    }
}
