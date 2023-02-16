<?php

use DisDev\Cli\ProgressBar;

print "\nProgress bar - max 10 step 1\n";

$oProgressBar = new ProgressBar(10);
$oProgressBar->start();

foreach (range(1, 10) as $step) {
    $oProgressBar->advance(1);

    echo ob_get_clean();

    usleep(200000);
}

print "\n\nProgress bar - max 10 step 1 with title\n";

$oProgressBar = new ProgressBar(10);
$oProgressBar->start('Barre de progrès');

foreach (range(1, 10, 2) as $step) {
    $oProgressBar->advance(2);

    echo ob_get_clean();

    usleep(400000);
}

print "\n\nProgress bar - max 10 step 2\n";

$oProgressBar = new ProgressBar(10);
$oProgressBar->start();

foreach (range(1, 10, 2) as $step) {
    $oProgressBar->advance(2);

    echo ob_get_clean();

    usleep(400000);
}

print "\n\nProgress bar - max 10 step 3\n";

$oProgressBar = new ProgressBar(10);
$oProgressBar->start();

foreach (range(1, 12, 3) as $step) {
    $oProgressBar->advance(3);

    echo ob_get_clean();

    usleep(400000);
}

print "\n\nProgress bar - max 100 step 1\n";

$oProgressBar = new ProgressBar(100);
$oProgressBar->start();

foreach (range(1, 100) as $step) {
    $oProgressBar->advance(1);

    echo ob_get_clean();

    usleep(50000);
}

print "\n\nProgress bar - max 50 step 10 until 30 then finish\n";

$oProgressBar = new ProgressBar(50);
$oProgressBar->start();

foreach (range(1, 30, 10) as $step) {
    $oProgressBar->advance(10);

    echo ob_get_clean();

    usleep(500000);
}
$oProgressBar->finish();
