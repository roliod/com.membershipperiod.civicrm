<?php
use CRM_mPRD_ExtensionUtil as E;

/**
 * MembershipPeriod.Get API specification
 * This is used to get all membership periods of a contact with respective contribution records.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 */
function _civicrm_api3_membership_period_Get_spec(&$spec)
{
    $spec['contact_id']['api.required'] = 1;
}

/**
 * MembershipPeriod.Get API
 *
 * @param array $contact_id
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_membership_period_Get($params)
{
    if (array_key_exists('contact_id', $params)) {

        $contact_id = $params['contact_id'];

        //check if the contact has a membership
        $membership_sql = "SELECT id, start_date, end_date, is_pay_later FROM civicrm_membership
                           WHERE contact_id = $contact_id";

        $membership = CRM_Core_DAO::executeQuery( $membership_sql, CRM_Core_DAO::$_nullArray );

        if (! $membership->fetch()) {
            $response = array(
                10 => array('status'  => 'failed',
                            'message' => 'Contact does not have a membership'
                )
            );

            return civicrm_api3_create_error($response, $params);
        }

        //get the contacts membership periods
        $period_sql = "SELECT period, start_date, end_date, contribution_id
                       FROM civicrm_mprd_membership_period
                       WHERE contact_id = $contact_id ORDER BY period ASC";

        $period = CRM_Core_DAO::executeQuery( $period_sql, CRM_Core_DAO::$_nullArray );

        while($period->fetch()) {

          if ($membership->is_pay_later == false) {
              $contribution_sql = "SELECT * FROM civicrm_contribution
                                   WHERE id = $period->contribution_id";

              $contribution = CRM_Core_DAO::executeQuery( $contribution_sql, CRM_Core_DAO::$_nullArray );

              $contribution->fetch();

              if (! isset($contribution->id)) {
                  $contribution_record = array(
                      'message' => 'RECORD_DOES_NOT_EXIST',
                  );
              } else {
                  $contribution_record = array(
                        'total_amount' => $contribution->total_amount,
                        'currency' => $contribution->currency,
                        'source' => $contribution->source,
                        'receive_date' => $contribution->receive_date,
                        'contribution' => $contribution->id,
                        'message' => 'RECORD_EXIST'
                  );
              }
          } else {
              $contribution_record = array(
                  'message' => 'PAYMENT_NOT_TAKEN'
              );
          }

          $periods[] = array(
              'period' => $period->period,
              'start_date' => $period->start_date,
              'end_date' => $period->end_date,
              'contribution_record' => $contribution_record
          );
        }

        if (! isset($periods[0])) {

            //check if payment has been made for membership
            if ($membership->is_pay_later == false) {

                //fetch contirbution record
                $contribution_sql = "SELECT * FROM civicrm_contribution
                                     WHERE contact_id = $contact_id";

                $contribution = CRM_Core_DAO::executeQuery( $contribution_sql, CRM_Core_DAO::$_nullArray );

                while ($contribution->fetch()) {

                   $contribution_record[] = array(
                        'total_amount' => $contribution->total_amount,
                        'currency' => $contribution->currency,
                        'source' => $contribution->source,
                        'receive_date' => $contribution->receive_date,
                        'message' => 'RECORD_EXIST'
                   );
                }

                if (! isset($contribution_record[0])) {
                    $contribution_record[] = array(
                        'message' => 'RECORD_DOES_NOT_EXIST'
                    );
                }

            } else {
                $contribution_record[] = array(
                    'message' => 'PAYMENT_NOT_TAKEN'
                );
            }

            $periods[] = array(
                'period' => 1,
                'start_date' => $membership->start_date,
                'end_date' => $membership->end_date,
                'contribution_records' => $contribution_record
            );
        }

        $response = array(
            10 => array('status' => 'success',
                        'message' => $periods
            )
        );

        return civicrm_api3_create_success($response, $params);

    } else {

        throw new API_Exception('Required field contact_id is missing');

    }
}
