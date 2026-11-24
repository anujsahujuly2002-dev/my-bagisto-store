<script>
    (() => {
        const scripts = @json(app(\EcomForYou\Theme\Upload\UploadedPage::class)->current()['scripts'] ?? []);

        const load = (index) => {
            if (index >= scripts.length) {
                return;
            }

            const script = document.createElement('script');

            if (scripts[index].module) {
                script.type = 'module';
            }

            if (scripts[index].src) {
                script.src = scripts[index].src;

                script.onload = script.onerror = () => load(index + 1);

                document.body.appendChild(script);

                return;
            }

            script.text = scripts[index].code ?? '';

            document.body.appendChild(script);

            load(index + 1);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => load(0));
        } else {
            load(0);
        }
    })();
</script>
