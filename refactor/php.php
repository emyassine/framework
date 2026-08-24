<?php
// 1. Define the timezone and the user's language (locale)

$timezone = new DateTimeZone('Africa/Casablanca');
$locale = 'ar_AR'; // French

// 2. Create the IntlDateFormatter
// We use a custom pattern 'vvvv' to get the full localized timezone name

$formatter = new IntlDateFormatter(
    $locale,
    IntlDateFormatter::NONE,
    IntlDateFormatter::NONE,
    $timezone
);
$formatter->setPattern('vvvv');

// 3. Format a date to see the translation
echo '             ' .$formatter->format(new DateTime());
// Outputs: "heure du Maroc" (or "Morocco Time" if locale was 'en_US')
