@php
    $formName ??= 'website_application';
    $source ??= 'website';
    $tracking = $tracking ?? [];
@endphp

<input type="hidden" name="source" value="{{ old('source', $tracking['source'] ?? $source) }}">
<input type="hidden" name="form_name" value="{{ old('form_name', $tracking['form_name'] ?? $formName) }}">
<input type="hidden" name="form_page" value="{{ old('form_page', url()->current()) }}">
<input type="hidden" name="utm_source" value="{{ old('utm_source', $tracking['utm_source'] ?? '') }}">
<input type="hidden" name="utm_medium" value="{{ old('utm_medium', $tracking['utm_medium'] ?? '') }}">
<input type="hidden" name="utm_campaign" value="{{ old('utm_campaign', $tracking['utm_campaign'] ?? '') }}">
<input type="hidden" name="utm_term" value="{{ old('utm_term', $tracking['utm_term'] ?? '') }}">
<input type="hidden" name="utm_content" value="{{ old('utm_content', $tracking['utm_content'] ?? '') }}">
<input type="hidden" name="referrer_url" value="{{ old('referrer_url', $tracking['referrer_url'] ?? '') }}">
<input type="hidden" name="landing_page" value="{{ old('landing_page', $tracking['landing_page'] ?? '') }}">
<input type="hidden" name="locale" value="{{ old('locale', app()->getLocale()) }}">
