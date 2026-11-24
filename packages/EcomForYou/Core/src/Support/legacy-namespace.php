<?php

/**
 * Bridge the vendor's former namespace and EcomForYou in both directions. A class asked for under `Webkul\` is
 * aliased to the same class under `EcomForYou\`, so third-party packages such as `bagisto/laravel-datafaker` and
 * older Bagisto extensions keep working. A class asked for under one of the namespaces a third-party package still
 * declares under `Webkul\`, written with the `EcomForYou\` prefix, is aliased to that package's class.
 */
spl_autoload_register(static function (string $class): void {
    $legacy = 'Webkul\\';

    $current = 'EcomForYou\\';

    $thirdParty = ['Faker\\'];

    if (str_starts_with($class, $legacy)) {
        $target = $current.substr($class, strlen($legacy));
    } elseif (
        str_starts_with($class, $current)
        && array_filter($thirdParty, fn (string $namespace) => str_starts_with(substr($class, strlen($current)), $namespace))
    ) {
        $target = $legacy.substr($class, strlen($current));
    } else {
        return;
    }

    if (
        class_exists($target)
        || interface_exists($target)
        || trait_exists($target)
        || enum_exists($target)
    ) {
        class_alias($target, $class);
    }
});
