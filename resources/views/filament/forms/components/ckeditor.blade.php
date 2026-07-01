<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div>
        <textarea name="{{ $getId() }}" id="{{ $getId() }}" rows="10" cols="80">
            {{ $getState() }}
        </textarea>
        <script>
            CKEDITOR.replace( '{{ $getId() }}', {
                language: '{{ app()->getLocale() }}',
                uiColor: '#9AB8F3',
                versionCheck: false
            } );
        </script>
    </div>
</x-dynamic-component>
