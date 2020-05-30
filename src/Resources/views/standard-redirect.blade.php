<?php $cieloStardard = app('Extras\Cielo\Payment\Standard') ?>

<body data-gr-c-s-loaded="true" cz-shortcut-listen="true">
    You will be redirected to the Cielo website in a few seconds.

    <form action="{{ $cieloStardard->getCieloUrl() }}" id="cielo_standard_checkout" method="POST">
        <input value="Click here if you are not redirected within 10 seconds..." type="submit">

        @foreach ($cieloStardard->getFormFields() as $name => $value)

        <input type="hidden" name="{{ $name }}" value="{{ $value }}">

        @endforeach
    </form>

    <script type="text/javascript">
        document.getElementById("cielo_standard_checkout").submit();
    </script>
</body>