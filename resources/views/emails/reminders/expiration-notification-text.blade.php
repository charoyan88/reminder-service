{{ $isPreExpiration ? __('reminders.pre_expiration_subject', ['order_type' => $orderType]) : __('reminders.post_expiration_subject', ['order_type' => $orderType]) }}

@if($isPreExpiration)
{{ __('reminders.pre_expiration_intro', [
    'order_type' => $orderType,
    'business_name' => $businessName,
    'expiration_date' => $expirationDate,
    'interval' => $interval
]) }}
@else
{{ __('reminders.post_expiration_intro', [
    'order_type' => $orderType,
    'business_name' => $businessName,
    'expiration_date' => $expirationDate,
    'interval' => $interval
]) }}
@endif

{{ __('reminders.action_required') }}
{{ __('reminders.please_renew') }}

{{ __('reminders.renew_now') }}: {{ $renewUrl }}

{{ __('reminders.contact_us') }}

{{ __('reminders.thank_you', ['business_name' => $businessName]) }}

--
{{ __('reminders.expiration_notice') }} | {{ $businessName }} 