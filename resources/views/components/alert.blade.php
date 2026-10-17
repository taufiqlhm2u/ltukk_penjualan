@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Flux.toast({
                heading: 'Berhasil',
                text: @js(session('success')),
                variant: 'success'
            })
        })
    </script>
@endif
@if (session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Flux.toast({
                heading: 'Peringatan',
                text: @js(session('warning')),
                variant: 'warning'
            })
        })
    </script>
@endif
@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Flux.toast({
                heading: 'Error',
                text: @js(session('error')),
                variant: 'danger'
            })
        })
    </script>
@endif
