<?php
/**
 * PSL/ClipperBundle/Service/RPanelService.php
 */
namespace PSL\ClipperBundle\Service;

use PSL\ClipperBundle\Utils\RPanelProject as RPanelProject;

use \Exception as Exception;
use \stdClass as stdClass;

/**
 * Helper class to communicate with the back end of RPanel
 */
class RPanelService
{
  private $params;
  private $conn;

  function __construct($params)
  {
    $this->params = $params;
  }

  /**
   * set connection to DB
   * @param $connection
   */
  public function setConnection(\Doctrine\DBAL\Connection $connection)
  {
    $this->conn = $connection;
  }

  /**
   * Create a feasibility project and return it.
   *
   * Database Translateapi
   *
   * @param RPanelProject $rp - a RPanel project object
   *
   * @return A string representation of the last inserted ID. (feasibility_project.projid)
   */
  public function createFeasibilityProject(RPanelProject $rp)
  {
    $conn = $this->conn;
    $conn->insert('feasibility_project',
      array(
        'proj_name'  => $rp->getProjName(),               // 'Name of FirstQ project'
        'proj_status' => $rp->getProjStatus(),            // 1
        'created_by' => $rp->getCreatedBy(),              // userid
        'proj_type' => $rp->getProjType(),                // 1
        'expired_date' => $rp->getExpiredDate(),          // estimated completion date
        'created_date' => $rp->getCreatedDate(),          // (now
        'proj_num' => $rp->getProjNum(),                  // clipper order id - fqg.id
      )
    );

    // returned the last inserted auto increment
    // feasibility_project.projid
    return $conn->lastInsertId();
  }

  /**
   * Creates a Feasibility project quota
   *
   * Database Translateapi
   *
   * @param RPanelProject $rp - a RPanel project object
   * @param Object $gs - a Google Sheet object
   *
   * @return A string representation of the last inserted ID. (feasibility_project_quota.quota_id)
   */
  public function createFeasibilityProjectQuota(RPanelProject $rp, $gs)
  {
    $conn = $this->conn;
    $conn->insert('feasibility_project_quota',
      array(
        'proj_id' => $rp->getProjId(),                    // feasibility_project.projid,
        'respondent_req' => $rp->getNumParticipants(),    // [Number of respondents required],
        'specialty_id' => $gs->specialty_id,              // [Specialty ID from MDM],
        'country' => $gs->country_id,                     // [Country ID from MDM],
        'incidence_rate' => $rp->getIncidenceRate(),      // [Incidence Rate, ask Claire, might be 100],
        'length' => $rp->getLength(),                     // [Length of interview, ask Claire, I think it is 5],
        'target_size' => $rp->getTargetSize(),            // 0,
        'target_list' => $rp->getTargetList(),            // 0,
        'feasibility_file' => $rp->getFeasibilityFile(),  // 0,
        'respondent' => $rp->getRespondent(),             // 0,
        'duration' => $rp->getAddedDuration('P5D'),       // now + 5 days,
        // 'field_duration' => $rp->getFieldDuration(),   // @TODO: remove, [Field Duration in days, most likely 1],
        'r_uni_size' => (int)$gs->result['F3'],           // [Col F Row 3 in Google Sheet],
        'r_uni_feasible' => (int)$gs->result['F5'],       // [Col F Row 5 in Google Sheet],
        'r_guaranteed' => (int)$gs->result['F7'],         // [Col F Row 7 in Google Sheet],
        'r_cr_req' => (int)$gs->result['F10'],            // [Col F Row 10 in Google Sheet],
        'r_panel_handling' => (int)$gs->result['F15'],    // [Col F Row 15 in Google Sheet],
        'r_hono_budget' => (int)$gs->result['F16'],       // [Col F Row 16 in Google Sheet],
        'r_total_panel' => (int)$gs->result['F17'],       // [Col F Row 17 in Google Sheet],
        'r_fee_complete' => (int)$gs->result['F20'],      // [Col F Row 20 in Google Sheet],
        'r_hono_complete' => (int)$gs->result['F21'],     // [Col F Row 21 in Google Sheet],
        'r_cr_budget' => (int)$gs->result['F22'],         // [Col F Row 22 in Google Sheet],
        'r_total_fielding' => (int)$gs->result['F24'],    // [Col F Row 24 in Google Sheet],
        'r_hono_offer' => (int)$gs->result['F26'],        // [Col F Row 26 in Google Sheet],
        'r_hono_cur' => $gs->result['F27'],               // [Col F Row 27 in Google Sheet],
        'estimate_date' => $rp->getEstimateDate(),        // [Estimated start date],
        'created_date' => $rp->getCreatedDate(),          // Now(),
        'r_sample' => (int)$gs->result['F8'],             // [Col F Row 8 in Google Sheet],
        'r_feasible' => (int)$gs->result['F7'],           // [Col F Row 7 in Google Sheet],
        'r_panel_usage' => (int)$gs->result['F14'],       // [Col F Row 14 in Google Sheet],
        'r_hono_handling' => (int)$gs->result['F15'],     // [Col F Row 15 in Google Sheet],
        'r_client_cur' => $gs->result['F12'],             // [Col F Row 12 in Google Sheet]);
        'sheet_name' => NULL,                               // null
        'r_cr_proj_mgmt' => 0,                              // 0
        'r_cr_cpi' => 0,                                    // 0
        'r_cr_hono_handling' => 0,                          // 0
        'r_field_duration' => $rp->getAddedDuration('P5D'), // now + 5 days
        'cr_approved' => NULL,                              // null
        'end_date' => $rp->getEstimateDate(),               // estimated completion date
        'cr_days' => 0,                                     // 0
        'cr_link' => NULL,                                  // null
        'cr_create_date' => NULL,                           // null
        'cr_end_date' => NULL,                              // null
        'rteamid_generated' => 0,                           // 0
        'rteam_batch_id' => 0,                              // 0
        'tsid' => NULL,                                     // null
      )
    );

    // returned the last inserted auto increment
    // feasibility_project_quota.quota_id
    return $conn->lastInsertId();
  }

