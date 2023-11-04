<?php

declare(strict_types=1);

use Carbon\Carbon;
use MiBo\Taxonomy\CPA;
use MiBo\VAT\Enums\VATRate;

/** @phpstan-var array<int, array{non-empty-string: \MiBo\VAT\Enums\VATRate}> */
return [
    Carbon::create(2023, Carbon::JULY)->getTimestamp() => [
        CPA::E36002->name  => VATRate::SECOND_REDUCED,
        CPA::E37->name     => VATRate::SECOND_REDUCED,
        CPA::E381->name    => VATRate::REDUCED,
        CPA::E382->name    => VATRate::REDUCED,
        CPA::E383->name    => VATRate::REDUCED,
        CPA::F41001->name  => VATRate::REDUCED,
        CPA::F41003->name  => VATRate::REDUCED,
        CPA::F43->name     => VATRate::REDUCED,
        CPA::H49->name     => VATRate::NONE,
        CPA::H50->name     => VATRate::NONE,
        CPA::H51->name     => VATRate::NONE,
        CPA::I55->name     => VATRate::SECOND_REDUCED,
        CPA::I56->name     => VATRate::SECOND_REDUCED,
        CPA::J5914->name   => VATRate::SECOND_REDUCED,
        CPA::N77->name     => VATRate::SECOND_REDUCED,
        CPA::N81211->name  => VATRate::SECOND_REDUCED,
        CPA::N812211->name => VATRate::SECOND_REDUCED,
        CPA::P85->name     => VATRate::SECOND_REDUCED,
        CPA::Q86->name     => VATRate::SECOND_REDUCED,
        CPA::Q87->name     => VATRate::SECOND_REDUCED,
        CPA::Q8810->name   => VATRate::SECOND_REDUCED,
        CPA::Q8891->name   => VATRate::SECOND_REDUCED,
        CPA::R90->name     => VATRate::SECOND_REDUCED,
        CPA::R91->name     => VATRate::SECOND_REDUCED,
        CPA::R93->name     => VATRate::SECOND_REDUCED,
        CPA::R9311->name   => VATRate::SECOND_REDUCED,
        CPA::R9312->name   => VATRate::SECOND_REDUCED,
        CPA::S9523->name   => VATRate::SECOND_REDUCED,
        CPA::S952911->name => VATRate::SECOND_REDUCED,
        CPA::S9602->name   => VATRate::SECOND_REDUCED,
        CPA::S9603->name   => VATRate::REDUCED,
        CPA::S->name       => VATRate::STANDARD,
    ],
];
