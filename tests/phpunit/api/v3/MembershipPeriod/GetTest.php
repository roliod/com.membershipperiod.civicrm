<?php
/*
 * Standalone scripts are not aware of CiviCRM
 * Comment this out and enter the path to your civicrm.settings.php
 *
require_once 'PATH_TO_YOUR_civicrm.settings.php';
require_once 'CRM/Core/Config.php';
*/

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * MembershipPeriod.Get API Test Case
 * @group headless
 */
class api_v3_MembershipPeriod_GetTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless()
  {
      return \Civi\Test::headless()
        ->installMe(__DIR__)
        ->apply();
  }

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

  /**
   * The tearDown() method is executed after the test was executed (optional)
   * This can be used for cleanup.
   */
  public function tearDown()
  {
      parent::tearDown();
  }

  /**
   * Test MembershipPeriod Api
   *
   * Note how the function name begins with the word "test".
   */
  public function testMembershipPeriod()
  {
      $ids = array();

      $contact_params = array(
        'sequential' => 1,
        'first_name' => "Buffon",
        'last_name' => "James",
        'contact_type' => "Individual",
      );

      $contact = CRM_Contact_BAO_Contact::create($contact_params, $ids, true);

      if (! isset($contact->id)) {
          self::assertTrue(FALSE);
      } else {
          $result = civicrm_api3('MembershipPeriod', 'Get', array(
              'sequential' => 1,
              'contact_id' => 4
          ));

          $this->assertEquals(false, $result['is_error']);
      }
  }

}
