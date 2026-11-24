<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he']) ? 'rtl' : 'ltr' }}"
>
    <head>
        <meta charset="utf-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >

        <meta
            name="robots"
            content="noindex"
        >

        <title>@lang("tenancy::app.errors.$reason.title")</title>

        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
                background: #f8fafc;
                color: #1f2937;
            }

            main {
                max-width: 32rem;
                padding: 2rem;
                text-align: center;
            }

            h1 {
                margin: 0 0 0.75rem;
                font-size: 1.5rem;
            }

            p {
                margin: 0;
                line-height: 1.6;
                color: #4b5563;
            }
        </style>
    </head>

    <body>
        <main>
            <h1>@lang("tenancy::app.errors.$reason.title")</h1>

            <p>@lang("tenancy::app.errors.$reason.message")</p>
        </main>
    </body>
</html>
