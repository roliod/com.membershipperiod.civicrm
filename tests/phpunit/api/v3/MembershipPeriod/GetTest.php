<?php

require_once __DIR__ . '../../../../CRM/mprd/BaseUnitTestCase.php';

/**
 * MembershipPeriod.Get API Test Case
 * @group headless
 */
class api_v3_MembershipPeriod_GetTest extends BaseUnitTestCase {

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

      //create contact
      $contact = $this->create_contact($contact_params);

      if ($contact['status'] == false) {
          self::assertTrue(FALSE);
      } else {

          //create membership
          $membership_params = array(
             'contact_id' => $contact['message'],
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

          $membership = $this->create_membership($membership_params);

          if ($membership['status'] == false) {

              self::assertTrue(FALSE);

          } else {

              $result = civicrm_api3('MembershipPeriod', 'Get', array(
                  'sequential' => 1,
                  'contact_id' => $contact['message']
              ));

              $this->assertEquals(false, $result['is_error']);

              self::assertTrue(TRUE);
          }
      }
  }

}
