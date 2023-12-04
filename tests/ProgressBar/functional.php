<?php

use RayanLevert\Cli\ProgressBar;
use RayanLevert\Cli\Style\Foreground;

print "\nProgress bar - max 10 step 1";

$oProgressBar = new ProgressBar(10);
$oProgressBar->start();

foreach (range(1, 10) as $step) {
    $oProgressBar->advance(1);

    echo ob_get_clean();

    usleep(200000);
}

print "\nProgress bar - max 47 step 2 with decimal percentage";

$oProgressBar = new ProgressBar(47);
$oProgressBar->start();

foreach (range(1, 47, 2) as $step) {
    $oProgressBar->advance(2);

    echo ob_get_clean();

    usleep(200000);
}

print "\n\nProgress bar - max 10 step 1 with title";

$oProgressBar = new ProgressBar(10);
$oProgressBar->setTitle('Barre de progrès')->start();

foreach (range(1, 10, 2) as $step) {
    $oProgressBar->advance(2);

    echo ob_get_clean();

    usleep(400000);
}

print "\n\nProgress bar - max 10 step 2";

$oProgressBar = new ProgressBar(10);
$oProgressBar->setTitle('Title 2')->start();

foreach (range(1, 10, 2) as $step) {
    $oProgressBar->advance(2);

    echo ob_get_clean();

    usleep(400000);
}

print "\n\nProgress bar - max 10 step 3";

$oProgressBar = new ProgressBar(10);
$oProgressBar->setTitle('Title 3', Foreground::LIGHT_GREEN)->start();

foreach (range(1, 12, 3) as $step) {
    $oProgressBar->advance(3);

    echo ob_get_clean();

    usleep(400000);
}

print "\n\nProgress bar - max 100 step 1";

$oProgressBar = new ProgressBar(100);
$oProgressBar->start();

foreach (range(1, 100) as $step) {
    $oProgressBar->advance(1);

    echo ob_get_clean();

    usleep(5000);
}

print "\n\nProgress bar - max 50 step 10 until 30 then finish";

$oProgressBar = new ProgressBar(50);
$oProgressBar->start();

foreach (range(1, 30, 10) as $step) {
    $oProgressBar->advance(10);

    echo ob_get_clean();

    usleep(200000);
}
$oProgressBar->finish();

print "\n\nProgress bar - start a new one (3 in total) in a while";

$oProgressBar = new ProgressBar(100);

foreach (range(1, 3) as $range) {
    if ($range === 1) {
        $oProgressBar->setTitle("Barre n°$range", Foreground::GREEN);
    } elseif ($range === 2) {
        $oProgressBar->setTitle("Barre n°$range plus grande", Foreground::RED);
    } else {
        $oProgressBar->setTitle("Barre", Foreground::LIGHT_PURPLE);
    }

    $oProgressBar->start();

    foreach (range(1, 100, 10) as $step) {
        $oProgressBar->advance(10);

        echo ob_get_clean();

        usleep(50000);
    }

    usleep(200000);
}

print "\n\nProgress bar - changing title in a while";

$oProgressBar = new ProgressBar(100);
$oProgressBar->start();

foreach (range(1, 100) as $range) {
    if ($range >= 25 && $range <= 50) {
        $oProgressBar->setTitle("Barre de 25 à 50", Foreground::GREEN);
    } elseif ($range >= 50 && $range <= 75) {
        $oProgressBar->setTitle("Barre entre 50 et 75", Foreground::RED);
    } else {
        $oProgressBar->setTitle("Barre", Foreground::LIGHT_PURPLE);
    }

    $oProgressBar->advance(1);

    echo ob_get_clean();

    usleep(50000);
}

print "\n\nProgress bar - changing max in a while";

$oProgressBar = new ProgressBar(100);

foreach (range(1, 3) as $range) {
    if ($range === 1) {
        $max = 15;
    } elseif ($range === 2) {
        $max = 30;
    } else {
        $max = 50;
    }

    $oProgressBar->setMax($max, 30)->setTitle("Max $max")->start();

    foreach (range(1, $max) as $range) {
        $oProgressBar->advance();

        echo ob_get_clean();

        usleep(50000);
    }
}

print "\n\n";