  /**
   * update a Feasibility project
   *
   * Database Translateapi
   *
   * @param RPanelProject $rp - a RPanel project object
   */
  public function updateFeasibilityProject(RPanelProject $rp)
  {
    $conn = $this->conn;
    $conn->update('feasibility_project',
      array(
        'proj_status' => 2,                               // 2
        'launch_date' => $rp->getLaunchDate()             // launch_date from form 'Y-m-d H:i:s'
      ),
      array(
        'proj_id' => $rp->getProjId()                     // proj_id
      )
    );
  }

  /**
   * Creates a Feasibility project quota
   *
   * Database RPanel
   *
   * @param RPanelProject $rp - a RPanel project object
   * @return A string representation of the last inserted ID. (PROJECT.project_sk)
   */
  public function createProject(RPanelProject $rp)
  {
    $conn = $this->conn;
    $conn->insert('PROJECT',
      array(
        'project_code' => $rp->getProjId(),                                          // feasibility_project.projid
        'client_id' => $rp->getClientId(),                                           // [ClientID provided by Guohui]
        'status_id' => $rp->getStatusId(),                                           // 1
        'pm_details' => $rp->getProjName(),                                          // feasibility_project.proj_name
        'brand_id' => $rp->getBrandId(),                                             // 1
        'project_type' => $rp->getProjectType(),                                     // 'jit'
        'expiry_dttime' => gmdate('Y-m-d H:i:s', $rp->getAddedDuration('P5D')),    // now + 5 days
        'client_proposal_number' => $rp->getProjNum(),                               // clipper order_id - fqg.id
      )
    );

    // returned the last inserted auto increment
    // PROJECT.project_sk
    return $conn->lastInsertId();
  }

  /**
   * Creates a Feasibility project detail
   *
   * Database RPanel
   *
   * @param RPanelProject $rp - a RPanel project object
   */
  public function createProjectDetail(RPanelProject $rp, $gs)
  {
    $conn = $this->conn;
    $conn->insert('PROJECT_DETAIL',
      array(
        'project_sk' => $rp->getProjectSK(),              // PROJECT.project_sk,
        'specialty_id' => $gs->specialty_id,              // feasibility_project_quota.specialty_id,
        'country_id' => $gs->country_id,                  // feasibility_project_quota.country,
        'hono_amount' => (int)$gs->result['F26'],         // feasibility_project_quota.r_hono_offer,
        'hono_currency' => $gs->result['F27'],            // feasibility_project_quota.r_hono_cur,
        'interview_length' => $rp->getLength(),           // feasibility_project_quota.length,
        'email_template_id' => $rp->getEmailTemplateId(), // 0,
        'sample_invites' => $gs->result['F8'],            // feasibility_project_quota.r_sample,
        'quota' => $rp->getNumParticipants(),             // feasibility_project_quota.respondent_req
        'expiry_dttime' => gmdate('Y-m-d H:i:s', $rp->getAddedDuration('P5D')),  // now + 5 days
      )
    );

  }

  /**
   * Creates a Feasibility Link type
   *
   * Database RPanel
   *
   * @param RPanelProject $rp - a RPanel project object
   *
   * @return A string representation of the last inserted ID. (feasibility_link_type.ltid)
   */
  public function feasibilityLinkType(RPanelProject $rp)
  {
    $conn = $this->conn;
    $conn->insert('feasibility_link_type',
      array(
        'proj_id' => $rp->getProjId(),               // feasibility_project.projid
        'quote_id' => $rp->getQuoteId(),                  // feasibility_project_quota.quota_id
        'link_type' => $rp->getLinkType(),                // 'full'
        'created_by' => $rp->getCreatedBy(),              // [UserID created by Guohui]
        'created_date' => $rp->getCreatedDate(),          // Now()
      )
    );

    // returned the last inserted auto increment
    return $conn->lastInsertId();
  }

  /**
   * Creates a Feasibility link full url
   *
   * Database RPanel
   *
   * @param RPanelProject $rp - a RPanel project object
   */
  public function feasibilityLinkFullUrl(RPanelProject $rp)
  {
    $conn = $this->conn;
    foreach ($rp->getUrls() as $url) {
      $conn->insert('feasibility_full_url',
        array(
          'ltid' => $rp->getLTId(),                       // feasibility_link_type.ltid
          'link_url' => $url,                             // [Link URL from LimeSurvey]
          'created_date' => $rp->getCreatedDate(),        // Now()
          'created_by' => $rp->getCreatedBy(),            // [UserID created by Guohui]
        )
      );
    }
  }

  /**
   * PROJECT_DETAIL_TEXTINVITES
   *
   * @param RPanelProject $rp - a RPanel project object
   */
  public function createProjectDetailTextinvites(RPanelProject $rp)
  {
    $conn = $this->conn;
    $conn->insert('PROJECT_DETAIL_TEXTINVITES', array(
      // TODO: ask business for proper value
      'title' => $rp->getProjName(),
      // TODO: ask business for proper value
      'description' => $rp->getProjName(),
      // TODO: ask business for proper value
      'subject_email' => $rp->getProjName(),
      // translateapi.feasibility_project_quota.quota_id
      'quota_id' => $rp->getQuoteId(),
      // RPanel.PROJECT_DETAIL.project_sk
      'project_detail_sk' => $rp->getProjectSK(),
    ));
  }

}
