<?php

namespace App\Services\LineupGenerator;

/**
 * Result class for player assignment attempts
 */
class AssignmentResult
{
    private bool $successful;
    private QuarterAssignmentData $quarterData;

    public function __construct(bool $successful, QuarterAssignmentData $quarterData)
    {
        $this->successful = $successful;
        $this->quarterData = $quarterData;
    }

    public static function success(QuarterAssignmentData $quarterData): self
    {
        return new self(true, $quarterData);
    }

    public static function failure(QuarterAssignmentData $quarterData): self
    {
        return new self(false, $quarterData);
    }

    public function wasSuccessful(): bool
    {
        return $this->successful;
    }

    public function getQuarterData(): QuarterAssignmentData
    {
        return $this->quarterData;
    }
}