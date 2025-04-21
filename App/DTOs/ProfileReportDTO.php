<?php

namespace App\DTOs;

use App\DTOs\ProfileDTO;
use App\DTOs\MessageDTO;

class ProfileReportDTO {
    public int $id;
    public ProfileDTO $reporterProfileDTO;
    public int $reportedProfileId;
    public string $reasonType;
    public ?string $description;
    public string $status;
    public string $createdAt;
    public string $updatedAt;

    /**
     * Constructor for ProfileReportDTO
     * 
     * @param int $id
     * @param ProfileDTO $reporterProfileDTO
     * @param int $reportedProfileId
     * @param string $reasonType
     * @param string|null $description
     * @param string $status
     * @param string|null $adminNotes
     * @param string $createdAt
     * @param string $updatedAt
     */
    public function __construct(
        int $id,
        ProfileDTO $reporterProfileDTO,
        int $reportedProfileId,
        string $reasonType,
        ?string $description,
        string $status,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->reporterProfileDTO = $reporterProfileDTO;
        $this->reportedProfileId = $reportedProfileId;
        $this->reasonType = $reasonType;
        $this->description = $description;
        $this->status = $status;
        $this->createdAt = MessageDTO::formatTimestamp($createdAt);
        $this->updatedAt = $updatedAt;
    }
}
