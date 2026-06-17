@include('emails._header')

  <p style="font-size:15px;color:#1A0F07;font-weight:700;margin:0 0 6px;">{{ $heading }}</p>
  <p style="font-size:14px;color:#5A4A3A;line-height:1.7;margin:0 0 22px;">{!! nl2br(e($intro)) !!}</p>

  @if(!empty($rows))
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
         style="background:#FDF9F5;border:1.5px solid #E8DDD4;border-radius:8px;margin-bottom:22px;">
    @foreach($rows as $label => $value)
    <tr>
      <td style="padding:11px 18px;border-bottom:{{ $loop->last ? 'none' : '1px solid #EFE6DC' }};font-size:12px;color:#9A8A7A;text-transform:uppercase;letter-spacing:.04em;width:42%;">{{ $label }}</td>
      <td style="padding:11px 18px;border-bottom:{{ $loop->last ? 'none' : '1px solid #EFE6DC' }};font-size:14px;color:#1A0F07;font-weight:700;text-align:right;">{!! $value !!}</td>
    </tr>
    @endforeach
  </table>
  @endif

  @isset($ctaUrl)
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:22px;">
    <tr><td align="center">
      <a href="{{ $ctaUrl }}"
         style="display:inline-block;background:{{ $accent ?? '#5D3A1A' }};color:#ffffff;font-size:14px;font-weight:700;
                letter-spacing:.04em;padding:13px 36px;border-radius:7px;text-decoration:none;">{{ $ctaLabel ?? 'View' }}</a>
    </td></tr>
  </table>
  @endisset

  @isset($noteBody)
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
         style="background:#FFF8F0;border:1.5px solid #F0D8B8;border-radius:7px;">
    <tr><td style="padding:14px 18px;">
      @isset($noteTitle)<p style="font-size:12px;font-weight:700;color:#8B4513;margin:0 0 5px;">{{ $noteTitle }}</p>@endisset
      <p style="font-size:12.5px;color:#7A5A3A;line-height:1.7;margin:0;">{!! nl2br(e($noteBody)) !!}</p>
    </td></tr>
  </table>
  @endisset

@include('emails._footer')
