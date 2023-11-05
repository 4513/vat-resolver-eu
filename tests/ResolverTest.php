<?php

declare(strict_types=1);

namespace MiBo\VAT\VATResolvers\Tests;

use Carbon\Carbon;
use MiBo\Taxonomy\Contracts\ProductTaxonomy;
use MiBo\Taxonomy\CPA;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Exceptions\CouldNotRetrieveVATInformationException;
use MiBo\VAT\VATResolvers\EUResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class ResolverTest
 *
 * @package MiBo\VAT\VATResolvers\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @coversDefaultClass \MiBo\VAT\VATResolvers\EUResolver
 */
final class ResolverTest extends TestCase
{
    /**
     * @small
     *
     * @covers ::__construct
     * @covers ::retrieveVAT
     * @covers ::setResources
     * @covers ::addResource
     * @covers ::findVAT
     *
     * @param \MiBo\VAT\Enums\VATRate|string $expected
     * @param string $countryCode
     * @param \MiBo\Taxonomy\Contracts\ProductTaxonomy $classification
     * @param \Carbon\Carbon $date
     *
     * @return void
     *
     * @dataProvider getData
     */
    public function testResolver(
        VATRate|string $expected,
        string $countryCode,
        ProductTaxonomy $classification,
        Carbon $date
    ): void
    {
        $resolver = $this->getResolver();

        $resolver->setResources([]);
        $resolver->addResource('CZE', include __DIR__ . '/../resources/cze.php');
        $resolver->addResource('SVK', []);

        if (!$expected instanceof VATRate) {
            $this->expectException($expected);
        }

        $result = $resolver->retrieveVAT($classification, $countryCode, $date);

        $this->assertTrue($expected->equals($result->getRate()));
        $this->assertSame($countryCode, $result->getCountryCode());
        $this->assertSame($classification->getCode(), $result->getClassification()->getCode());
    }

    /**
     * Testing data.
     *
     * @return array<string, array{0: \MiBo\VAT\Enums\VATRate, 1: string, 2: \MiBo\Taxonomy\CPA, 3: \Carbon\Carbon}>
     */
    public static function getData(): array
    {
        return [
            'Normal result' => [
                VATRate::NONE,
                'CZE',
                CPA::H4920,
                Carbon::create(2023, Carbon::AUGUST),
            ],
            'Default result' => [
                VATRate::STANDARD,
                'CZE',
                CPA::A01,
                Carbon::create(2023, Carbon::SEPTEMBER),
            ],
            'Unknown country' => [
                CouldNotRetrieveVATInformationException::class,
                'USA',
                CPA::A01,
                Carbon::create(2023, Carbon::SEPTEMBER),
            ],
            'Unknown date' => [
                CouldNotRetrieveVATInformationException::class,
                'CZE',
                CPA::A01,
                Carbon::create(1940, Carbon::OCTOBER),
            ],
            'Unknown classification' => [
                CouldNotRetrieveVATInformationException::class,
                'CZE',
                // @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                new class implements ProductTaxonomy {
                    public function getCode(): string
                    {
                        return '';
                    }

                    public function is(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public function belongsTo(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public function wraps(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public static function isValid(string $code): bool
                    {
                        return false;
                    }
                },
                // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                Carbon::now(),
            ],
            'Full match' => [
                VATRate::SECOND_REDUCED,
                'CZE',
                CPA::N812211,
                Carbon::create(2023, Carbon::SEPTEMBER),
            ],
            'Category match' => [
                VATRate::SECOND_REDUCED,
                'CZE',
                CPA::N81211,
                Carbon::create(2023, Carbon::SEPTEMBER),
            ],
            'Global classification' => [
                VATRate::STANDARD,
                'CZE',
                CPA::S,
                Carbon::create(2023, Carbon::SEPTEMBER),
            ],
            'Combined classification' => [
                VATRate::COMBINED,
                'CZE',
                // @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                new class implements ProductTaxonomy {
                    public function getCode(): string
                    {
                        return VATRate::COMBINED->name;
                    }

                    public function is(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public function belongsTo(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public function wraps(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public static function isValid(string $code): bool
                    {
                        return false;
                    }
                },
                // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                Carbon::now(),
            ],
            'Any classification' => [
                VATRate::ANY,
                'CZE',
                // @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                new class implements ProductTaxonomy {
                    public function getCode(): string
                    {
                        return VATRate::ANY->name;
                    }

                    public function is(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public function belongsTo(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public function wraps(string|ProductTaxonomy $code): bool
                    {
                        return false;
                    }

                    public static function isValid(string $code): bool
                    {
                        return false;
                    }
                },
                // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
                Carbon::now(),
            ],
        ];
    }

    private function getResolver(): EUResolver
    {
        return new EUResolver();
    }
}
