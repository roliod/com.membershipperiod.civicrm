<?php

/**
 * This is a generic test class for the extension (implemented with PHPUnit).
 */
class CRM_mprd_MembershipPeriodUnitTest extends \PHPUnit_Framework_TestCase {

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

  public function testMembershipPeriodNew()
  {
      $params = array(
         'contact_id' => 114,
         'membership_type_id' => 2,
         'join_date' => '20180406',
         'start_date' => '20180406',
         'end_date' => '20180430',
         'source' => 'Payment',
         'status_id' => 1,
         'is_override' => null,
         'max_related' => null
      );
  }

  public function testMembershipPeriodRenewal()
  {

  }

  /**
   * Simple example test case.
   *
   * Note how the function name begins with the word "test".
   */
  public function testExample()
  {
      self::assertTrue(TRUE, "The argument must be true to pass the test");
  }

}
