<?php

namespace Models;

use Core\Model;
use DTOs\ProfileReportDTO;

class ProfileReport extends Model
{
    public function __construct($logFile = "profile_reports.log")
    {
        parent::__construct($logFile);
    }

    /**
     * Create a new report for a profile
     * 
     * @param int $reporterProfileId The ID of the profile reporting
     * @param int $reportedProfileId The ID of the reported profile
     * @param string $reasonType The reason for the report ('spam', 'harassment', 'inappropriate_content')
     * @param string|null $description The description provided by the reporter
     * @return int|false The ID of the new report or false if creation failed
     */
    public function createReport(
        int $reporterProfileId,
        int $reportedProfileId,
        string $reasonType,
        ?string $description = null
    ): array {
        try {
            $this->logger->debug("Models/ProfileReport->createReport(): Starting report creation for reporter profile $reporterProfileId");

            // Insert the report
            $sql = "
                INSERT INTO PROFILE_REPORTS (
                    reporter_profile_id,
                    reported_profile_id,
                    reason_type,
                    description
                ) VALUES (
                    :reporter_profile_id,
                    :reported_profile_id,
                    :reason_type,
                    :description
                )
            ";

            $this->db->query($sql);
            $this->db->bind(':reporter_profile_id', $reporterProfileId);
            $this->db->bind(':reported_profile_id', $reportedProfileId);
            $this->db->bind(':reason_type', $reasonType);
            $this->db->bind(':description', $description);

            if (!$this->db->execute()) {
                $this->logger->error("Models/ProfileReport->createReport(): Failed to insert report");
                return ['success' => false];
            }

            $reportId = $this->db->getLastInsertId();
            $this->logger->debug("Models/ProfileReport->createReport(): Created report with ID: $reportId");

            return ['success' => true,
                'reportId' => $reportId
            ];
        } catch (\Exception $e) {
            $this->logger->error("Models/ProfileReport->createReport(): Exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all available reason type options for profile reports
     * @return array Array of available reason type options
     */
    public function getReportOptions(): array {
        try{
            $sql = "
            SELECT SUBSTRING(COLUMN_TYPE, 6, LENGTH(COLUMN_TYPE) - 6) AS enum_values
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '" . DB_NAME . "'
            AND TABLE_NAME = 'PROFILE_REPORTS'
            AND COLUMN_NAME = 'reason_type'
            ";
            
            $this->db->query($sql);
            $result = $this->db->single();
            
            if (!$result) {
                $this->logger->error("Models/ProfileReport->getReportOptions(): Failed to retrieve reason type options");
                return [];
            }
            
            // The result will be in format: 'spam','harassment','inappropriate_content'
            $enumString = $result->enum_values;
            
            // Remove the quotes and split by comma
            $options = array_map(function($value) {
                return trim($value, "'");
            }, explode(',', $enumString));
            
            $this->logger->debug("Models/ProfileReport->getReportOptions(): Retrieved options: " . implode(', ', $options));
            
            return $options;
        }catch (\Exception $e) {
            $this->logger->error("Models/ProfileReport->getReportOptions(): " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all reports made by a specific profile
     * 
     * @param int $profileId The ID of the profile whose reports are being retrieved
     * @return array Array of ProfileReportDTO objects
     */
    public function getReportsByProfile(int $profileId): array
    {
        $sql = "
            SELECT
                r.id as report_id,
                r.reporter_profile_id,
                r.reported_profile_id,
                r.reason_type,
                r.description,
                r.status,
                r.admin_notes,
                r.created_at,
                r.updated_at,
                reported.full_name as reported_full_name,
                reported.profile_picture as reported_profile_picture
            FROM PROFILE_REPORTS r
            JOIN PROFILES reported ON r.reported_profile_id = reported.id
            WHERE r.reporter_profile_id = :profile_id
            ORDER BY r.created_at DESC
        ";

        $this->db->query($sql);
        $this->db->bind(':profile_id', $profileId);
        $results = $this->db->resultSetAssoc();

        $reports = [];

        foreach ($results as $row) {
            $this->logger->debug("Models/ProfileReport->getReportsByProfile($profileId): row: " . $row['report_id']);

            // Create ProfileReportDTO
            $reportDto = new ProfileReportDTO(
                $row['report_id'],
                $row['reporter_profile_id'],
                $row['reported_profile_id'],
                $row['reason_type'],
                $row['description'],
                $row['status'],
                $row['admin_notes'],
                $row['created_at'],
                $row['updated_at']
            );

            $reports[] = $reportDto;
        }

        return $reports;
    }

    /**
     * Get all reports related to a specific reported profile
     * 
     * @param int $profileId The ID of the reported profile
     * @return array Array of ProfileReportDTO objects
     */
    public function getReportsForProfile(int $profileId): array
    {
        $sql = "
            SELECT
                r.id as report_id,
                r.reporter_profile_id,
                r.reported_profile_id,
                r.reason_type,
                r.description,
                r.status,
                r.admin_notes,
                r.created_at,
                r.updated_at,
                reporter.full_name as reporter_full_name,
                reporter.profile_picture as reporter_profile_picture
            FROM PROFILE_REPORTS r
            JOIN PROFILES reporter ON r.reporter_profile_id = reporter.id
            WHERE r.reported_profile_id = :profile_id
            ORDER BY r.created_at DESC
        ";

        $this->db->query($sql);
        $this->db->bind(':profile_id', $profileId);
        $results = $this->db->resultSetAssoc();

        $reports = [];

        foreach ($results as $row) {
            $this->logger->debug("Models/ProfileReport->getReportsForProfile($profileId): row: " . $row['report_id']);

            // Create ProfileReportDTO
            $reportDto = new ProfileReportDTO(
                $row['report_id'],
                $row['reporter_profile_id'],
                $row['reported_profile_id'],
                $row['reason_type'],
                $row['description'],
                $row['status'],
                $row['admin_notes'],
                $row['created_at'],
                $row['updated_at']
            );

            $reports[] = $reportDto;
        }

        return $reports;
    }
}
