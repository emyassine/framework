<?php declare(strict_types=1);

$composers = glob('x-webkernel/*/composer.json');

foreach ($composers as $composer) {
    $data = json_decode(file_get_contents($composer), true);
    echo $data['name'] . PHP_EOL;
}

/*
$timezone = new DateTimeZone('Africa/Casablanca');
$locale   = 'he_HE';

$formatter = new IntlDateFormatter(
    $locale,
    IntlDateFormatter::NONE,
    IntlDateFormatter::NONE,
    $timezone
);
$formatter->setPattern('vvvv');

// Wrap with RTL markers
$rtl_start = "\u{202B}"; // RTL embedding
$rtl_end   = "\u{202C}"; // end embedding

echo $rtl_start . $formatter->format(new DateTime()) . $rtl_end . PHP_EOL;
 */
