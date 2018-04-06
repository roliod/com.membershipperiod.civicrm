<?php

require_once 'mprd.civix.php';
use CRM_mPRD_ExtensionUtil as E;

/**
 * Catch database posts.
 *
 */
function mprd_civicrm_post($op, $objectName, $objectId, &$objectRef)
{
    if ($objectName == 'Membership') { //make sure its a memebership

        $contact_id = isset($objectRef->contact_id) ? $objectRef->contact_id: '';

        if ($op == 'create') { //after creating a new memebership
            
            $membership_period = fetch_membership_period($contact_id);

            insert_membership_period($objectRef, $membership_period);

            $last_membership_period = last_membership_period($contact_id);

            insert_membership_period_to_membership($last_membership_period, $objectRef->id);

        }

    }

    if ($objectName == 'Contribution') {

        if ($op == 'create') {

            attach_contribution_to_period($objectRef->contact_id, $objectRef->id);

            return true;
        }

    }
}

/**
 * Catch membership obj before update
 *
 *
 * @access public
 * @return
 */
function mprd_civicrm_pre($op, $objectName, $id, &$params)
{
    if ($objectName == 'Membership') { //make sure its a memebership

        if ($op == 'edit') { //before a memebership update
            save_membership_update($params);
        }
    }
}

/**
 * Custormize contact summary page
 *
 *
 * @access public
 * @return
 */
function mprd_civicrm_summary($contactID, &$content, &$contentPlacement = CRM_Utils_Hook::SUMMARY_BELOW)
{
    // die(print_r(fetch_membership_period_with_contribution(199)));

    $contentPlacement = CRM_Utils_Hook::SUMMARY_BELOW;
    $content = '
    <div id="customFields">
      <div class="contact_panel">
        <div class="contactCardLef">
          <div class="customFieldGroup crm-collapsible ui-corner-all constituent_information crm-custom-set-block-1 collapsed">
            <div class="collapsible-title">
              Membership Periods
            </div>
            <div id="option12_wrapper" class="dataTables_wrapper no-footer">
              <table id="option12" class="display dataTable no-footer" role="grid">
                <thead>
                  <tr role="row">
                  <th class="sorting" tabindex="0" aria-controls="option12" rowspan="1" colspan="1" aria-label="Group: activate to sort column ascending">Period</th>
                  <th class="sorting" tabindex="0" aria-controls="option12" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending">Start Date</th>
                  <th class="sorting" tabindex="0" aria-controls="option12" rowspan="1" colspan="1" aria-label="Date Added: activate to sort column ascending">End Date</th>
                  <th class="sorting" tabindex="0" aria-controls="option12" rowspan="1" colspan="1" aria-label="Date Added: activate to sort column ascending">Contributon Record</th>
                  <th class="sorting_disabled" rowspan="1" colspan="1" aria-label=""></th>
                  </tr>
                </thead>
                <tbody>';

    foreach (fetch_membership_period_with_contribution($contactID) as $membership) {

    $content .= '<tr id="group_contact" class="crm-entity odd-row odd" role="row">
                    <td class="bold">'. $membership['period'] .'</td>
                    <td>'. $membership['start_date']. '</td>
                    <td>'. $membership['end_date'] .'</td>';

    $contribution_id = $membership['contribution_id'];

    if ($contribution_id != false) {

        $url = CRM_Utils_System::url('civicrm/contact/view/contribution',
          "reset=1&id=$contribution_id&cid=$contactID&action=view&context=contribution&selectedChild=contribute"
        );

        $content .= '<td><span><a href="'.$url.'" class="action-item crm-hover-button" title="View Contribution">View</a></span></td></tr>';

    }

    }

    $content .=  '</tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="contactCardRight">
        </div>
        <div class="clear"></div>
      </div>
    </div>';
}

/**
 * Get contacts membership periods, each with a contibution id
 * Contribution id would be 0 if payment was not taken for membership or renewal
 *
 * @access public
 * @return array
 */
