<?php

namespace DTOs;

class ProfileReportDTO {
    private int $id;
    private int $reporterProfileId;
    private int $reportedProfileId;
    private string $reasonType;
    private ?string $description;
    private string $status;
    private ?string $adminNotes;
    private string $createdAt;
    private string $updatedAt;

    /**
     * Constructor for ProfileReportDTO
     * 
     * @param int $id
     * @param int $reporterProfileId
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
        int $reporterProfileId,
        int $reportedProfileId,
        string $reasonType,
        ?string $description,
        string $status,
        ?string $adminNotes,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->reporterProfileId = $reporterProfileId;
        $this->reportedProfileId = $reportedProfileId;
        $this->reasonType = $reasonType;
        $this->description = $description;
        $this->status = $status;
        $this->adminNotes = $adminNotes;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    // Getters

    public function getId(): int {
        return $this->id;
    }

    public function getReporterProfileId(): int {
        return $this->reporterProfileId;
    }

    public function getReportedProfileId(): int {
        return $this->reportedProfileId;
    }

    public function getReasonType(): string {
        return $this->reasonType;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getAdminNotes(): ?string {
        return $this->adminNotes;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string {
        return $this->updatedAt;
    }
}
