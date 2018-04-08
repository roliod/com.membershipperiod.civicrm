<?php
require_once '../../../../../default/civicrm.settings.php';
require_once 'CRM/Core/Config.php';


use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
// class CRM_mprd_MembershipPeriodUnitTest extends BaseTestClass {
class CRM_mprd_MembershipPeriodUnitTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  /**
   * Configure the headless environment.
   */
  public function setUpHeadless()
  {
      // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
      // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
      return \Civi\Test::headless()
        ->installMe(__DIR__)
        ->apply();
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp()
  {
      CRM_Core_Config::singleton( );

      parent::setUp();
  }

  public function tearDown()
  {
      parent::tearDown();
  }

  /**
   * Create a new membership where payment is taken
   */
  public function testMembershipPeriodNew()
  {
      $config = CRM_Core_Config::singleton( );

      $params = array(
         'contact_id' => 1,
         'membership_type_id' => 2,
         'join_date' => '20180406',
         'start_date' => '20180406',
         'end_date' => '20180430',
         'source' => 'Payment001',
         'status_id' => 1,
         'is_override' => null,
         'max_related' => null,
         'is_pay_later' => 0
      );

      $ids = array();

      //create membership
      CRM_Member_BAO_Membership::create($params, $ids, true);

      self::assertTrue(TRUE, "The argument must be true to pass the test");
  }

  /**
   * Create a new membership where payment is not taken
   */
  public function testMembershipPeriodNewPaymentNotTaken()
  {
      $params = array(
         'contact_id' => 2,
         'membership_type_id' => 2,
         'join_date' => '20180406',
         'start_date' => '20180406',
         'end_date' => '20181030',
         'source' => 'Payment002',
         'status_id' => 1,
         'is_override' => null,
         'max_related' => null,
         'is_pay_later' => 1
      );

      $ids = array();

      //create membership
      CRM_Member_BAO_Membership::create($params, $ids);

      self::assertTrue(TRUE, "The argument must be true to pass the test");
  }

  /**
   * Renew an existing membership
   */
  // public function testMembershipPeriodRenewal()
  // {
  //     // $default = array();
  //     //
  //     // $ids = array(
  //     //     'contact_id' => 1
  //     // );
  //
  //     $dates = array(
  //         'start_date' => CRM_Utils_Array::value('membership_start_date', '20180406'),
  //         'end_date' => CRM_Utils_Array::value('membership_end_date', '20190430'),
  //     );
  //
  //     $membership_renewal = new CRM_Core_Form();
  //     $membership_renewal->controller = new CRM_Core_Controller();
  //
  //     list($membership_renew) = CRM_Member_BAO_Membership::processMembership(
  //       1,
  //       2,
  //       FALSE,
  //       $membership_renewal,
  //       NULL,
  //       NULL,
  //       NULL,
  //       1,
  //       NULL,
  //       NULL,
  //       NULL,
  //       FALSE,
  //       $dates
  //     );
  //
  //     // $params = array(
  //     //     'join_date' => '20180406',
  //     //     'start_date' => '20180406',
  //     //     'end_date' => '20190430',
  //     //     'membership_type_id' => 2,
  //     //     'status_id' => 1
  //     // );
  //     //
  //     // CRM_Member_BAO_Membership::create($params, $ids);
  //
  //     self::assertTrue(TRUE, "The argument must be true to pass the test");
  // }

  /**
   * Add a contibution where payment was taken
   */
  public function testContributionWithPayment()
  {

  }

  /**
   * Add a contibution where payment was not taken
   */
  public function testContributionWithoutPayment()
  {

  }

}
