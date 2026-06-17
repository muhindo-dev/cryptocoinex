@extends('layouts.public')
@section('title', 'Privacy Policy')
@section('desc', 'How Cryptocoinex, a practice trading simulator, handles your information.')

@section('content')
<div class="wrap">
  <article class="legal">
    <div class="eyebrow">Legal</div>
    <h1>Privacy Policy</h1>
    <div class="updated">Last updated {{ date('F Y') }}</div>

    <div class="note">
      <b>Cryptocoinex is an educational practice simulator.</b> We do not process payments, hold funds,
      or provide any financial service. All trading uses virtual “USD” with no real-world value.
    </div>

    <p>This policy explains what information we collect when you use Cryptocoinex and how we use it.
      By creating an account or using the platform, you agree to the practices described here.</p>

    <h2>1. Information we collect</h2>
    <ul>
      <li><strong>Account details</strong> — your name, email address and password (stored encrypted) when you register.</li>
      <li><strong>Profile information</strong> — optional details you choose to add, such as country, timezone and avatar.</li>
      <li><strong>Activity data</strong> — your simulated trades, virtual balance, lesson progress, achievements and leaderboard standings.</li>
      <li><strong>Technical data</strong> — basic information such as your device type and last-active time, used to keep the service running.</li>
    </ul>

    <h2>2. How we use your information</h2>
    <ul>
      <li>To create and secure your account and personalise your experience.</li>
      <li>To run the simulator — track your virtual balance, trades, progress and rankings.</li>
      <li>To send service-related messages (for example, account or password emails) if you request them.</li>
      <li>To improve and safeguard the platform.</li>
    </ul>

    <h2>3. No real money, no financial data</h2>
    <p>Cryptocoinex never collects card numbers, bank details or any payment information, because there are
      no deposits, withdrawals or charges of any kind. Everything on the platform is for learning and practice.</p>

    <h2>4. Cookies &amp; local storage</h2>
    <p>We use a session cookie to keep you signed in and local storage to remember preferences such as your
      theme and sound settings. These are essential to how the app works and are not used for advertising.</p>

    <h2>5. Data sharing</h2>
    <p>We do not sell your personal information. Aggregated, non-identifying activity (such as your display
      name on a public leaderboard) may be visible to other users. We may disclose information if required by law.</p>

    <h2>6. Your choices</h2>
    <ul>
      <li>You can edit your profile and preferences at any time from your account.</li>
      <li>You can fully reset your account — permanently deleting all your trades, ledger and stats — from the wallet page.</li>
      <li>To delete your account entirely, contact us and we'll remove it.</li>
    </ul>

    <h2>7. Children</h2>
    <p>Cryptocoinex is intended for users who can lawfully form a binding agreement. It is not directed at children.</p>

    <h2>8. Changes</h2>
    <p>We may update this policy from time to time. Material changes will be reflected on this page with a new “last updated” date.</p>

    <h2>9. Contact</h2>
    <p>Questions about this policy? Reach us through the in-app help section. See also our
      <a href="{{ route('terms') }}">Terms of Service</a>.</p>
  </article>
</div>
@endsection
