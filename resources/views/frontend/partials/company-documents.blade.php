@php
    // Params: $documents (list), $downloadUrl (fn $doc => url), $deleteUrl (fn $doc => url)|null,
    //         $uploadUrl (string)|null
    $documents = \App\Support\Rows::from($documents ?? []);
    $typeLabels = [
        'commercial_register' => __('Commercial register'),
        'tax_card' => __('Tax card'),
        'license' => __('License'),
        'other' => __('Other'),
    ];
    $fmtSize = function ($bytes) {
        $bytes = (int) $bytes;
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1) . ' MB' : number_format(max($bytes, 1) / 1024, 1) . ' KB';
    };
@endphp

@if (count($documents))
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3 text-start">{{ __('Document') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Type') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Size') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Uploaded') }}</th>
            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
        </x-slot:head>
        @foreach ($documents as $doc)
            <tr>
                <td class="px-4 py-3">
                    <p class="font-semibold text-hb-navy">{{ data_get($doc, 'title') ?: data_get($doc, 'original_filename') }}</p>
                    @if (data_get($doc, 'title'))<p class="text-xs text-hb-muted">{{ data_get($doc, 'original_filename') }}</p>@endif
                </td>
                <td class="px-4 py-3">{{ $typeLabels[data_get($doc, 'type', 'other')] ?? data_get($doc, 'type') }}</td>
                <td class="px-4 py-3">{{ $fmtSize(data_get($doc, 'size_bytes', 0)) }}</td>
                <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($doc, 'created_at'), 'Y-m-d') }}</td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap justify-end gap-2">
                        <x-button variant="light" size="sm" :href="$downloadUrl($doc)">{{ __('Download') }}</x-button>
                        @if (!empty($deleteUrl))
                            <form method="POST" action="{{ $deleteUrl($doc) }}" class="inline" onsubmit="return confirm('{{ __('Delete this document?') }}')">
                                @csrf @method('DELETE')
                                <x-button type="submit" size="sm" variant="destructive">{{ __('Delete') }}</x-button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
@else
    <p class="text-sm text-hb-muted">{{ __('No documents uploaded yet.') }}</p>
@endif

@if (!empty($uploadUrl))
    <form method="POST" action="{{ $uploadUrl }}" enctype="multipart/form-data" class="mt-6 space-y-4 border-t border-hb-border pt-5">
        @csrf
        <h3 class="text-sm font-bold text-hb-navy">{{ __('Upload a document') }}</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.select name="type" :label="__('Type')">
                @foreach ($typeLabels as $value => $label)
                    <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="title" :label="__('Title (optional)')" type="text" value="{{ old('title') }}" />
        </div>
        <x-form.file-upload name="file" :label="__('File')" :required="true" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" :error="$errors->first('file')" :hint="__('PDF, JPG, PNG, DOC or DOCX — up to 10 MB.')" />
        <x-button type="submit">{{ __('Upload') }}</x-button>
    </form>
@endif
