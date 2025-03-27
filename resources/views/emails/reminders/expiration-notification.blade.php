<x-mail::message>
# {{ $isPreExpiration ? __('reminders.pre_expiration_subject', ['order_type' => $orderType]) : __('reminders.post_expiration_subject', ['order_type' => $orderType]) }}

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

<x-mail::panel>
## {{ __('reminders.action_required') }}
{{ __('reminders.please_renew') }}
</x-mail::panel>

<x-mail::button :url="$renewUrl">
{{ __('reminders.renew_now') }}
</x-mail::button>

{{ __('reminders.contact_us') }}

{{ __('reminders.thank_you', ['business_name' => $businessName]) }}

<x-slot:footer>
{{ __('reminders.expiration_notice') }} | {{ $businessName }}
</x-slot:footer>
</x-mail::message> 