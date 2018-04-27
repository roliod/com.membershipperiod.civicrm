<?php

/*
 * Standalone scripts are not aware of CiviCRM
 * Comment this out and enter the path to your civicrm.settings.php
 *
require_once 'PATH_TO_YOUR_civicrm.settings.php';
require_once 'CRM/Core/Config.php';
*/


/**
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
class BaseUnitTestCase extends \PHPUnit_Framework_TestCase {

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp()
  {

      /*
       * Standalone scripts are not aware of CiviCRM
       * Comment this out
       *
         CRM_Core_Config::singleton();
      */


      parent::setUp();
  }

  public function tearDown()
  {
      parent::tearDown();
  }

  /**
   * Helper for creating contacts
   */
  public function create_contact($params)
  {
      $ids = array();

      // create contact
      $contact = CRM_Contact_BAO_Contact::create($params, $ids, true);

      if (! isset($contact->id)) {

          return [
              'status' => false
          ];

      } else {

          return [
              'status' => true,
              'message' => $contact->id
          ];

      }
  }

  /**
   * Helper for creating memberships
   */
  public function create_membership($params)
  {
      $ids = array();

      //create membership
      $membership = CRM_Member_BAO_Membership::create($params, $ids);

      if (! isset($membership->id)) {

          return [
              'status' => false
          ];

      } else {

          return [
              'status' => true,
              'message' => $membership->id
          ];

      }
  }

  /**
   * Could be used for creating, but we would be using this helper for renewing memberships
   */
  public function renew_membership($params, $id)
  {
      $ids = array(
          'id' => $id
      );

      //create membership
      $membership = CRM_Member_BAO_Membership::add($params, $ids);

      if (! isset($membership->id)) {

          return [
              'status' => false
          ];

      } else {

          return [
              'status' => true,
              'message' => $membership->id
          ];

      }
  }

  /**
   * Helper for making contribution
   */
  public function make_contribution($params)
  {
      $ids = array();

      $contribution = CRM_Contribute_BAO_Contribution::add($params, $ids);

      if (! isset($contribution->id)) {

          return [
              'status' => false
          ];

      } else {

          return [
              'status' => true,
              'message' => $contribution->id
          ];

      }
  }

}
