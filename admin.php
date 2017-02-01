<?php
/**
 * Yeah, This is an easter egg. You can safely remove this file... or not =O
 * Inspired in https://stackoverflow/admin.php
 *
 * @package Advandz
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

$videos = [
    '9UZbGgXvCCA',
    'wbby9coDRCk',
    'VnnWp_akOrE',
    'eh7lp9umG2I',
    'fx2Z5ZD_Rbo',
    'MJbTjBLEKBU',
    'z9Uz1icjwrM',
    'jI-kpVh6e1U',
    'Sagg08DrO5U',
    'KMFOVSWn0mI',
    '8ZcmTl_1ER8',
    'sCNrK-n68CM',
    'rTlAmiLKX_U',
    'eBPfnj8_4W4',
    'kxopViU98Xo',
    '51skM6WA-dg',
    'nIzAQB_pe9U',
    '66tQR7koR_Q',
    'UcRtFYAz2Yo',
    'zEaxNfkLUUQ',
    '3-GsefjiKtE',
    '3-GsefjiKtE',
    'mPBzujm-cF8',
    'VWurRZ_Aj5g',
    'NDcj8Ub3SV8',
    'bD2XyTivpWU',
    'hGlyFc79BUE',
    'OWFBqiUgspg',
    'WxXV9P0Lj30',
    'OziwuRfGoLI',
    'ZL-0HmjXfU8',
    'PCv6DS1yQbk'
];

$video = $videos[rand(0, (count($videos) - 1))];

header('Location: https://www.youtube.com/watch?v=' . $video);