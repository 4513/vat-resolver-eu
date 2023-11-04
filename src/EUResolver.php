<?php

declare(strict_types=1);

namespace MiBo\VAT\VATResolvers;

use Carbon\Carbon;
use CompileError;
use DateTimeInterface;
use DomainException;
use MiBo\Taxonomy\Contracts\ClassificationOfProductsByActivity;
use MiBo\Taxonomy\Contracts\ProductTaxonomy;
use MiBo\VAT\Contracts\VATResolver;
use MiBo\VAT\Enums\VATRate;
use MiBo\VAT\Exceptions\CouldNotRetrieveVATInformationException;
use MiBo\VAT\VAT;
use Stringable;

/**
 * Class EUResolver
 *
 * @package MiBo\VAT\VATResolvers
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.0
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class EUResolver implements VATResolver
{
    /** @var array<non-empty-string, array<int, array{non-empty-string: \MiBo\VAT\Enums\VATRate}>> */
    private array $resources;

    public function __construct()
    {
        $this->resources['CZE'] = require __DIR__ . '/../resources/cze.php';
    }

    /**
     * @inheritDoc
     */
    public function retrieveVAT(
        ProductTaxonomy $classification,
        Stringable|string $countryCode,
        ?DateTimeInterface $date
    ): VAT
    {
        // Unsupported classification.
        if (!$classification instanceof ClassificationOfProductsByActivity) {
            throw new CouldNotRetrieveVATInformationException(
                (string) $countryCode,
                $date ?? Carbon::now(),
                new DomainException('Classification must be of type CPA.')
            );
        }

        if (!key_exists((string) $countryCode, $this->resources)) {
            throw new CouldNotRetrieveVATInformationException(
                (string) $countryCode,
                $date ?? Carbon::now(),
                new DomainException('Country code is not supported.')
            );
        }

        $resource  = $this->resources[(string) $countryCode];
        $date    ??= Carbon::now();
        $timestamp = $date->getTimestamp();

        foreach ($resource as $validSince => $classificationData) {
            if ($timestamp < $validSince) {
                continue;
            }

            return VAT::get(
                $countryCode,
                $this->findVAT($classification, $classificationData),
                $classification,
                $date
            );
        }

        throw new CouldNotRetrieveVATInformationException((string) $countryCode, $date);
    }

    /**
     * Changes all the resources.
     *
     * @param array<non-empty-string, array<int, array{non-empty-string: \MiBo\VAT\Enums\VATRate}>> $resources
     *
     * @return void
     */
    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    /**
     * Adds a resource for provided country.
     *
     * @param non-empty-string $countryCode
     * @param array<int, array{non-empty-string: \MiBo\VAT\Enums\VATRate}> $resource
     *
     * @return void
     */
    public function addResource(string $countryCode, array $resource): void
    {
        $this->resources[$countryCode] = $resource;
    }

    /**
     * Tries to find the VAT rate for the given classification.
     *
     *  The method uses the most specified classification available. If not applicable, uses the less
     * specified and continues until the most general classification is reached. If none of the
     * classifications is found, returns the standard VAT rate.
     *
     * @param \MiBo\Taxonomy\Contracts\ClassificationOfProductsByActivity $classification
     * @param array<non-empty-string, \MiBo\VAT\Enums\VATRate> $data
     *
     * @return \MiBo\VAT\Enums\VATRate
     *
     * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
     */
    private function findVAT(ClassificationOfProductsByActivity $classification, array $data): VATRate
    {
        // @phpcs:disable SlevomatCodingStandard.Exceptions.DisallowNonCapturingCatch.DisallowedNonCapturingCatch
        try {
            $id = $classification->getSubcategory();

            if (key_exists($id, $data)) {
                return $data[$id];
            }
        } catch (CompileError) {}

        try {
            $id = $classification->getCategory();

            if (key_exists($id, $data)) {
                return $data[$id];
            }
        } catch (CompileError) {}

        try {
            $id = $classification->getClass();

            if (key_exists($id, $data)) {
                return $data[$id];
            }
        } catch (CompileError) {}

        try {
            $id = $classification->getGroup();

            if (key_exists($id, $data)) {
                return $data[$id];
            }
        } catch (CompileError) {}

        try {
            $id = $classification->getDivision();

            if (key_exists($id, $data)) {
                return $data[$id];
            }
        } catch (CompileError) {}

        // @phpcs:enable SlevomatCodingStandard.Exceptions.DisallowNonCapturingCatch.DisallowedNonCapturingCatch

        $id = $classification->getSection();

        if (key_exists($id, $data)) {
            return $data[$id];
        }

        return VATRate::STANDARD;
    }
}