function fetch_membership_period_with_contribution($contact_id)
{
    $sql = "SELECT period, start_date, end_date, contribution_id FROM civicrm_mprd_membership_period
            WHERE contact_id = $contact_id ORDER BY period ASC";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    $periods = array();

    while($dao->fetch()) {
        $periods[] = array(
            'period' => $dao->period,
            'start_date' => $dao->start_date,
            'end_date' => $dao->end_date,
            'contribution_id' => $dao->contribution_id
        );
    }

    if (empty($periods)) {
        return fetch_default_period($contact_id);
    }

    return $periods;
}

/**
 * Get default period from membership if we dont have the contact in our custorm table
 *
 *
 * @access public
 * @return array
 */
function fetch_default_period($contact_id)
{
    $contact_sql = "SELECT start_date, end_date FROM civicrm_membership WHERE contact_id = $contact_id";

    $contact = CRM_Core_DAO::executeQuery( $contact_sql, CRM_Core_DAO::$_nullArray );

    $contact->fetch();

    $contribution_sql = "SELECT id FROM civicrm_contribution WHERE contact_id = $contact_id";

    $contribution = CRM_Core_DAO::executeQuery( $contribution_sql, CRM_Core_DAO::$_nullArray );

    $contribution->fetch();

    $contribution = (payment_taken_for_membership($contact_id) == true) ? $contribution->id: 0;

    $period[] = array(
        'period' => 1,
        'start_date' => $contact->start_date,
        'end_date' => $contact->end_date,
        'contribution_id' => $contribution
    );

    return $period;
}

/**
 * Attach contibution id to membership period if a payment was taken
 *
 *
 * @access public
 * @return true
 */
function attach_contribution_to_period($contact_id, $contribution_id)
{
    if (payment_taken_for_membership($contact_id)) {

        $membership_period_id = fetch_current_membership($contact_id);

        $sql = "UPDATE civicrm_mprd_membership_period SET contribution_id = $contribution_id
                WHERE id = $membership_period_id";

        CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

        return true;
    }

    return true;
}

/**
 * Get contacts current membership period id
 *
 *
 * @access public
 * @return int
 */
function fetch_current_membership($contact_id)
{
    $sql = "SELECT id FROM civicrm_mprd_membership_period WHERE contact_id = $contact_id ORDER BY id DESC LIMIT 1";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    if (! $dao->fetch()) { //just incase we can't find anything, create a membership period
        $membership = fetch_contacts_membership_data($contact_id);

        $period = fetch_membership_period($contact_id);

        $start_date = str_replace('-', '', $membership->start_date);
        $end_date = str_replace('-', '', $membership->end_date);

        save_period($start_date, $end_date, $contact_id, $period);

        return fetch_current_membership($contact_id);
    }

    return $dao->id;
}

/**
 * Get all data for contacts membership
 *
 *
 * @access public
 * @return object
 */
function fetch_contacts_membership_data($contact_id)
{
    $sql = "SELECT * FROM civicrm_membership WHERE contact_id = $contact_id";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    $dao->fetch();

    return $dao;
}

/**
 * Check if a payment was taken for contacts memebership
 *
 *
 * @access public
 * @return true, false on failure
 */
function payment_taken_for_membership($contact_id = '')
{
    $sql = "SELECT is_pay_later FROM civicrm_membership WHERE contact_id = $contact_id";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    if (! $dao->fetch()) { //just incase we can't find anything, thats wierd but okay!
        return false;
    }

    if ($dao->is_pay_later == true) {
        return false;
    }

    return true;
}

/**
 * Save membership update
 *
 *
 * @access public
 * @return true, empty on failure
 */
function save_membership_update($params)
{
    if (! isset($params['end_date'])) { //check if its a renewal or period edit
        return;
    }

    //log current membership period before updating anything
    log_current_period($params['id']);

    $contact_id = fetch_contact_id_from_membership($params['id']);

    $period = fetch_membership_period($contact_id);

    $current_start_date = fetch_current_start_date($params['id']);

    $start_date = isset($params['start_date']) ? $params['start_date']: $current_start_date;

    //now log the new period
    save_period($start_date, $params['end_date'], $contact_id, $period);

    $membership_period_id = last_membership_period($contact_id);

    insert_membership_period_to_membership($membership_period_id, $params['id']);

    return true;
}

/**
 * Get contact id fro m membership table
 *
 *
 * @access public
 * @return int, false on failure
 */
