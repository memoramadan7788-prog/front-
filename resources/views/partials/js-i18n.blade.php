@php
    $jsCatalog = trans('js');
    if (! is_array($jsCatalog)) {
        $jsCatalog = [];
    }
@endphp
<script>
window.__hbLocale = @json(app()->getLocale());
window.__hbI18n = @json($jsCatalog);
window.__ = function (key, replace) {
    var dict = window.__hbI18n || {};
    var parts = String(key || '').split('.');
    var value = dict;
    for (var i = 0; i < parts.length; i++) {
        if (value && typeof value === 'object' && Object.prototype.hasOwnProperty.call(value, parts[i])) {
            value = value[parts[i]];
        } else {
            value = null;
            break;
        }
    }
    if (typeof value !== 'string') {
        value = key;
    }
    if (replace && typeof replace === 'object') {
        Object.keys(replace).forEach(function (token) {
            value = value.replace(new RegExp(':' + token, 'g'), String(replace[token]));
        });
    }
    return value;
};
</script>
