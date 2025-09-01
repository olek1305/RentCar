<script>
    document.addEventListener('DOMContentLoaded', function() {
        const consent = localStorage.getItem('cookie_consent');
        if (consent) {
            const parsed = JSON.parse(consent);
            if (parsed['{{ $type }}']) {
                @if($src)
                const script = document.createElement('script');
                script.src = '{{ $src }}';
                script.async = true;
                document.head.appendChild(script);
                @else
                    @if($slot)
                    {!! $slot !!}
                    @endif
                @endif
            }
        }
    });
</script>