function fetch_contact_id_from_membership($membership_id)
{
    $sql = "SELECT contact_id FROM civicrm_membership WHERE id = $membership_id";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    if (! $dao->fetch()) { //just incase we can't find anything, thats wierd but okay!
        return false;
    }

    return $dao->contact_id;
}

/**
 * Get current start date, just in case we are editing just the end date
 * Where membership has not expired
 *
 * @access public
 * @return string
 */
function fetch_current_start_date($membership_id)
{
    $sql = "SELECT start_date FROM civicrm_membership WHERE id = $membership_id";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    if (! $dao->fetch()) { //just incase we can't find anything, thats wierd but okay!
        return false;
    }

    return $dao->start_date;
}

/**
 * Log contacts current membership period before update
 *
 *
 * @access public
 * @return true
 */
function log_current_period($membership_id)
{
    $sql = "SELECT start_date, end_date, contact_id FROM civicrm_membership WHERE id = $membership_id";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    if (! $dao->fetch()) { //just incase we can't find anything, thats wierd but okay!
        return;
    }

    $period = fetch_membership_period($dao->contact_id);

    $start_date = str_replace('-', '', $dao->start_date);
    $end_date = str_replace('-', '', $dao->end_date);

    save_period($start_date, $end_date, $dao->contact_id, $period);

    return true;
}

/**
 * Save a membership period
 *
 *
 * @access public
 * @return true
 */
function save_period($start_date, $end_date, $contact_id, $period)
{
    $period = fetch_membership_period($contact_id);

    $sql = "INSERT INTO civicrm_mprd_membership_period (period, start_date, end_date, contact_id)
            VALUES ($period, $start_date, $end_date, $contact_id)";

    CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    return true;
}

/**
 * Get current period the contacts membership is
 *
 *
 * @access public
 * @return int
 */
function fetch_membership_period($contact_id = '')
{
    $sql = "SELECT period FROM civicrm_mprd_membership_period
            WHERE contact_id = $contact_id ORDER BY period DESC limit 1";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    //our custorm membership period table is empty so its the first period
    if (! $dao->fetch()) {
        return 1;
    }

    return $dao->period + 1;
}

/**
 * Insert new membership period
 *
 *
 * @access public
 * @return true
 */
function insert_membership_period($objectRef, $period)
{
    $sql = "INSERT INTO civicrm_mprd_membership_period (period, start_date, end_date, contact_id)
            VALUES ($period, $objectRef->start_date, $objectRef->end_date, $objectRef->contact_id)";

    CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    return true;
}

/**
 * Get contacts current membership period id
 *
 *
 * @access public
 * @return mixed false on failure, membership id on success
 */
function last_membership_period($contact_id)
{
    $sql = "SELECT id FROM civicrm_mprd_membership_period
            WHERE contact_id = $contact_id ORDER BY id DESC limit 1";

    $dao = CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    if (! $dao->fetch()) {
        return false;
    }

    return $dao->id;
}

/**
 * Update membership with the current membership period
 *
 *
 * @access public
 * @return true
 */
function insert_membership_period_to_membership($membership_period, $membership)
{
    $sql = "UPDATE civicrm_membership SET membership_period_id = $membership_period
            WHERE id = $membership";

    CRM_Core_DAO::executeQuery( $sql, CRM_Core_DAO::$_nullArray );

    return true;
}


/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function mprd_civicrm_config(&$config) {
  _mprd_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function mprd_civicrm_xmlMenu(&$files) {
  _mprd_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function mprd_civicrm_install() {
  _mprd_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function mprd_civicrm_postInstall() {
  _mprd_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function mprd_civicrm_uninstall() {
  _mprd_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function mprd_civicrm_enable() {
  _mprd_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function mprd_civicrm_disable() {
  _mprd_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function mprd_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _mprd_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function mprd_civicrm_managed(&$entities) {
  _mprd_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function mprd_civicrm_caseTypes(&$caseTypes) {
  _mprd_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function mprd_civicrm_angularModules(&$angularModules) {
  _mprd_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function mprd_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _mprd_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function mprd_civicrm_entityTypes(&$entityTypes) {
  _mprd_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function mprd_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function mprd_civicrm_navigationMenu(&$menu) {
  _mprd_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _mprd_civix_navigationMenu($menu);
} // */
